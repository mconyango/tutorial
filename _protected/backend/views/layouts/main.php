<?php

use backend\assets\AppAsset;
use backend\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/ico" href="<?=Yii::$app->urlManager->baseUrl?>/favicon.ico" />
    <?= Html::csrfMetaTags() ?>
    <title><?= !empty($this->title)? Html::encode($this->title):\Yii::$app->setting->get(\backend\modules\conf\Constants::SECTION_SYSTEM, \backend\modules\conf\Constants::KEY_APP_NAME, Yii::$app->name) ?></title>
    <?php $this->head(); ?>
</head>
<body class="fixed-header menu-on-top smart-style-3 fixed-navigation">
<?php $this->beginBody() ?>
<!--HEADER SECTION-->
<?= $this->render('@app/views/layouts/header') ?>
<!--END HEADER SECTION-->
<!--MENU SECTION-->
<?= $this->render('@app/views/layouts/menu') ?>
<!--END MENU SECTION-->
<!--MAIN PANEL-->
<div id="main" role="main">
    <!-- RIBBON -->
    <div id="ribbon">
        <!-- breadcrumb -->
        <?= Breadcrumbs::widget([
            'options' => ['class' => 'breadcrumb'],
            'itemTemplate' => "<li>{link}</li>\n", // template for all links
            'activeItemTemplate' => "<li class=\"active\">{link}</li>\n",
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <!-- end breadcrumb -->
    </div>
    <!-- END RIBBON -->
    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row" style="margin-left: -13px;margin-right: -13px">
            <div class="col-md-12">
                <?= Alert::widget(); ?>
                <?= $content; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->render('@app/views/layouts/footer') ?>
<!--END MAIN PANEL-->
<!--modal-->
<div class="modal fade" id="my_bs_modal" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        </div>
    </div>
</div>
<!--end modal-->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
