<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'ru',
    'language' => 'ru',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
    ],
];
