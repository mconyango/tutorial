<?php

use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\forms\EmailSettings */

$this->title = 'Email Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_tab'); ?>
        <div class="tab-content padding-top-10">
            <?php
            $form = ActiveForm::begin([
                'id' => 'settings-form',
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
                    <h3 class="panel-title"><?= $this->title; ?></h3>
                </div>
                <div class="panel-body">
                    <?= Html::errorSummary($model, ['class' => 'alert alert-danger']); ?>

                    <?= $form->field($model, 'email_host'); ?>

                    <?= $form->field($model, 'email_port'); ?>

                    <?= $form->field($model, 'email_username')->hint('e.g noreply@domain.com'); ?>

                    <?= $form->field($model, 'email_password')->passwordInput([])->hint(Lang::t('Password for the username.')); ?>

                    <?= $form->field($model, 'email_security')->dropDownList(['' => 'NULL', 'ssl' => 'SSL', 'tls' => 'TLS'], []); ?>

                    <div class="form-group">
                        <?= Html::activeLabel($model, 'email_theme', ['class' => 'control-label col-md-2']) ?>
                        <div class="col-md-8">
                            <?=
                            yii\imperavi\Widget::widget([
                                'model' => $model,
                                'attribute' => 'email_theme',
                                'options' => [
                                    'minHeight' => 100,
                                    'replaceDivs' => false,
                                    'paragraphize' => true,
                                    'cleanOnPaste' => true,
                                    'removeWithoutAttr' => [],
                                ],
                            ]); ?>
                            <p class="help-block text-muted">
                                <?= Lang::t('Make sure that "{{content}}" placeholder is not removed.'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="panel-footer clearfix">
                    <div class="pull-right">
                        <button class="btn btn-primary" type="submit"><i
                                class="fa fa-check"></i> <?= Lang::t('Save Changes') ?></button>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>