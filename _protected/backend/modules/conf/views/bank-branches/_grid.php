<?php

use common\extensions\grid\GridView;
use common\helpers\Utils;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\BankBranches */

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'branch_code',
        ],
        [
            'attribute' => 'branch_name',
        ],
        [
            'attribute' => 'is_active',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'class' => common\extensions\grid\ActionColumn::className(),
            'template' => '{update}{delete}',
        ],
    ],
]);
?>