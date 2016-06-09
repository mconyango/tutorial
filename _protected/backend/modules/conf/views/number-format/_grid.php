<?php
/* @var $this yii\web\View */
use backend\modules\auth\models\Users;
use common\extensions\grid\GridView;

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Users::isDev(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'next_number',
        ],
        [
            'attribute' => 'min_digits',
        ],
        [
            'attribute' => 'prefix',
        ],
        [
            'attribute' => 'suffix',
        ],
        [
            'attribute' => 'preview',
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{update}{delete}',
        ],
    ],
]);
?>