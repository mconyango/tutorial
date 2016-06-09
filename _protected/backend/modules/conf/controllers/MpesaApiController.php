<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/07
 * Time: 4:46 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\models\MpesaApiCredentials;
use common\helpers\Lang;

class MpesaApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'M-PESA API Credential';
        parent::init();
    }


    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $searchModel = MpesaApiCredentials::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCreate()
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new MpesaApiCredentials();
        return $model->simpleAjaxSave();
    }


    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = MpesaApiCredentials::loadModel($id);
        return $model->simpleAjaxSave();
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        MpesaApiCredentials::softDelete($id);
    }
}