<?php
/* @var $this yii\web\View */
/* @var $model \backend\modules\conf\models\NotifTypes */
use backend\modules\auth\models\Users;
use common\extensions\grid\GridView;
use common\helpers\Utils;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php
$grid_id = 'notif-grid';
echo GridView::widget([
    'id' => $grid_id,
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Users::isDev(), 'modal' => false],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['update', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'id',
            'visible' => Users::isDev(),
            'filter' => false,
        ],
        [
            'attribute' => 'name',
            'filter' => false,
        ],
        [
            'attribute' => 'description',
            'filter' => false,
        ],
        [
            'attribute' => 'is_active',
            'filter' => Utils::booleanOptions(),
            'value' => function ($model) {
                return Utils::decodeBoolean($model->is_active);
            },
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