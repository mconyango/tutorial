<?php
/* @var $this yii\web\View */
use backend\modules\conf\models\Region;
use common\extensions\grid\GridView;
use common\helpers\Utils;

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' =>Yii::$app->user->canCreate(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'code',
        ],
        [
            'attribute' => 'description',
            'filter' => false,
        ],
        [
            'attribute' => 'region_id',
            'value' => function ($data) {
                return Region::getFieldByPk($data->region_id, 'name');
            },
            'filter' => Region::getListData(),
        ],
        [
            'attribute' => 'is_active',
            'value' => function ($data) {
                return Utils::decodeBoolean($data->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{update}{delete}',
        ],
    ],
]);
?>