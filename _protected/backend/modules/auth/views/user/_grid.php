<?php
use backend\modules\auth\Acl;
use backend\modules\auth\models\UserLevels;
use common\extensions\grid\GridView;
use common\helpers\Lang;
use yii\helpers\Html;
use backend\modules\auth\models\Users;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model Users */
?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => !empty($model->level_id) && Yii::$app->user->canCreate(), 'modal' => false],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['update', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'name',
            'filter' => true,
        ],
        [
            'attribute' => 'username',
            'filter' => true,
            'enableSorting' => true,
        ],
        [
            'attribute' => 'email',
            'filter' => true,
        ],
        [
            'attribute' => 'phone',
            'filter' => true,
        ],
        [
            'attribute' => 'level_id',
            'value' => function ($model) {
                return UserLevels::getFieldByPk($model->level_id, 'name');
            },
            'filter' => Users::userLevelOptions(),
            'visible' => empty($model->level_id),
        ],
        [
            'attribute' => 'status',
            'filter' => Users::statusOptions(),
            'value' => function ($model) {
                return Users::decodeStatus($model->status);
            },
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{view}{update}{delete}',
            'buttons' => [
                'update' => function ($url, $model) {
                    return Users::checkPrivilege(Acl::ACTION_DELETE, $model->level_id) || Users::isMyAccount($model->id) ? Html::a('<i class="fa fa-pencil text-success"></i>', $url, [
                        'data-pjax' => 0,
                        'title' => Lang::t('Update'),
                    ]) : '';
                },
                'delete' => function ($url, $model) {
                    return Users::checkPrivilege(Acl::ACTION_DELETE, $model->level_id) ? Html::a('<i class="fa fa-trash text-danger"></i>', 'javascript:void(0);', [
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