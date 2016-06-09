<?php

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/23
 * Time: 12:58 PM
 */

namespace common\assets;

use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

/**
 * Class CommonAsset
 * All custom assets created by the developer
 *
 * @package common\assets
 */
class CommonAsset extends AssetBundle
{

    public $sourcePath = '@commonAssets/assets';

    public $css = [
        'css/custom.css',
    ];

    public $js = [
        'js/myapp.js',
        'js/plugins.js',
        'js/modules/tutorial.js',
        'js/script.js',
    ];
    public $depends = [
        JqueryAsset::class,
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        BowerAsset::class
    ];

}
