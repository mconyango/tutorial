<?php

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title =\common\helpers\Lang::t('Create {level}',['level'=>\backend\modules\auth\models\UserLevels::getFieldByPk($model->level_id,'name')]);
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index','level_id'=>$model->level_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model,'client'=>$client]) ?>
    </div>
</div>
