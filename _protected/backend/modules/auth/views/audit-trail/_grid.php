<?php

use backend\modules\auth\models\AuditTrail;
use backend\modules\auth\models\Users;
use common\extensions\grid\GridView;
use common\helpers\DateUtils;
use yii\helpers\Html;

/* @var $model backend\modules\auth\models\AuditTrail */
$grid_id = 'audit_trail_grid';

echo GridView::widget([
    'id' => $grid_id,
    'searchModel' => $model,
    'createButton' => ['visible' => false],
    'toolbarButtons' => [],
    'columns' => [
        [
            'attribute' => 'user_id',
            'value' => function ($v) {
                return Users::getFieldByPk($v->user_id, 'name');
            }
        ],
        [
            'attribute' => 'action',
            'value' => function ($v) {
                return AuditTrail::decodeAction($v->action);
            }
        ],
        [
            'attribute' => 'action_description',
            'value' => function ($v) {
                return \Illuminate\Support\Str::limit($v->action_description);
            }
        ],
        [
            'attribute' => 'ip_address',
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($v) {
                return DateUtils::formatDate($v->created_at);
            }
        ],
        [
            'class' => \common\extensions\grid\ActionColumn::class,
            'width' => '120px',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('More details <i class="fa fa-chevron-circle-right"></i>', $url, [
                        'data-pjax' => 0,
                        'style' => 'min-width:100px;',
                        'class' => 'show_modal_form',
                        'data-grid' => $model->shortClassName() . '-grid-pjax'
                    ]);
                },
            ]
        ],
    ],
]);