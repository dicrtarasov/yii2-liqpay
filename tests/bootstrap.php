<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 05.11.20 05:44:22
 */

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types = 1);

/** string */

define('YII_ENV', 'dev');

/** bool */
define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

new yii\console\Application([
    'id' => 'test',
    'basePath' => __DIR__,
    'components' => [
        'cache' => yii\caching\ArrayCache::class,
        'urlManager' => [
            'hostInfo' => 'https://dicr.org'
        ]
    ],
    'modules' => [
        'liqpay' => dicr\liqpay\LiqPayModule::class,
        'publicKey' => 'xxxxxx',
        'privateKey' => 'xxxxxx'
    ]
]);
