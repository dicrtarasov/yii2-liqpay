<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 04.01.22 22:44:53
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\liqpay\LiqPayModule;
use Exception;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * Class CheckoutRequestTest
 */
class CheckoutRequestTest extends TestCase
{
    /**
     * Модуль.
     *
     * @return LiqPayModule
     */
    private static function module(): LiqPayModule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('liqpay');
    }

    /**
     * @throws Exception
     */
    public function testSend(): void
    {
        $req = self::module()->checkoutRequest([
            'orderId' => 123,
            'amount' => 123.45,
            'description' => 'Тест оплаты'
        ]);

        $res = $req->send();

        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($res);
    }
}
