<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Category */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <label class="control-label col-md-2"><?=$model->getAttributeLabel('name')?></label>
        <div class="col-md-8">
            <?=\yii\bootstrap\Html::activeTextInput($model,'name',['class'=>'form-control']);?>
        </div>
    </div>

    <?php //$form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
