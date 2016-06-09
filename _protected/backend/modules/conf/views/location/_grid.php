<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\Location */
use common\extensions\grid\GridView;
use common\helpers\Utils;

?>
<?= GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'name',
        ],
        [
            'attribute'=>'code',
        ],
        [
            'attribute' => 'description',
        ],
        [
            'attribute' => 'country',
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