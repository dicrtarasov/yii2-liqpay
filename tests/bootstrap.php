<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 04.01.22 22:36:24
 */

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types = 1);

/** string */
const YII_ENV = 'dev';

/** bool */
const YII_DEBUG = true;

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
        'liqpay' => [
            'class' => dicr\liqpay\LiqPayModule::class,
            'publicKey' => 'xxxxx',
            'privateKey' => 'xxxxx',
            'debug' => true
        ]
    ]
]);
