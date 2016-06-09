<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 1:46 AM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\models\Users;
use backend\modules\conf\models\NumberingFormat;
use common\helpers\Lang;
use Yii;
use yii\web\ForbiddenHttpException;

class NumberFormatController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest && !Users::isDev()) {
            throw new ForbiddenHttpException(Lang::t('403_error'));
        }

        $this->resourceLabel = 'Number Format';
        parent::init();
    }

    public function actionIndex()
    {
        $searchModel = NumberingFormat::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $this->pageTitle = Lang::t(self::LABEL_CREATE . ' ' . $this->resourceLabel);

        $model = new NumberingFormat();
        return $model->simpleAjaxSave();
    }

    public function actionUpdate($id)
    {
        $this->pageTitle = Lang::t(self::LABEL_UPDATE . ' ' . $this->resourceLabel);

        $model = NumberingFormat::loadModel($id);
        return $model->simpleAjaxSave();
    }

    public function actionDelete($id)
    {
        NumberingFormat::softDelete($id);
    }
}