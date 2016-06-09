<?php
namespace backend\modules\auth\controllers;

use backend\modules\auth\Constants;
use backend\modules\auth\models\Resources;
use backend\modules\auth\models\Users;
use Yii;
use backend\modules\auth\Controller;
use common\helpers\Lang;
use yii\web\ForbiddenHttpException;

/**
 * Manage system resources: This controller should only be accessed by system engineer/author
 * @author Fred <mconyango@gmail.com>
 */
class ResourceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest && !Users::isDev()) {
            throw new ForbiddenHttpException(Lang::t('403_error'));
        }

        $this->resourceLabel = 'Resource';
        $this->activeSubMenu = Constants::SUBMENU_RESOURCES;

        parent::init();
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = Resources::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Resources();
        return $model->simpleAjaxSave();
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = Resources::loadModel($id);
        return $model->simpleAjaxSave();
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        Resources::softDelete($id);
    }
}