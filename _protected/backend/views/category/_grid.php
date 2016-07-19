<?php
/* @var $this yii\web\View */
/* @var $model backend\models\Category */
use common\extensions\grid\GridView;
use common\helpers\Url;
use common\helpers\Utils;

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' =>true, 'modal' => true],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
        ],
        [
            'class' => common\extensions\grid\ActionColumn::className(),
            'template' => '{update}{delete}',
        ],
    ],
]);
?>