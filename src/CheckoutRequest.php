<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 12:42:00
 */

/** @noinspection PhpUnused */
declare(strict_types = 1);

namespace dicr\liqpay;

use Yii;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
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
    /** @var LiqPayModule */
    protected $module;

    /** @var float Версия API (required) */
    public $version = self::VERSION;

    /** @var string тип операции (required) */
    public $action = self::ACTION_PAY;

    /** @var string язык интерфейса (optional) */
    public $language = self::LANGUAGE_RU;

    /** @var string адрес страницы для отправки результата (optional) */
    public $callbackUrl;

    /** @var string адрес для перенаправления клиента обратно (optional) */
    public $returnUrl;

    /** @var float сумма платежа (required) */
    public $amount;

    /** @var string валюта (required) */
    public $currency;

    /** @var string назначение платежа (required) */
    public $description;

    /** @var string [255] уникальный ID покупки в магазине (required) */
    public $orderId;

    /** @var string идентификатор покупателя (optional) */
    public $customerId;

    /**
     * @var string|int время до которого клиент может оплатить счет по UTC. (optional)
     * Передается в формате YYYY-MM-DD HH:mm:ss
     */
    public $expiredDate;

    /**
     * @var string[] способы оплаты (optional)
     * Если не задано, то будут использоваться настройки личного кабинета.
     */
    public $paytypes;

    /**
     * @var bool код верификации.
     * Генерируется и возвращается в Callback. Так же сгенерированный код будет передан в транзакции верификации
     * для отображения в выписке по карте клиента. Работает для action= auth
     */
    public $verifyCode = false;

    /** @var bool режим отладки */
    public $debug = false;

    // @todo реализовать остальные параметры

    /**
     * CheckoutRequest constructor.
     *
     * @param LiqPayModule $module
     * @param array $config
     */
    public function __construct(LiqPayModule $module, $config = [])
    {
        $this->module = $module;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['version', 'default', 'value' => self::VERSION],
            ['version', 'number', 'min' => 1],

            ['action', 'default', 'value' => self::ACTION_PAY],
            ['action', 'in', 'range' => array_keys(self::ACTIONS)],

            ['language', 'default', 'value' => self::LANGUAGE_RU],
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

            ['debug', 'default', 'value' => false],
            ['debug', 'boolean'],
            ['debug', 'filter', 'filter' => 'boolval'],
        ];
    }

    /**
     * Возвращает данные.
     *
     * @return array
     * @throws InvalidConfigException
     */
    protected function values()
    {
        if (! $this->validate()) {
            $attr = array_keys($this->firstErrors)[0];
            throw new InvalidConfigException($attr . ': ' . $this->getFirstError($attr));
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
            'sandbox' => $this->debug ? 1 : ''
        ];
    }

    /**
     * Обработка методом переадресации клиента на страницу оплаты.
     * Статус операции будет отправлен на callbackUrl.
     *
     * @throws InvalidConfigException
     * @throws ExitException
     */
    public function processClient()
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
        Yii::$app->end(0, Yii::$app->response);
    }

    /**
     * Отправляет запрос к API.
     *
     * @return CheckoutResponse
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function processApi()
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

        $json = json_decode($ret, true);
        if (empty($json)) {
            throw new Exception('Некорректный ответ LiqPay: ' . $ret);
        }

        // инициализируем модель ответа
        $response = new CheckoutResponse();

        // устанавливаем через setAttributes с safe = true
        $response->attributes = $json;
        return $response;
    }
}
