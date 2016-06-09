<?php
/* @var $this yii\web\View */
use backend\modules\auth\Acl;
use backend\modules\auth\models\UserLevels;
use common\extensions\grid\GridView;

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate($this->context->resource), 'modal' => true],
    'toolbarButtons' => [],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'parent_id',
            'value' => function ($model) {
                return UserLevels::getFieldByPk($model->parent_id, 'name');
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