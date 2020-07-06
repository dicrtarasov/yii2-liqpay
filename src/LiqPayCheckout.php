<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 09:04:29
 */

/** @noinspection PhpUnused */
declare(strict_types = 1);

namespace dicr\liqpay;

use Yii;
use yii\base\BaseObject;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use function array_filter;
use function array_key_exists;
use function base64_encode;
use function gmdate;
use function in_array;
use function is_numeric;
use function json_encode;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function sprintf;
use function strtolower;

/**
 * Эквайринг платежей методом LiqPay Checkout.
 *
 * @link https://www.liqpay.ua/documentation/api/aquiring/checkout/
 * @link https://www.liqpay.ua/documentation/data_signature
 */
class LiqPayCheckout extends BaseObject
{
    /** @var int версия API */
    public const VERSION = 3;

    /** @var string URL API для запросов методом Server-Server */
    public const URL_SERVER = 'https://www.liqpay.ua/api/request';

    /** @var string URL страницы переадресации для метода Client-Server */
    public const URL_CLIENT = 'https://www.liqpay.ua/api/3/checkout';

    /** @var string тип операции: платеж */
    public const ACTION_PAY = 'pay';

    /** @var string тип операции: блокировка средств на счету отправителя */
    public const ACTION_HOLD = 'hold';

    /** @var string тип операции: регулярный платеж */
    public const ACTION_SUBSCRIBE = 'subscribe';

    /** @var string тип операции: пожертвование */
    public const ACTION_PAYDONATE = 'paydonate';

    /** @var string тип операции: пред-авторизация карты */
    public const ACTION_AUTH = 'auth';

    /** @var string[] типы операций */
    public const ACTIONS = [
        self::ACTION_PAY => 'платеж',
        self::ACTION_HOLD => 'удержание средств',
        self::ACTION_SUBSCRIBE => 'регулярный платеж',
        self::ACTION_PAYDONATE => 'пожертвование',
        self::ACTION_AUTH => 'пред-авторизация карты'
    ];

    /** @var string валюта доллары */
    public const CURRENCY_USD = 'USD';

    /** @var string валюта евро */
    public const CURRENCY_EUR = 'EUR';

    /** @var string валюта рубли */
    public const CURRENCY_RUB = 'RUB';

    /** @var string валюта гривны */
    public const CURRENCY_UAH = 'UAH';

    /** @var string валюта белорусский рубль */
    public const CURRENCY_BYN = 'BYN';

    /** @var string валюта казахстанский тенге */
    public const CURRENCY_KZT = 'KZT';

    /** @var string[] валюты */
    public const CURRENCIES = [
        self::CURRENCY_USD, self::CURRENCY_EUR,
        self::CURRENCY_RUB, self::CURRENCY_UAH,
        self::CURRENCY_BYN, self::CURRENCY_KZT
    ];

    /** @var string язык интерфейса: русский */
    public const LANGUAGE_RU = 'ru';

    /** @var string язык интерфейса: английский */
    public const LANGUAGE_EN = 'en';

    /** @var string язык UK */
    public const LANGUAGE_UK = 'uk';

    /** @var string[] языки интерфейса */
    public const LANGUAGES = [
        self::LANGUAGE_RU, self::LANGUAGE_EN, self::LANGUAGE_UK
    ];

    /** @var string оплата картой */
    public const PAYTYPE_CARD = 'card';

    /** @var string оплата через кабинет liqpay */
    public const PAYTYPE_LIQPAY = 'liqpay';

    /** @var string оплата через Приват24 */
    public const PAYTYPE_PRIVAT24 = 'privat24';

    /** @var string оплата через кабинет masterpass */
    public const PAYTYPE_MASTERPASS = 'masterpass';

    /** @var string рассрочка */
    public const PAYTYPE_MOMENTPART = 'moment_part';

    /** @var string наличными */
    public const PAYTYPE_CASH = 'cash';

    /** @var string счет на e-mail */
    public const PAYTYPE_INVOICE = 'invoice';

    /** @var string сканирование QR_CODE */
    public const PAYTYPE_QR = 'qr';

    /** @var string[] способы оплаты */
    public const PAYTYPES = [
        self::PAYTYPE_CASH => 'наличными',
        self::PAYTYPE_CARD => 'картой',
        self::PAYTYPE_INVOICE => 'счет на e-mail',
        self::PAYTYPE_LIQPAY => 'кабинет LiqPay',
        self::PAYTYPE_MASTERPASS => 'кабинет MasterPass',
        self::PAYTYPE_PRIVAT24 => 'Приват24',
        self::PAYTYPE_MOMENTPART => 'рассрочка',
        self::PAYTYPE_QR => 'сканирование QR-кода'
    ];

