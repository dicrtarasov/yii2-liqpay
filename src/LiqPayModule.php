<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 26.07.20 04:13:26
 */

declare(strict_types = 1);
namespace dicr\liqpay;

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
 */
class LiqPayModule extends Module implements LiqPay
{
    /** @inheritDoc */
    public $controllerNamespace = __NAMESPACE__;

    /** @var string */
    public $publicKey;

    /** @var string */
    public $privateKey;

    /** @var array конфиг LiqPayCheckout по-умолчанию */
    public $checkoutConfig = [];

    /** @var callable function(CheckoutResponse $response) */
    public $checkoutHandler;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        $this->publicKey = trim((string)$this->publicKey);
        if (empty($this->publicKey)) {
            throw new InvalidConfigException('publicKey');
        }

        $this->privateKey = trim((string)$this->privateKey);
        if (empty($this->privateKey)) {
            throw new InvalidConfigException('privateKey');
        }

        $this->checkoutConfig = array_merge([
            'class' => CheckoutRequest::class,
            'callbackUrl' => Yii::$app instanceof Application ?
                Url::to(['/' . $this->uniqueId . '/checkout'], true) : null,
            'returnUrl' => Yii::$app instanceof Application ?
                Url::to(Yii::$app->homeUrl, true) : null
        ], $this->checkoutConfig ?: []);

        if (! empty($this->checkoutHandler) && ! is_callable($this->checkoutHandler)) {
            throw new InvalidConfigException('checkoutHandler');
        }
    }

    /**
     * Кодирует данные.
     *
     * @param array $data
     * @return string
     */
    public function encodeData(array $data) : string
    {
        // конвертируем значения в строки
        $data = array_map(static function ($val) : string {
            return trim((string)$val);
        }, $data);

        // фильтруем пустые значения
        $data = array_filter($data, static function (string $val) : bool {
            return $val !== '';
        });

        // добавляем ключ
        $data['public_key'] = $this->publicKey;

        // кодируем
        return base64_encode(json_encode($data));
    }

    /**
     * Генерирует подпись данных.
     *
     * @param string $data
     * @return string
     * @link https://www.liqpay.ua/documentation/data_signature
     */
    public function signData(string $data) : string
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
    }

    /**
     * Создает запрос Checkout.
     *
     * @param array $config
     * @return CheckoutRequest
     */
    public function checkoutRequest(array $config = []) : CheckoutRequest
    {
        return new CheckoutRequest($this, array_merge($this->checkoutConfig, $config));
    }
}
