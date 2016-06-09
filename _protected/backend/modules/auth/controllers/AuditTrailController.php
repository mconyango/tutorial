<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/05/05
 * Time: 2:42 PM
 */

namespace backend\modules\auth\controllers;


use backend\modules\auth\Controller;
use backend\modules\auth\models\AuditTrail;
use common\helpers\DateUtils;

class AuditTrailController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Audit Trail';
        parent::init();
    }

    public function actionIndex($user_id = null, $from = null, $to = null)
    {
        $this->hasPrivilege();

        $condition = '';
        $params = [];
        $date_filter = DateUtils::getDateFilterParams($from, $to, 'created_at', true, true);
        if (!empty($date_filter['condition'])) {
            if (!empty($condition))
                $condition .= ' AND ';
            $condition .= $date_filter['condition'];
        }

        /* @var $searchModel AuditTrail */
        $searchModel = AuditTrail::searchModel(['defaultOrder' => ['id' => SORT_DESC], 'condition' => $condition, 'params' => $params]);
        $searchModel->user_id = $user_id;

        return $this->render('index', [
            'filterOptions' => [
                'user_id' => $user_id,
                'from' => $from,
                'to' => $to,
            ],
            'searchModel' => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        $this->hasPrivilege();

        $model = AuditTrail::loadModel($id);

        return $this->renderPartial('view', [
            'model' => $model,
        ]);
    }

}