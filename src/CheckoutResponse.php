<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 11:52:09
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
    public function formName()
    {
        return '';
    }

    /**
     * @return array
     */
    public function rules()
    {
        // нужно перечислить safe, которые будут загружаться
        return [
            ['status', 'required'],
            ['err_description', 'string'],
            ['order_id', 'string'],
        ];
    }
}
