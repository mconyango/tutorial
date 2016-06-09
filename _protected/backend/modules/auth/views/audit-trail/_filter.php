<?php

use common\helpers\Lang;
use common\helpers\Url;
use yii\bootstrap\Html;

?>
<?= Html::beginForm(Url::to(['index']), 'get', ['class' => '']) ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Lang::t('Filters:') ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <?= Html::label('From', "", ['class' => 'control-label']) ?>
                <br/>
                <?= Html::textInput('from', $filterOptions['from'], ['class' => 'form-control input-lg show-datepicker']); ?>
            </div>

            <div class="col-md-2">
                <?= Html::label('to', "", ['class' => 'control-label']) ?>
                <br/>
                <?= Html::textInput('to', $filterOptions['to'], ['class' => 'form-control input-lg show-datepicker']); ?>
            </div>

            <div class="col-md-1">
                <br/>
                <button class="btn btn-primary btn-lg" type="submit"
                        style="margin-top: 5px;"><?= Lang::t('Submit') ?></button>
            </div>
            <div class="col-md-1">
                <br/>
                <a class="btn btn-link" style="margin-top: 10px;"
                   href="<?= Url::to(['index']) ?>"><?= Lang::t('clear') ?></a>
            </div>
        </div>
    </div>
</div>
<?= Html::endForm() ?>

