<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 12:30:11
 */

declare(strict_types = 1);
namespace dicr\liqpay;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\Url;
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
 * @noinspection PhpUnused
 */
class LiqPayModule extends Module implements LiqPay
{
    /** @var string */
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
    public function init()
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
            'callbackUrl' => Url::to(['/' . $this->uniqueId . '/checkout'], true),
            'returnUrl' => Url::to(Yii::$app->homeUrl, true)
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
    public function encodeData(array $data)
    {
        // конвертируем значения в строки
        $data = array_map(static function($val) {
            return trim((string)$val);
        }, $data);

        // фильтруем пустые значения
        $data = array_filter($data, static function(string $val) {
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
    public function signData(string $data)
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, 1));
    }

    /**
     * Создает запрос Checkout.
     *
     * @param array $config
     * @return CheckoutRequest
     * @throws InvalidConfigException
     */
    public function checkoutRequest(array $config = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject(array_merge($this->checkoutConfig, $config), [$this]);
    }
}
