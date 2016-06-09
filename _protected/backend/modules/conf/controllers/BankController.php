<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/01/18
 * Time: 2:58 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\models\BankNames;
use common\helpers\Lang;

class BankController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Bank';
        parent::init();
    }


    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);
        $searchModel = BankNames::searchModel(['defaultOrder' => ['bank_code' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCreate()
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new BankNames();
        return $model->simpleAjaxSave();
    }


    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = BankNames::loadModel($id);
        return $model->simpleAjaxSave();
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        BankNames::softDelete($id);
    }

}