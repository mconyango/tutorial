<?php

use backend\modules\auth\models\Users;
use common\extensions\grid\GridView;
use common\helpers\Lang;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

?>
<?php
$grid_id = 'roles-grid';
echo GridView::widget([
    'id' => $grid_id,
    'searchModel' => $model,
    'createButton' => ['visible' => \backend\modules\auth\models\Users::isDev(), 'modal' => true],
    'toolbarButtons' => [],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['view', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'name',
            'filter' => false,
        ],
        [
            'attribute' => 'description',
            'filter' => false,
        ],
        [
            'attribute' => 'level_id',
            'value' => function ($data) {
                return \backend\modules\auth\models\UserLevels::getFieldByPk($data->level_id, 'name');
            },
            'filter' => false,
            'visible' => Users::isDev(),
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{view}{update}{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Users::isDev() ? Html::a('<i class="fa fa-trash text-danger"></i>', 'javascript:void(0);', [
                        'data-pjax' => '0',
                        'title' => Lang::t('Delete'),
                        'data-confirm-message' => Lang::t('DELETE_CONFIRM'),
                        'data-href' => $url,
                        'class' => 'grid-update',
                        'data-grid' => $model->shortClassName() . '-grid-pjax'
                    ]) : '';
                },
            ]
        ],
    ],
]);
?>