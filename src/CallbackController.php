<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 10.11.20 02:53:18
 */

declare(strict_types = 1);

namespace dicr\liqpay;

use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;

use function base64_decode;
use function call_user_func;

/**
 * Контроллер обработки запросов LiqPay Checkout с результатами платежей.
 *
 * @property-read LiqPayModule $module
 * @property-read Request $request
 */
class CallbackController extends Controller
{
    /**
     * {@inheritDoc}
     * LiqPay отправляет запросы без CSRF
     */
    public $enableCsrfValidation = false;

    /**
     * Обработчик запросов от LiqPay со статусом платежей.
     *
     * @throws BadRequestHttpException
     */
    public function actionIndex() : void
    {
        if (! $this->request->isPost) {
            throw new BadRequestHttpException('post');
        }

        Yii::debug('Callback: ' . $this->request->rawBody, __METHOD__);

        // получаем данные
        $data = Yii::$app->request->post('data');
        if (empty($data)) {
            throw new BadRequestHttpException('data');
        }

        // проверяем сигнатуру
        $signature = Yii::$app->request->post('signature');
        if (empty($signature) || $signature !== $this->module->signData($data)) {
            throw new BadRequestHttpException('signature');
        }

        if (! empty($this->module->checkoutHandler)) {
            $json = Json::decode(base64_decode($data));
            if (empty($json)) {
                throw new BadRequestHttpException('invalid data json');
            }

            // инициализируем модель ответа
            $response = new CheckoutResponse();

            // устанавливаем через setAttributes
            $response->setAttributes($json, false);

            // вызываем обработчик
            call_user_func($this->module->checkoutHandler, $response);
        }
    }
}
