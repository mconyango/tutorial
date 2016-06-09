<?php
/* @var $this yii\web\View */
use backend\modules\auth\models\Users;
use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\helpers\Url;

?>
<div class="list-group my-list-group">
    <a href="<?= Url::to(['/conf/settings/index']) ?>" class="list-group-item">
        <?= Lang::t('General Settings') ?>
    </a>

    <a href="<?= Url::to(['/conf/bank/index']) ?>" class="list-group-item">
        <?= Lang::t('Banks') ?>
    </a>

    <a href="<?= Url::to(['/conf/email/index']) ?>"
       class="list-group-item <?= Yii::$app->controller->isSubMenuActive(Constants::SUBMENU_EMAIL) ? ' active' : '' ?>">
        <?= Lang::t('Email Settings') ?>
    </a>

    <a href="<?= Url::to(['/conf/notif/index']) ?>" class="list-group-item">
        <?= Lang::t('Notifications') ?>
    </a>

    <a href="<?= Url::to(['/conf/location/index']) ?>" class="list-group-item">
        <?= Lang::t('Organization Structure') ?>
    </a>

    <a href="<?= Url::to(['/conf/mpesa-api/index']) ?>" class="list-group-item">
        <?= Lang::t('M-PESA API Credentials') ?>
    </a>

    <?php if (Users::isDev()): ?>
        <a href="<?= Url::to(['/conf/oauth-client/index']) ?>" class="list-group-item">
            <?= Lang::t('Manage Apps') ?>
        </a>

        <a href="<?= Url::to(['/conf/number-format/index']) ?>" class="list-group-item">
            <?= Lang::t('Numbering Format') ?>
        </a>

        <a href="<?= Url::to(['/conf/job-manager/index']) ?>" class="list-group-item">
            <?= Lang::t('Job Manager') ?>
        </a>
    <?php endif; ?>

    <a href="<?= Url::to(['/conf/email-queue/index']) ?>" class="list-group-item"><?= Lang::t('Email Queue') ?></a>

    <?php if (Users::isDev()): ?>
        <a href="<?= Url::to(['/conf/settings/runtime']) ?>" class="list-group-item"><?= Lang::t('Runtime Logs') ?></a>
    <?php endif; ?>
</div>
