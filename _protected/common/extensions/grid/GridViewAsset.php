<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 8:46 PM
 */

namespace common\extensions\grid;


use yii\web\AssetBundle;

class GridViewAsset extends AssetBundle
{
    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        parent::init();
    }

    public $js = [
    ];

    public $css = [
        'css/custom.css',
    ];
    public $depends = [
        'common\assets\CommonAsset',
    ];
}