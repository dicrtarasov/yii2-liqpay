<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 10.11.20 02:58:45
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\liqpay\LiqPayModule;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;

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
    private static function module() : LiqPayModule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('liqpay');
    }

    /**
     * @throws Exception
     */
    public function testSend() : void
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
