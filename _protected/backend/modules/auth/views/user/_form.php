<?php
use backend\modules\auth\Acl;
use backend\modules\auth\models\Roles;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\Timezone;
use common\helpers\Lang;
use common\helpers\Url;
use nenad\passwordStrength\PasswordInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
/* @var $form yii\bootstrap\ActiveForm */
?>
<?php
$can_update = Users::checkPrivilege(Acl::ACTION_UPDATE, false, $model->level_id) && !Users::isMyAccount($model->id);

$form = ActiveForm::begin([
    'id' => 'users-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'options' => ['data-model' => strtolower($model->shortClassName())],
    'fieldConfig' => [
        'enableError' => false,
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-3',
            'offset' => 'col-md-offset-3',
            'wrapper' => 'col-md-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-edit"></i> <?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <?= Html::errorSummary([$model], ['class' => 'alert alert-danger']) ?>

        <div class="row">
            <div class="col-md-6">
                <fieldset class="well">
                    <h4><?= Lang::t('Account details') ?></h4>
                    <hr>
                    <?php if (!$model->getIsNewRecord() && $can_update) : ?>
                        <?= $form->field($model, 'level_id')->dropDownList(Users::userLevelOptions()) ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'role_id')->dropDownList(Roles::getListData('id', 'name', "")) ?>

                    <?= $form->field($model, 'name') ?>

                    <?= $form->field($model, 'email') ?>

                    <?= $form->field($model, 'phone') ?>

                    <?= $form->field($model, 'username') ?>

                    <?php if ($model->isNewRecord): ?>
                        <?= $form->field($model, 'password')->widget(PasswordInput::classname(), []) ?>

                        <?= $form->field($model, 'confirm')->passwordInput() ?>

                        <?= $form->field($model, 'send_email')->checkbox() ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'timezone')->dropDownList(Timezone::getListData()) ?>

                    <?= $this->render('_imageField', ['model' => $model]) ?>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="panel-footer clearfix">
        <div class="pull-right">
            <button class="btn btn-sm btn-primary" type="submit">
                <i class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save Changes') ?>
            </button>
            <a class="btn btn-default btn-sm"
               href="<?= Url::getReturnUrl($model->isNewRecord ? Url::to(['index']) : Url::to(['view', 'id' => $model->id])) ?>">
                <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
            </a>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
