<?php

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/24
 * Time: 4:50 PM
 */

namespace backend\controllers;

use api\models\Users;
use backend\models\Subcategory;
use common\controllers\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DefaultController extends Controller
{

    public $skipPermissionCheckOnAction = true;

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['error', 'index', 'filter-subcategory'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Declares external actions for the controller.
     *
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::className(),
            ],
        ];
    }

    /**
     * Lists all UserLevels models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->activeMenu = 1;
        return $this->render('index', []);
    }

    public function actionFilterSubcategory($category_id)
    {
        $data = Subcategory::getListData('id', 'name', false, ['category_id' => $category_id]);
        echo json_encode($data);
    }

}
