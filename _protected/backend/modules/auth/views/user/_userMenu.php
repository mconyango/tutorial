<?php
use backend\modules\auth\Acl;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
$can_update = Users::checkPrivilege(Acl::ACTION_UPDATE, $model->level_id);
?>
    <div class="text-center">
        <img id="avator" class="editable img-responsive thumbnail"
             src="<?= Users::getProfileImageUrl($model->id, 256, $model->profile_image) ?>"
             style="margin-left:auto;margin-right:auto;">
        <p><i class="fa fa-star"></i> <?= UserLevels::getFieldByPk($model->level_id, 'name'); ?></p>

        <p><span class="label <?=$model->status===Users::STATUS_ACTIVE?'label-success':'label-danger'?>"> <?= Users::decodeStatus($model->status); ?></span></p>

        <p><i class="fa fa-envelope-o"></i> <?= Html::encode($model->email); ?></p>
        <?php if (!empty($model->phone)): ?>
            <p><i class="fa fa-phone"></i> <?= Html::encode($model->phone) ?></p>
        <?php endif; ?>
    </div>

    <div class="list-group">
        <?php if (Users::isMyAccount($model->id)): ?>
            <a class="list-group-item" href="<?= Url::to(['change-password']) ?>">
                <i class="fa fa-lock text-success"></i> <?= Lang::t('Change your password') ?>
            </a>
        <?php endif; ?>

        <?php if ($can_update): ?>
            <?php if ($model->status === Users::STATUS_ACTIVE): ?>
                <a class="list-group-item change-user-status" href="#"
                   data-href="<?= Url::to(['change-status', 'id' => $model->id, 'status' => Users::STATUS_BLOCKED]) ?>">
                    <i class="fa fa-ban text-danger"></i> <?= Lang::t('Block Account') ?>
                </a>
            <?php else: ?>
                <a class="list-group-item change-user-status" href="#"
                   data-href="<?= Url::to(['change-status', 'id' => $model->id, 'status' => Users::STATUS_ACTIVE]) ?>">
                    <i class="fa fa-check-circle text-success"></i> <?= Lang::t('Activate Account') ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php
$options = [];
$this->registerJs("MyApp.modules.auth.user(" . \yii\helpers\Json::encode($options) . ");");
?>