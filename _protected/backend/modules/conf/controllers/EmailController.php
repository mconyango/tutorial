<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 5:59 PM
 */

namespace backend\modules\conf\controllers;

use backend\modules\auth\Acl;
use backend\modules\conf\Constants;
use backend\modules\conf\forms\EmailSettings;
use backend\modules\conf\models\EmailTemplate;
use common\helpers\Lang;
use Yii;

class EmailController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Email Template';
        $this->activeSubMenu = Constants::SUBMENU_EMAIL;

        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $searchModel = EmailTemplate::searchModel(['defaultOrder' => ['name' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);

        $model = new EmailTemplate();
        return $model->simpleSave('create');
    }

    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = EmailTemplate::loadModel($id);
        return $model->simpleSave('update', 'update');
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        EmailTemplate::softDelete($id);
    }

    public function actionSettings()
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = new EmailSettings();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));
            return $this->refresh();
        }

        return $this->render('settings', [
            'model' => $model,
        ]);
    }


}