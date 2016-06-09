<?php

use backend\modules\customer\models\Customer;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\modules\conf\models\MpesaApiCredentials */

$this->title = $this->context->pageTitle;

$form = ActiveForm::begin([
    'id' => 'my-modal-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'options' => ['data-model' => strtolower($model->shortClassName())],
    'fieldConfig' => [
        'enableError' => false,
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-3',
            'offset' => 'col-md-offset-3',
            'wrapper' => 'col-md-6',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?= Html::encode($this->title); ?></h4>
    </div>

    <div class="modal-body">
        <div class="alert hidden" id="my-modal-notif"></div>

        <?= $form->field($model, 'description', []); ?>

        <?= $form->field($model, 'sp_id', []); ?>

        <?= $form->field($model, 'password', [])->passwordInput(['class'=>'form-control']); ?>

        <?= $form->field($model, 'service_id', []); ?>

        <?= $form->field($model, 'initiator_identifier', []); ?>

        <?= $form->field($model, 'initiator_password', [])->passwordInput(['class'=>'form-control']); ?>

        <?= $form->field($model, 'organization_shortcode', []); ?>

        <?= $form->field($model, 'customer_id', [])->dropDownList(Customer::getListData(),['class'=>'select2']); ?>

        <?= $form->field($model, 'is_active', [])->checkbox(); ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" type="submit"><i
                class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save changes') ?></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i
                class="fa fa-times"></i> <?= Lang::t('Close') ?></button>
    </div>
<?php ActiveForm::end(); ?>