<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/24
 * Time: 4:50 PM
 */

namespace backend\controllers;

use common\controllers\Controller;
use Yii;

class ErrorController extends Controller
{

    public $skipPermissionCheckOnAction = true;

    public $layout = false;

    /**
     * Declares external actions for the controller.
     *
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => \yii\web\ErrorAction::className(),
            ],
        ];
    }

}