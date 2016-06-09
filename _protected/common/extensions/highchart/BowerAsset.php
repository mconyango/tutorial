<?php
/**
 * Created by PhpStorm.
 * User: mconyango
 * Date: 1/27/16
 * Time: 12:53 PM
 */

namespace common\extensions\highchart;


class BowerAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@bower';

    public $css = [
        'bootstrap-daterangepicker/daterangepicker.css',
    ];

    public $js = [
        'highcharts/highcharts.js',
        'highcharts/highcharts-3d.js',
        'highcharts/modules/exporting.js',
        'moment/min/moment.min.js',
        'bootstrap-daterangepicker/daterangepicker.js',
    ];

    public $depends = [
        \common\assets\CommonAsset::class,
    ];
}