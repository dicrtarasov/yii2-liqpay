<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 17.01.22 05:09:21
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
    /** статус платежа */
    public string|null $status = null;

    /** текст ошибки */
    public ?string $err_description = null;

    /** id заказа магазина */
    public string|int|null $order_id = null;

    // todo: реализовать остальные

    /**
     * @inheritDoc
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
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
