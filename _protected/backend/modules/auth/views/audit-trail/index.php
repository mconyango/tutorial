<?php

/* @var $this yii\web\View */

$this->title = 'Audit Trail';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/auth/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_filter', ['filterOptions' => $filterOptions]); ?>
        <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>
