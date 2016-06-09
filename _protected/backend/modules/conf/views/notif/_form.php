<?php

use backend\modules\auth\models\Roles;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use common\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\NotifTypes */
/* @var $form yii\bootstrap\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'notif-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'fieldConfig' => [
        'enableError' => false,
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-2',
            'offset' => 'col-md-offset-2',
            'wrapper' => 'col-md-6',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <?= Html::errorSummary($model, ['class' => 'alert alert-danger']); ?>
        <fieldset>
            <legend><?= Lang::t('Notification details') ?></legend>
            <?php if (Users::isDev()): ?>
                <?= $form->field($model, 'id', []); ?>

                <?= $form->field($model, 'name'); ?>

                <?= $form->field($model, 'description')->textarea(['rows' => 3]); ?>

                <?= $form->field($model, 'model_class_name'); ?>

                <?= $form->field($model, 'notification_trigger')->dropDownList(NotifTypes::notificationTriggerOptions()); ?>

                <?= $form->field($model, 'notify_days_before'); ?>

                <?= $form->field($model, 'fa_icon_class'); ?>
            <?php endif ?>
            <?= $form->field($model, 'template')->textarea(['rows' => 3])->hint(
                'Template for displaying notification within this system<br>Please do not remove placeholders (terms enclosed in {{}})'
            ); ?>

            <?= $form->field($model, 'send_email')->checkbox(); ?>

            <div class="form-group">
                <?= Html::activeLabel($model, 'email_template', ['class' => 'control-label col-md-2']) ?>
                <div class="col-md-8">
                    <?=
                    yii\imperavi\Widget::widget([
                        'model' => $model,
                        'attribute' => 'email_template',
                        'options' => [
                            'minHeight' => 150,
                            'replaceDivs' => false,
                            'paragraphize' => true,
                            'cleanOnPaste' => true,
                            'removeWithoutAttr' => [],
                        ],
                    ]); ?>
                    <p class="help-block text-muted">
                        Template for sending the notification as email<br/>
                        Please do not remove placeholders (terms enclosed in {{}})
                    </p>
                </div>

                <?= $form->field($model, 'send_sms')->checkbox(); ?>

                <?= $form->field($model, 'sms_template')->textarea(['rows' => 3])->hint(
                    'Template for sending the notification as sms<br>Please do not remove placeholders (terms enclosed in {{}})'
                ); ?>

                <?= $form->field($model, 'is_active')->checkbox(); ?>
        </fieldset>
        <fieldset>
            <legend><?= Lang::t('Users and/or Roles to notify') ?></legend>

            <?= $form->field($model, 'notify_all_users')->checkbox(); ?>

            <?= $form->field($model, 'users')->dropDownList(
                YII_DEBUG ? Users::getListData('id', 'name', false, '[[level_id]] <>:t1 AND [[status]]=:t2', [':t1' => UserLevels::LEVEL_ADMIN, ':t2' => Users::STATUS_ACTIVE]) : Users::getListData('id', 'name', false, '[[level_id]]<>:t1 AND [[level_id]<>:t2 AND [[status]]=:t3', [':t1' => UserLevels::LEVEL_ADMIN, ':t2' => UserLevels::LEVEL_DEV, ':t3' => Users::STATUS_ACTIVE]),
                ['multiple' => true, 'class' => 'form-control select2']
            ); ?>

            <?= $form->field($model, 'roles')->dropDownList(Roles::getListData('id', 'name'), ['multiple' => true, 'class' => 'select2']); ?>
        </fieldset>
    </div>
</div>
<div class="panel-footer clearfix">
    <div class="pull-right">
        <button class="btn btn-sm btn-primary" type="submit"><i
                class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save changes') ?>
        </button>
        <a class="btn btn-default btn-sm"
           href="<?= Url::getReturnUrl(Url::to(['index'])) ?>"><i
                class="fa fa-times"></i> <?= Lang::t('Cancel') ?></a>
    </div>
</div>
<?php ActiveForm::end(); ?>
</div>