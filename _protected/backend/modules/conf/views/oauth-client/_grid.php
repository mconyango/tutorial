<?php
/* @var $this yii\web\View */
use common\extensions\grid\GridView;
?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' =>Yii::$app->user->canCreate(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'client_id',
            'filter' => false,
        ],
        [
            'attribute' => 'client_secret',
            'filter' => false,
        ],
        [
            'attribute' => 'grant_types',
            'filter' => false,
        ],
        [
            'class' => \common\extensions\grid\ActionColumn::className(),
            'template' => '{update}{delete}',
        ],
    ],
]);
?>