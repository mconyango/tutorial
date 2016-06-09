<?php

/* @var $this yii\web\View */

$this->title = 'Manage Branches';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>