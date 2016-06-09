<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/01/18
 * Time: 3:22 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\models\BankBranches;
use backend\modules\conf\models\BankNames;
use common\helpers\Lang;

class BankBranchesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Branch';
        parent::init();
    }

    public function actionIndex($bank_id)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);
        $searchModel = BankBranches::searchModel(['condition' => ['bank_id' => $bank_id], 'defaultOrder' => ['branch_code' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'bank' => BankNames::loadModel($bank_id),
        ]);
    }

    public function actionCreate($bank_id)
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new BankBranches();
        $model->bank_id = $bank_id;
        return $model->simpleAjaxSave('_form', 'index', ['bank_id' => $bank_id]);
    }

    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        /* @var $model BankBranches */
        $model = BankBranches::loadModel($id);
        return $model->simpleAjaxSave('_form', 'index', ['bank_id' => $model->bank_id]);
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        BankBranches::softDelete($id);
    }


}