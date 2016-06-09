<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\auth\models\Users */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \common\helpers\Lang::t('Manage {user}',['user'=>empty($level_id)?'Users':\backend\modules\auth\models\UserLevels::getFieldByPk($level_id,'name')]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/auth/views/layouts/submenu',['level_id'=>$level_id]); ?>
    </div>
    <div class="col-md-10">
            <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>
