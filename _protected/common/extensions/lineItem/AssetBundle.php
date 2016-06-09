<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/11
 * Time: 2:53 PM
 */

namespace common\extensions\lineItem;


class AssetBundle extends \yii\web\AssetBundle
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
        'js/lineItem.js',
    ];

    public $css = [
    ];

    public $depends = [
        'common\assets\CommonAsset',
    ];
}