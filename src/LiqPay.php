<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 17.01.22 05:11:59
 */

declare(strict_types=1);
namespace dicr\liqpay;

/**
 * Константы LiqPay.
 */
interface LiqPay
{
    /** версия API */
    public const VERSION = 3;

    /** валюта доллары */
    public const CURRENCY_USD = 'USD';

    /** валюта евро */
    public const CURRENCY_EUR = 'EUR';

    /** валюта рубли */
    public const CURRENCY_RUB = 'RUB';

    /** валюта гривны */
    public const CURRENCY_UAH = 'UAH';

    /** валюта белорусский рубль */
    public const CURRENCY_BYN = 'BYN';

    /** валюта казахстанский тенге */
    public const CURRENCY_KZT = 'KZT';

    /** валюты */
    public const CURRENCIES = [
        self::CURRENCY_USD, self::CURRENCY_EUR,
        self::CURRENCY_RUB, self::CURRENCY_UAH,
        self::CURRENCY_BYN, self::CURRENCY_KZT
    ];

    /** язык интерфейса: русский */
    public const LANGUAGE_RU = 'ru';

    /** язык интерфейса: английский */
    public const LANGUAGE_EN = 'en';

    /** язык UK */
    public const LANGUAGE_UK = 'uk';

    /** языки интерфейса */
    public const LANGUAGES = [
        self::LANGUAGE_RU, self::LANGUAGE_EN, self::LANGUAGE_UK
    ];

    /** оплата картой */
    public const PAYTYPE_CARD = 'card';

    /** оплата через кабинет liqpay */
    public const PAYTYPE_LIQPAY = 'liqpay';

    /** оплата через Приват24 */
    public const PAYTYPE_PRIVAT24 = 'privat24';

    /** оплата через кабинет masterpass */
    public const PAYTYPE_MASTERPASS = 'masterpass';

    /** рассрочка */
    public const PAYTYPE_MOMENTPART = 'moment_part';

    /** наличными */
    public const PAYTYPE_CASH = 'cash';

    /** счет на e-mail */
    public const PAYTYPE_INVOICE = 'invoice';

    /** сканирование QR_CODE */
    public const PAYTYPE_QR = 'qr';

    /** способы оплаты */
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

    /** тип операции: платеж */
    public const ACTION_PAY = 'pay';

    /** тип операции: блокировка средств на счету отправителя */
    public const ACTION_HOLD = 'hold';

    /** тип операции: регулярный платеж */
    public const ACTION_SUBSCRIBE = 'subscribe';

    /** тип операции: пожертвование */
    public const ACTION_PAYDONATE = 'paydonate';

    /** тип операции: пред-авторизация карты */
    public const ACTION_AUTH = 'auth';

    /** регулярный платеж. Приходит только в callback-запросе. */
    public const ACTION_REGULAR = 'regular';

    /** типы операций */
    public const ACTIONS = [
        self::ACTION_PAY => 'платеж',
        self::ACTION_HOLD => 'удержание средств',
        self::ACTION_SUBSCRIBE => 'регулярный платеж',
        self::ACTION_PAYDONATE => 'пожертвование',
        self::ACTION_AUTH => 'пред-авторизация карты',
        self::ACTION_REGULAR => 'регулярный платеж'
    ];

    /** URL API для запросов методом Server-Server */
    public const URL_CHECKOUT_API = 'https://www.liqpay.ua/api/request';

    /** URL страницы переадресации для метода Client-Server */
    public const URL_CHECKOUT_CLIENT = 'https://www.liqpay.ua/api/3/checkout';

    /** успешный платеж */
    public const STATUS_SUCCESS = 'success';

    /** Неуспешный платеж. Некорректно заполнены данные */
    public const STATUS_ERROR = 'error';

    /** Неуспешный платеж */
    public const STATUS_FAILURE = 'failure';

    /** Платеж возвращен */
    public const STATUS_REVERSED = 'reversed';

    /** Тестовый платеж */
    public const STATUS_SANDBOX = 'sandbox';

    // todo: остальные статусы: https://www.liqpay.ua/documentation/api/callback
}
