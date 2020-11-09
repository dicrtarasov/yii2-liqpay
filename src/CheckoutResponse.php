<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 10.11.20 02:35:10
 */

declare(strict_types = 1);
namespace dicr\liqpay;

use yii\base\Model;

/**
 * Ответ на запрос Checkout.
 *
 * @link https://www.liqpay.ua/documentation/api/callback
 */
class CheckoutResponse extends Model implements LiqPay
{
    /** @var string статус платежа */
    public $status;

    /** @var string текст ошибки */
    public $err_description;

    /** @var string id заказа магазина */
    public $order_id;

    // todo: реализовать остальные

    /**
     * @return string
     */
    public function formName() : string
    {
        return '';
    }

    /**
     * @return array
     */
    public function rules() : array
    {
        // нужно перечислить safe, которые будут загружаться
        return [
            ['status', 'required'],
            ['err_description', 'string'],
            ['order_id', 'string'],
        ];
    }
}
