<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\Acl;
use backend\modules\auth\models\Roles;
use backend\modules\auth\models\UserLevels;
use common\controllers\FineUploaderAction;
use common\helpers\Lang;
use Yii;
use backend\modules\auth\models\Users;
use backend\modules\auth\Controller;
use yii\web\BadRequestHttpException;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'User';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-image' => [
                'class' => FineUploaderAction::className(),
            ],
        ];
    }


    public function actionIndex($level_id = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);
        list($condition, $params) = Users::getFetchCondition(['status' => 1]);
        /* @var $searchModel Users */
        $searchModel = Users::searchModel(['defaultOrder' => ['username' => SORT_ASC], 'condition' => $condition, 'params' => $params]);
        $searchModel->level_id = $level_id;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'level_id' => $level_id,
        ]);
    }

    public function actionView($id)
    {
        return $this->redirect(['update', 'id' => $id]);
    }


    public function actionCreate($level_id = null)
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);

        $model = new Users();
        $model->level_id = $level_id;
        $model->status = Users::STATUS_ACTIVE;
        if ($model->level_id != UserLevels::LEVEL_DEV) {
            $model->role_id = Roles::getScalar('id', ['level_id' => $model->level_id]);
        }
        $model->setScenario(Users::SCENARIO_CREATE);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));
            if ($model->send_email) {
                $model->sendCreatedUserEmail();
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        /* @var $model Users */
        $model = Users::loadModel($id);
        if (!Users::isMyAccount($id)) {
            Users::checkPrivilege(Acl::ACTION_UPDATE, $model->level_id, true);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id)
    {
        /* @var $model Users */
        $model = Users::loadModel($id);
        Users::checkPrivilege(Acl::ACTION_DELETE, $model->level_id, true);
        Users::softDelete($id);
    }

    public function actionChangePassword()
    {
        $model = Users::loadModel(Yii::$app->user->id);
        $model->setScenario(Users::SCENARIO_CHANGE_PASSWORD);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('Password changed successfully.'));
            return $this->refresh();
        }

        return $this->render('changePassword', [
            'model' => $model,
        ]);
    }


    public function actionChangeStatus($id, $status)
    {
        /* @var $model Users */
        $model = Users::loadModel($id);
        Users::checkPrivilege(Acl::ACTION_UPDATE, $model->level_id, true);

        $valid_status = array_keys(Users::statusOptions());
        if (!in_array($status, $valid_status)) {
            throw new BadRequestHttpException(Lang::t('400_error'));
        }
        $model->status = $status;

        $response = ['success' => false];
        if ($model->save(false)) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('Success.'));
            $response['success'] = true;
        }

        return json_encode($response);
    }
}