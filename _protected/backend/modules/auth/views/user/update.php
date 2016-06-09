<?php

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title =\common\helpers\Lang::t('Update {level}',['level'=>\backend\modules\auth\models\UserLevels::getFieldByPk($model->level_id,'name')]);
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index','level_id'=>$model->level_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-xs-12 col-sm-2">
        <?= $this->render('_userMenu', ['model' => $model]) ?>
    </div>
    <div class="col-xs-12 col-sm-10">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>