<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 6:43 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class NotifController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Notification';

        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $searchModel = NotifTypes::searchModel(['defaultOrder' => ['name' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        if (!Users::isDev())
            throw new ForbiddenHttpException(Lang::t('403_error'));

        $model = new NotifTypes();
        return $model->simpleSave('create');
    }

    public function actionUpdate($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = NotifTypes::loadModel($id);
        return $model->simpleSave('update', 'update');
    }

    public function actionDelete($id)
    {
        $this->hasPrivilege(Acl::ACTION_DELETE);
        NotifTypes::softDelete($id);
    }

    public function actionFetch()
    {
        $html = $this->renderPartial('fetch', [
            'data' => Notif::fetchNotif(),
        ]);

        return Json::encode(['html' => $html, 'unseen' => (int)Notif::getTotalUnSeenNotif(), 'total' => (int)Notif::getCount(['user_id' => Yii::$app->user->id])]);
    }

    public function actionMarkAsRead($id = NULL)
    {
        Notif::markAsRead($id);
        return Json::encode(TRUE);
    }

    public function actionMarkAsSeen()
    {
        Notif::markAsSeen();
    }
}