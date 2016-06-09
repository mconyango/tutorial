<?php
/* @var $this yii\web\View */
use common\extensions\grid\GridView;
use common\helpers\Utils;

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'toolbarButtons' => [],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'viewable',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->viewable);
            }
        ],
        [
            'attribute' => 'creatable',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->creatable);
            }
        ],
        [
            'attribute' => 'editable',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->editable);
            }
        ],
        [
            'attribute' => 'deletable',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->deletable);
            }
        ],
        [
            'attribute' => 'executable',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->executable);
            }
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [],
        ],
    ],
]);
?>