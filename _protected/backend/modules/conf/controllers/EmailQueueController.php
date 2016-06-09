<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/03/15
 * Time: 11:37 AM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\models\EmailQueue;

class EmailQueueController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel='Email Queue';
        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $searchModel = EmailQueue::searchModel(['defaultOrder' => ['id' => SORT_DESC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);

        EmailQueue::softDelete($id);
    }

}