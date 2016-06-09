<?php

/* @var $this yii\web\View */
use common\helpers\Lang;
use common\helpers\Url;
use nenad\passwordStrength\PasswordInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $model backend\modules\auth\models\Users */

$this->title = 'Change your password';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-xs-12 col-sm-2">
        <?= $this->render('_userMenu', ['model' => $model]) ?>
    </div>
    <div class="col-xs-12 col-sm-10">
        <?php
        $form = ActiveForm::begin([
            'id' => 'change-password-form',
            'layout' => 'horizontal',
            'enableClientValidation' => false,
            'options' => [],
            'fieldConfig' => [
                'enableError' => false,
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'label' => 'col-md-2',
                    'offset' => 'col-md-offset-2',
                    'wrapper' => 'col-md-4',
                    'error' => '',
                    'hint' => '',
                ],
            ],
        ]);
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-lock"></i> <?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>

                <?= $form->field($model, 'currentPassword')->passwordInput() ?>

                <?= $form->field($model, 'password')->widget(PasswordInput::classname(), []) ?>

                <?= $form->field($model, 'confirm')->passwordInput() ?>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <a class="btn btn-default btn-sm" href="<?= Url::getReturnUrl(Url::to(['update','id' => $model->id])) ?>">
                        <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
                    </a>
                    <button class="btn btn-sm btn-primary" type="submit">
                        <i class="fa fa-check"></i> <?= Lang::t('Change Password') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>