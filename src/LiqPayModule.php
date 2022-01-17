<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 17.01.22 05:13:09
 */

declare(strict_types=1);
namespace dicr\liqpay;

use Closure;
use JsonException;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Application;

use function array_filter;
use function array_map;
use function array_merge;
use function base64_encode;
use function is_callable;
use function json_encode;
use function sha1;

/**
 * Модуль LiqPay.
 *
 * @link https://www.liqpay.ua/documentation/api/aquiring/
 */
class LiqPayModule extends Module implements LiqPay
{
    /** @inheritDoc */
    public $controllerNamespace = __NAMESPACE__;

    public string $publicKey;

    public string $privateKey;

    /** конфиг LiqPayCheckout по-умолчанию */
    public array $checkoutConfig = [];

    /** function(CheckoutResponse $response) */
    public Closure $checkoutHandler;

    /** режим отладки */
    public bool $debug = false;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->publicKey)) {
            throw new InvalidConfigException('publicKey');
        }

        if (empty($this->privateKey)) {
            throw new InvalidConfigException('privateKey');
        }

        if (! empty($this->checkoutHandler) && ! is_callable($this->checkoutHandler)) {
            throw new InvalidConfigException('checkoutHandler');
        }
    }

    /**
     * Кодирует данные.
     *
     * @throws JsonException
     */
    public function encodeData(array $data): string
    {
        // конвертируем значения в строки
        $data = array_map(static fn($val): string => trim((string)$val), $data);

        // фильтруем пустые значения
        $data = array_filter($data, static fn(string $val): bool => $val !== '');

        // добавляем ключ
        $data['public_key'] = $this->publicKey;

        // кодируем
        return base64_encode(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * Генерирует подпись данных.
     *
     * @link https://www.liqpay.ua/documentation/data_signature
     */
    public function signData(string $data): string
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
    }

    /**
     * Создает запрос Checkout.
     */
    public function checkoutRequest(array $config = []): CheckoutRequest
    {
        return new CheckoutRequest($this, array_merge([
            'callbackUrl' => Yii::$app instanceof Application ?
                Url::to(['/' . $this->uniqueId . '/checkout'], true) : null,
            'returnUrl' => Yii::$app instanceof Application ?
                Url::to(Yii::$app->homeUrl, true) : null
        ], $this->checkoutConfig ?: [], $config));
    }
}
