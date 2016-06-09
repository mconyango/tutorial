<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 3:01 AM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\JobProcesses;
use backend\modules\conf\models\Jobs;
use common\helpers\Lang;
use Yii;
use yii\web\ForbiddenHttpException;

class JobManagerController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest && !Users::isDev()) {
            throw new ForbiddenHttpException(Lang::t('403_error'));
        }
        $this->resourceLabel = 'Job';
        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege();

        $searchModel = Jobs::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        $this->hasPrivilege();

        $job = Jobs::loadModel($id);

        return $this->render('view', [
            'searchModel' => JobProcesses::searchModel(['condition' => ['job_id' => $id], 'defaultOrder' => ['created_at' => SORT_DESC]]),
            'job' => $job,
        ]);
    }

    public function actionCreate()
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);

        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new Jobs();
        return $model->simpleAjaxSave();
    }

    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = Jobs::loadModel($id);
        return $model->simpleAjaxSave();
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        Jobs::softDelete($id);
    }

    public function actionStart($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        Jobs::startJob($id);
    }

    public function actionStop($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        Jobs::stopJob($id);
    }
}