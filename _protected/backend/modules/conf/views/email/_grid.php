<?php
/* @var $this yii\web\View */
use backend\modules\auth\models\Users;
use common\extensions\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php
$grid_id = 'email-templates-grid';
echo GridView::widget([
    'id' => $grid_id,
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => false],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['update', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'id',
            'visible' => Users::isDev(),
        ],
        [
            'attribute' => 'name',
            'filter' => true,
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [
                'update' => function ($url) {
                    return Yii::$app->user->canUpdate() ? Html::a('<i class="fa fa-pencil text-success"></i>', $url, ['data-pjax' => 0]) : '';
                },
            ]
        ],
    ],
]);
?>