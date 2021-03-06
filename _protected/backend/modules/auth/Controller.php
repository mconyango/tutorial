<?php
namespace backend\modules\auth;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/02
 * Time: 5:43 PM
 */
class Controller extends \common\controllers\Controller
{
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        if (empty($this->activeMenu))
            $this->activeMenu = Constants::MENU_USER_MANAGEMENT;
        if (empty($this->resource))
            $this->resource = Constants::RES_USER;

        parent::init();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'upload-image', 'change-password', 'change-status', 'filter'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'change-status' => ['post'],
                ],
            ],
        ];
    }
}