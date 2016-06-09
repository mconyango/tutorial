<?php
/**
 * -----------------------------------------------------------------------------
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * -----------------------------------------------------------------------------
 */

namespace backend\assets;

use common\assets\CommonAsset;
use Yii;
use yii\web\AssetBundle;

// set @themes alias so we do not have to update baseUrl every time we change themes
Yii::setAlias('@themes', Yii::$app->view->theme->baseUrl);

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 *
 * Customized by Nenad Živković
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@themes';

    public $css = [
        'css/smartadmin-production.min.css',
        'css/smartadmin-skins.min.css',
        'css/theme-custom.css',
        'css/custom.css',
    ];
    public $js = [
        'js/app.config.js',
        'js/app.js',
    ];
    public $depends = [
        CommonAsset::class,
    ];
}
