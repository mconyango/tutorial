<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\BankNames */
use common\extensions\grid\GridView;
use common\helpers\Url;
use common\helpers\Utils;

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['bank-branches/index', "bank_id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'bank_code',
        ],
        [
            'attribute' => 'bank_name',
        ],
        [
            'attribute' => 'mpesa_paybill',
        ],
        [
            'attribute' => 'is_active',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'header' => 'Branches',
            'value' => function ($model) {
                return \backend\modules\conf\models\BankBranches::getCount(['bank_id' => $model->id]);
            }
        ],
        [
            'class' => common\extensions\grid\ActionColumn::className(),
            'template' => '{update}{delete}',
        ],
    ],
]);
?>