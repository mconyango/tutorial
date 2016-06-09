<?php

/* @var $this yii\web\View */
use common\helpers\Lang;

/* @var $searchModel backend\modules\auth\models\UserLevels */

$this->title = Lang::t('User Levels');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/auth/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>
