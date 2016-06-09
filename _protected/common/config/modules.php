<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/02
 * Time: 4:56 PM
 */
return [
    'gridview' => [
        'class' => '\kartik\grid\Module'
        // enter optional module parameters below - only if you need to
        // use your own export download action or custom translation
        // message source
        // 'downloadAction' => 'gridview/export/download',
        // 'i18n' => []
    ],
    // for code generation
    'gii' => [
        'class' => yii\gii\Module::class,
    ],
    'debug' => [
        'class' => yii\debug\Module::class,
    ],
    'auth' => [
        'class' => backend\modules\auth\Module::class,
    ],
    'conf' => [
        'class' => backend\modules\conf\Module::class,
    ],
    'core' => [
        'class' => backend\modules\core\Module::class,
    ],
    'reports' => [
        'class' => backend\modules\reports\Module::class,
    ],
    'customer' => [
        'class' => backend\modules\customer\Module::class,
    ],
    'help' => [
        'class' => backend\modules\help\Module::class,
    ],
];