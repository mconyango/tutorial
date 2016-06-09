<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\Acl;
use backend\modules\auth\Constants;
use backend\modules\auth\Controller;
use backend\modules\auth\models\Permission;
use backend\modules\auth\models\Resources;
use backend\modules\auth\models\Roles;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

/**
 * Roles management controller
 * @author Fred <mconyango@gmail.com>
 */
class RoleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Role';
        $this->resource = Constants::RES_ROLE;
        $this->activeSubMenu = Constants::SUBMENU_ROLES;
        parent::init();
    }


    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $searchModel = Roles::searchModel(['defaultOrder' => ['name' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);
        /** @var $model Roles */
        $model = Roles::loadModel($id);
        $level_id = $model->level_id;
        $forbidden_items = Acl::getForbiddenItems($level_id === null ? UserLevels::LEVEL_SUPER_ADMIN : $level_id);
        $items = Resources::getResources($forbidden_items);

        $post_key = Permission::shortClassName();
        if (isset($_POST[$post_key])) {
            if (isset($_POST['users'])) {
                Roles::updateRoleUsers($id, $_POST['users']);
            } else {
                Roles::updateRoleUsers($id);
            }
            $permissions = $_POST[$post_key];
            foreach ($permissions as $key => $value) {
                (new Permission())->setPermission($key, $id, $value);
            }

            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));

            return $this->refresh();
        }

        return $this->render('view', [
            'model' => $model,
            'resources' => $items,
        ]);
    }

    public function actionCreate()
    {
        if (!Users::isDev())
            throw new ForbiddenHttpException(Lang::t('403_error'));

        $model = new Roles();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return Json::encode([
                    'success' => true,
                    'message' => Lang::t('SUCCESS_MESSAGE'),
                    'redirectUrl' => Url::getReturnUrl(Url::to(['view', 'id' => $model->id])),
                    'data' => Roles::getListData('id', 'name', true),
                    'selected' => $model->id,
                    'forceRedirect' => true,
                ]);
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
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = Roles::loadModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return Json::encode(['success' => true, 'message' => Lang::t('SUCCESS_MESSAGE'), 'redirectUrl' => Url::getReturnUrl(Url::to(['view', 'id' => $model->id]))]);
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
        if (!Users::isDev())
            throw new ForbiddenHttpException(Lang::t('403_error'));

        $this->hasPrivilege(Acl::ACTION_DELETE);
        Roles::loadModel($id)->delete();
    }
}
