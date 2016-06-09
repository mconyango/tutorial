<?php

use common\helpers\Lang;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\conf\models\BankBranches */
/* @var $bank backend\modules\conf\models\BankNames */

$this->title = Lang::t('{bank} branches', ['bank' => Html::encode($bank->bank_name)]);
$this->params['breadcrumbs'] = [
    ['label' => 'Banks', 'url' => ['bank/index']],
    Lang::t('Branches'),
];
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>