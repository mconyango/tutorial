<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/08
 * Time: 5:08 PM
 */

namespace backend\modules\conf\controllers;


use api\models\OauthClients;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use Yii;
use yii\web\ForbiddenHttpException;

class OauthClientController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->isGuest && !Users::isDev()) {
            throw new ForbiddenHttpException(Lang::t('403_error'));
        }
        $this->resourceLabel = 'Oauth Client';
        parent::init();
    }

    public function actionIndex()
    {
        $searchModel = OauthClients::searchModel(['defaultOrder' => ['client_id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new OauthClients();
        return $model->simpleAjaxSave();
    }


    public function actionUpdate($id)
    {
        $model = OauthClients::loadModel($id);
        return $model->simpleAjaxSave();
    }


    public function actionDelete($id)
    {
        OauthClients::softDelete($id);
    }
}