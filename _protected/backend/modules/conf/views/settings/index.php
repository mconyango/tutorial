<?php
/* @var $this yii\web\View */

use common\helpers\Lang;
use common\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $model backend\modules\conf\forms\Settings */
$this->title = Lang::t('Settings');
$this->params['breadcrumbs'] = [
    $this->title
];
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
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
                <fieldset>
                    <legend><?= Lang::t('Company details') ?></legend>

                    <?= $form->field($model, 'company_name'); ?>

                    <?= $form->field($model, 'app_name'); ?>

                    <?= $form->field($model, 'company_email'); ?>

                    <?= $form->field($model, 'default_timezone')->dropDownList(\backend\modules\conf\models\Timezone::getListData(), []); ?>

                    <?= $form->field($model, 'country_id')->dropDownList(\backend\modules\conf\models\CountryReference::getListData(), []); ?>
                </fieldset>

                <fieldset>
                    <legend><?= Lang::t('Display options') ?></legend>

                    <?= $form->field($model, 'items_per_page')->dropDownList(\common\helpers\Utils::generateIntegersList(10, 200, 5), []); ?>
                </fieldset>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit"><i
                            class="fa fa-check"></i> <?= Lang::t('Save Changes') ?></button>
                    <a class="btn btn-default" href="<?= Url::getReturnUrl(Url::to(['index'])) ?>"><i
                            class="fa fa-times"></i> <?= Lang::t('Cancel') ?></a>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>