    /** @var float Версия API (required) */
    public $version = self::VERSION;

    /** @var string ключ public_key (required) */
    public $publicKey;

    /** @var string ключ private_key (required) */
    public $privateKey;

    /** @var string тип операции (required) */
    public $action = self::ACTION_PAY;

    /** @var float сумма платежа (required) */
    public $amount;

    /** @var string валюта (required) */
    public $currency;

    /** @var string назначение платежа (required) */
    public $description;

    /** @var string [255] уникальный ID покупки в магазине (required) */
    public $orderId;

    /** @var string язык интерфейса (optional) */
    public $language = self::LANGUAGE_RU;

    /** @var string адрес страницы для отправки результата (optional) */
    public $callbackUrl;

    /** @var string адрес для перенаправления клиента обратно (optional) */
    public $returnUrl;

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
    public $verifyCode;

    /** @var bool режим отладки */
    public $debug = false;

    /**
     * Проверка данных.
     *
     * @throws InvalidConfigException
     */
    public function validate()
    {
        $this->version = (float)$this->version;
        if ($this->version <= 0) {
            throw new InvalidConfigException('version');
        }

        $this->publicKey = trim((string)$this->publicKey);
        if (empty($this->publicKey)) {
            throw new InvalidConfigException('publicKey');
        }

        $this->privateKey = trim((string)$this->privateKey);
        if (empty($this->privateKey)) {
            throw new InvalidConfigException('privateKey');
        }

        $this->action = trim((string)$this->action);
        if (! array_key_exists($this->action, self::ACTIONS)) {
            throw new InvalidConfigException('action');
        }

        $this->amount = (float)sprintf('%.2f', $this->amount);
        if ($this->amount <= 0) {
            throw new InvalidConfigException('amount');
        }

        $this->currency = trim((string)$this->currency);
        if (! in_array($this->currency, self::CURRENCIES, true)) {
            throw new InvalidConfigException('currency');
        }

        $this->description = trim((string)$this->description);
        if (empty($this->description)) {
            throw new InvalidConfigException('description');
        }

        $this->orderId = trim((string)$this->orderId);
        if (empty($this->orderId)) {
            throw new InvalidConfigException('orderId');
        }

        $this->language = trim((string)$this->language);
        if (! empty($this->language) && ! in_array($this->language, self::LANGUAGES, true)) {
            throw new InvalidConfigException('language');
        }

        $this->callbackUrl = trim((string)$this->callbackUrl);
        $this->returnUrl = trim((string)$this->returnUrl);
        $this->customerId = trim((string)$this->customerId);

        $this->expiredDate = trim((string)$this->expiredDate);
        if ($this->expiredDate !== '' && ! is_numeric($this->expiredDate)) {
            $time = strtolower($this->expiredDate);
            if (empty($time) || $time < time()) {
                throw new InvalidConfigException('expiredDate');
            }
        }

        $this->paytypes = (array)($this->paytypes ?: []);
        foreach ($this->paytypes as $type) {
            if ((! array_key_exists($type, self::PAYTYPES))) {
                throw new InvalidConfigException('paytype: ' . $type);
            }
        }

        $this->verifyCode = (bool)$this->verifyCode;

        $this->debug = (bool)$this->debug;
    }

    /**
     * Возвращает данные.
     *
     * @return string[]
     * @throws InvalidConfigException
     */
    protected function values()
    {
        $this->validate();

        return array_filter([
            'version' => (string)$this->version,
            'public_key' => (string)$this->publicKey,
            'action' => (string)$this->action,
            'amount' => (string)$this->amount,
            'currency' => (string)$this->currency,
            'description' => (string)$this->description,
            'order_id' => (string)$this->orderId,
            'language' => (string)$this->language,
            'server_url' => (string)$this->callbackUrl,
            'result_url' => (string)$this->returnUrl,
            'customer' => (string)$this->customerId,
            'expired_date' => ! empty($this->expiredDate) ?
                gmdate('Y-m-d H:i:s', $this->expiredDate) : '',
            'paytypes' => implode(' ', $this->paytypes),
            'verifycode' => $this->verifyCode ? 'Y' : '',
            'sandbox' => $this->debug ? '1' : ''
        ], static function(string $val) {
            return $val !== '';
        });
    }

    /**
     * Значение data.
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function data()
    {
        return base64_encode(json_encode($this->values()));
    }

    /**
     * Генерирует подпись данных.
     *
     * @param string $data
     * @return string
     */
    protected function signature(string $data)
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, 1));
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
        $data = $this->data();
        $signature = $this->signature($data);

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
                'action' => self::URL_CLIENT,
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
}
