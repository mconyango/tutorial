<?php
use backend\modules\conf\Constants;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<header id="header">
    <div id="logo-group">
        <!-- PLACE YOUR LOGO HERE -->
        <span id="logo" class="logo-text"><a
                href="<?= Yii::$app->homeUrl ?>"><?= Yii::$app->setting->get(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME,Yii::$app->name); ?></a></span>
        <?php //echo $this->render('notif.views.layouts.notif') ?>
    </div>
    <!-- pulled right: nav area -->
    <div class="pull-left hidden" style="margin-left: 20px">
        <h1 style="color: #fff">
            <?= Yii::$app->setting->get(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME, Yii::$app->name); ?>
        </h1>
    </div>
    <div class="pull-right">
        <div id="hide-menu" class="btn-header pull-right">
            <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i
                        class="fa fa-reorder"></i></a> </span>
        </div>
        <ul id="mobile-profile-img" class="header-dropdown-list hidden-xs padding-5"
            style="display: block!important;padding-right: 2px!important;padding-left: 2px!important;">
        </ul>
    </div>
</header>