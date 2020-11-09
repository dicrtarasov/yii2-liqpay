# LiqPay API клиент для Yii2

API: https://www.liqpay.ua/documentation/api/aquiring/

## Настройка модуля

```php
'modules' => [
    'liqpay' => [
        'class' => dicr\liqpay\LiqPayModule::class,
        'publicKey' => 'ваш_публичный_ключ',
        'privateKey' => 'ваш_приватный ключ',
        // опционально обработчик callback с результатами оплаты
        'checkoutHandler' => static function(dicr\liqpay\CheckoutResponse $response) {
            // сохранение результата оплаты
        }
    ]
];
```

## Использование

```php
use dicr\liqpay\LiqPayModule;

/** @var LiqPayModule $liqpay получаем модуль */
$liqpay = Yii::$app->getModule('liqpay');

// создаем запрос платежа
$request = $liqpay->checkoutRequest([
    'orderId' => 56894,
    'amount' => 1234.23,
    'description' => 'Оплата заказа №56894'
]);

// переадресуем клиента на страницу оплаты
$request->processClient();
```
