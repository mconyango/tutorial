<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/07
 * Time: 12:16 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\models\Location;
use common\helpers\Lang;

class LocationController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Branch';
        parent::init();
    }


    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);
        $searchModel = Location::searchModel(['defaultOrder' => ['name' => SORT_ASC]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCreate()
    {
        $this->hasPrivilege(Acl::ACTION_CREATE);
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new Location();
        $model->is_active = 1;

        return $model->simpleAjaxSave();
    }


    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = Location::loadModel($id);
        return $model->simpleAjaxSave();
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        Location::softDelete($id);
    }
}