<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'HouseOfPayments-Backend',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'default/index',
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log', 'gii', 'debug'],
    'modules' => [],
    'components' => [
        // here you can set theme used for your backend application
        // - template comes with: 'default', 'slate', 'spacelab' and 'cerulean'
        'view' => [
            'theme' => [
                'pathMap' => ['@app/views' => '@webroot/themes/default/views'],
                'baseUrl' => '@web/themes/default',
                'basePath' => '@webroot/themes/default',
            ],
        ],
        'user' => [
            'class' => \common\components\User::class,
            'identityClass' => \backend\modules\auth\models\Users::class,
            'enableAutoLogin' => true,
            'autoRenewCookie' => true,
            'loginUrl' => ['auth/auth/login'],
            'as authLog' => [
                'class' => yii2tech\authlog\AuthLogWebUserBehavior::class,
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
        'request' => [
            'enableCookieValidation' => true,
            'enableCsrfValidation' => true,
            'cookieValidationKey' => '5eBjETPtBWgCTiqZ7z2zlQ8ewRCEc675',
        ],
    ],
    'params' => $params,
];
