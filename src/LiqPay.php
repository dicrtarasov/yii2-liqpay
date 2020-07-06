<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 11:36:43
 */

declare(strict_types = 1);
namespace dicr\liqpay;

/**
 * Константы LiqPay.
 */
interface LiqPay
{
    /** @var int версия API */
    public const VERSION = 3;

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

    /** @var string регулярный платеж. Приходит только в callback-запросе. */
    public const ACTION_REGULAR = 'regular';

    /** @var string[] типы операций */
    public const ACTIONS = [
        self::ACTION_PAY => 'платеж',
        self::ACTION_HOLD => 'удержание средств',
        self::ACTION_SUBSCRIBE => 'регулярный платеж',
        self::ACTION_PAYDONATE => 'пожертвование',
        self::ACTION_AUTH => 'пред-авторизация карты',
        self::ACTION_REGULAR => 'регулярный платеж'
    ];

    /** @var string URL API для запросов методом Server-Server */
    public const URL_CHECKOUT_API = 'https://www.liqpay.ua/api/request';

    /** @var string URL страницы переадресации для метода Client-Server */
    public const URL_CHECKOUT_CLIENT = 'https://www.liqpay.ua/api/3/checkout';

    /** @var string успешный платеж */
    public const STATUS_SUCCESS = 'success';

    /** @var string Неуспешный платеж */
    public const STATUS_FAILURE = 'failure';

    /** @var string Платеж возвращен */
    public const STATUS_REVERSED = 'reversed';

    // todo: остальные статусы: https://www.liqpay.ua/documentation/api/callback
}
