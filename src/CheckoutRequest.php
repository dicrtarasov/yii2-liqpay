<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 17.01.22 05:08:33
 */

declare(strict_types=1);

namespace dicr\liqpay;

use dicr\validate\ValidateException;
use JsonException;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\StringHelper;

use function array_keys;
use function file_get_contents;
use function http_build_query;
use function json_decode;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function stream_context_create;

/**
 * Эквайринг платежей методом LiqPay Checkout.
 *
 * @link https://www.liqpay.ua/documentation/api/aquiring/checkout/doc
 */
class CheckoutRequest extends Model implements LiqPay
{
    /** Версия API (required) */
    public float $version = self::VERSION;

    /** тип операции (required) */
    public string $action = self::ACTION_PAY;

    /** язык интерфейса (optional) */
    public ?string $language = self::LANGUAGE_RU;

    /** адрес страницы для отправки результата (optional) */
    public ?string $callbackUrl = null;

    /** адрес для перенаправления клиента обратно (optional) */
    public ?string $returnUrl = null;

    /** сумма платежа (required) */
    public ?float $amount = null;

    /** валюта (required) */
    public ?string $currency = self::CURRENCY_UAH;

    /** назначение платежа (required) */
    public ?string $description = null;

    /** уникальный ID покупки в магазине (required) */
    public string|int|null $orderId = null;

    /** идентификатор покупателя (optional) */
    public string|int|null $customerId = null;

    /**
     * время до которого клиент может оплатить счет по UTC. (optional)
     * Передается в формате YYYY-MM-DD HH:mm:ss
     */
    public string|null $expiredDate = null;

    /**
     * @var string[]|null способы оплаты (optional)
     * Если не задано, то будут использоваться настройки личного кабинета.
     */
    public ?array $paytypes = null;

    /**
     * код верификации.
     * Генерируется и возвращается в Callback. Так же сгенерированный код будет передан в транзакции верификации
     * для отображения в выписке по карте клиента. Работает для action= auth
     */
    public ?bool $verifyCode = false;

    // @todo реализовать остальные параметры

    /**
     * CheckoutRequest constructor.
     *
     * @param LiqPayModule $module
     * @param array $config
     */
    public function __construct(
        protected LiqPayModule $module,
        array $config = []
    ) {
        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            ['version', 'required'],
            ['version', 'number', 'min' => 1],

            ['action', 'required'],
            ['action', 'in', 'range' => array_keys(self::ACTIONS)],

            ['language', 'default'],
            ['language', 'in', 'range' => self::LANGUAGES],

            [['callbackUrl', 'returnUrl'], 'default'],
            [['callbackUrl', 'returnUrl'], 'url'],

            ['amount', 'required'],
            ['amount', 'number', 'min' => 0.01],
            ['amount', 'filter', 'filter' => 'floatval'],

            ['currency', 'required'],
            ['currency', 'in', 'range' => self::CURRENCIES],

            ['description', 'trim'],
            ['description', 'required'],

            ['orderId', 'trim'],
            ['orderId', 'required'],

            ['customerId', 'trim'],
            ['customerId', 'default'],

            ['expiredDate', 'default'],
            ['expiredDate', 'date', 'format' => 'php:Y-m-d H:i:s'],

            ['paytypes', 'default', 'value' => []],
            ['paytypes', 'each', 'rule' => ['in', 'range' => array_keys(self::PAYTYPES)]],

            ['verifyCode', 'default', 'value' => false],
            ['verifyCode', 'boolean'],
            ['verifyCode', 'filter', 'filter' => 'boolval'],
        ];
    }

    /**
     * Возвращает данные.
     *
     * @throws ValidateException
     */
    private function values(): array
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        return [
            'version' => $this->version,
            'action' => $this->action,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'order_id' => $this->orderId,
            'language' => $this->language,
            'server_url' => $this->callbackUrl,
            'result_url' => $this->returnUrl,
            'customer' => $this->customerId,
            'expired_date' => $this->expiredDate,
            'paytypes' => implode(' ', $this->paytypes),
            'verifycode' => $this->verifyCode ? 'Y' : '',
            'sandbox' => $this->module->debug ? 1 : ''
        ];
    }

    /**
     * Обработка методом переадресации клиента на страницу оплаты.
     * Статус операции будет отправлен на callbackUrl.
     *
     * @throws ValidateException|JsonException
     */
    public function redirect(): void
    {
        $data = $this->module->encodeData($this->values());
        $signature = $this->module->signData($data);

        // очищаем вывод
        while (ob_get_level() > 0) {
            ob_get_clean();
        }

        // готовим страницу ответа
        ob_start();
        ?>
        <html lang="ru">
        <head>
            <meta charset="UTF-8"/>
            <title>Переадресация на оплату в LiqPay</title>
        </head>
        <body>
            <?php
            echo Html::beginTag('form', [
                'id' => 'liqpay-form',
                'method' => 'POST',
                'action' => self::URL_CHECKOUT_CLIENT,
                'accept-charset' => 'utf-8'
            ]);

            echo Html::hiddenInput('data', $data);
            echo Html::hiddenInput('signature', $signature);
            echo Html::endTag('form');
            ?>
            <script>window.document.forms[0].submit();</script>
        </body>
        </html>
        <?php
        // отправляем ответ
        Yii::$app->response->content = ob_get_clean();
        Yii::$app->response->statusCode = 200;

        try {
            Yii::$app->end(0, Yii::$app->response);
        } catch (Throwable $ex) {
            Yii::error($ex, __METHOD__);
            exit;
        }
    }

    /**
     * Отправляет запрос к API.
     *
     * @throws Exception
     * @throws JsonException
     */
    public function send(): CheckoutResponse
    {
        $data = $this->module->encodeData($this->values());
        $signature = $this->module->signData($data);

        $query = http_build_query([
            'data' => $data,
            'signature' => $signature
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'content' => $query,
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: ' . StringHelper::byteLength($query)
                ],
            ],
            'ssl' => [
                'verify_peer' => false,
                'allow_self_signed' => true
            ]

        ]);

        $ret = file_get_contents(self::URL_CHECKOUT_API, false, $ctx);
        if ($ret === false) {
            throw new Exception('Ошибка запроса LiqPay');
        }

        $json = json_decode($ret, true, 512, JSON_THROW_ON_ERROR);
        if (empty($json)) {
            throw new Exception('Некорректный ответ LiqPay: ' . $ret);
        }

        // инициализируем модель ответа
        $response = new CheckoutResponse();

        // устанавливаем через setAttributes с safe = true
        $response->setAttributes($json, false);

        return $response;
    }
}
