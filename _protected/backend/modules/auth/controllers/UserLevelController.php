<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\Constants;
use backend\modules\auth\Controller;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

/**
 * LevelController implements the CRUD actions for UserLevels model.
 */
class UserLevelController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest && !Users::isDev()) {
            throw new ForbiddenHttpException(Lang::t('403_error'));
        }
        $this->resourceLabel = 'User Level';
        $this->activeSubMenu = Constants::SUBMENU_USER_LEVELS;
        parent::init();
    }


    public function actionIndex()
    {
        $searchModel = UserLevels::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCreate()
    {
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new UserLevels();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return Json::encode(['success' => true, 'message' => Lang::t('SUCCESS_MESSAGE'), 'redirectUrl' => Url::getReturnUrl(Url::to(['index']))]);
            } else {
                return Json::encode(['success' => false, 'message' => $model->getErrors()]);
            }
        }

        return $this->renderPartial('_form', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = UserLevels::loadModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return Json::encode(['success' => true, 'message' => Lang::t('SUCCESS_MESSAGE'), 'redirectUrl' => Url::getReturnUrl(Url::to(['index']))]);
            } else {
                return Json::encode(['success' => false, 'message' => $model->getErrors()]);
            }
        }

        return $this->renderPartial('_form', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id)
    {
        UserLevels::loadModel($id)->delete();
    }
}
