<?php
return [
    'name' => 'JS EXAMPLES',
    //'language' => 'sr',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules.php'),
    'timeZone' => 'UTC',
    'components' => [
        'db' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db.php'),
        'assetManager' => [
            'linkAssets' => true,
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'urlManager' => [
            'class' => yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'session' => [
            'class' => yii\web\DbSession::class,
            'sessionTable' => '{{app_session}}',
            'cookieParams' => ['httponly' => true, 'lifetime' => 3600 * 4],
            'timeout' => 3600 * 4,
            'useCookies' => true,
            'name' => 'PHPBACKENDSESSIONID',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\DbTarget::class,
                    'levels' => ['error'],
                    'logTable' => '{{%conf_log}}',
                    'enabled' => false,
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                    'except' => ['yii\db*', 'yii\web\Session*'],
                    'fileMode' => 0664,
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/translations',
                    'sourceLanguage' => 'en',
                ],
                'yii' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/translations',
                    'sourceLanguage' => 'en'
                ],
            ],
        ],
        'formatter' => [
            'class' => \yii\i18n\Formatter::class,
            'dateFormat' => 'php:d-M-Y',
            'datetimeFormat' => 'php:d-M-Y H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],
        //setting
        'setting' => [
            'class' => common\components\Setting::class,
            'settingTable' => 'conf_setting',
        ],
        'localTime'=>[
            'class'=>\common\components\LocalTime::class,
        ]
    ], // components

    // set allias for our uploads folder so it can be shared by both frontend and backend applications
    // @appRoot alias is definded in common/config/bootstrap.php file
    'aliases' => [
        '@commonAssets' => '@common/assets',
    ],
];
