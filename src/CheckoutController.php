<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 11:48:53
 */

/** @noinspection PhpUnused */
declare(strict_types = 1);

namespace dicr\liqpay;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use function base64_decode;
use function call_user_func;
use function json_decode;

/**
 * Контроллер обработки запросов LiqPay Checkout с результатами платежей.
 *
 * @property-read LiqPayModule $module
 */
class CheckoutController extends Controller
{
    /** @var bool LiqPay отправляет запросы без CSRF */
    public $enableCsrfValidation = false;

    /**
     * Обработчик запросов от LiqPay со статусом платежей.
     *
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
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
            $json = json_decode(base64_decode($data), true);
            if (empty($json)) {
                throw new BadRequestHttpException('invalid data json');
            }

            // инициализируем модель ответа
            $response = new CheckoutResponse();

            // устанавливаем через setAttributes с safe = true
            $response->attributes = $json;

            // вызываем обработчик
            call_user_func($this->module->checkoutHandler, $response);
        } else {
            // пишем ответ в логи
            Yii::info(base64_decode($data), __METHOD__);
        }
    }
}
