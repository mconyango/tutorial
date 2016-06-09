<?php
/* @var $this yii\web\View */
use backend\modules\conf\models\JobProcesses;
use common\extensions\grid\GridView;
use common\helpers\Lang;
use common\helpers\Url;
use common\helpers\Utils;
use yii\helpers\Html;

?>
<?php
echo GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => true, 'modal' => true],
    'striped' => false,
    'rowOptions' => function ($model) {
        return ["class" => !$model->is_active ? "bg-danger linkable" : "linkable", "data-href" => Url::to(['view', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'id',
            'filter' => false,
        ],
        [
            'attribute' => 'execution_type',
            'value' => function ($model) {
                return \backend\modules\conf\models\Jobs::decodeExecutionType($model->execution_type);
            },
            'filter' => false,
        ],
        [
            'attribute' => 'last_run',
            'value' => function ($model) {
                return \common\helpers\DateUtils::formatDate($model->last_run, "d/m/Y H:i:s");
            },
            'filter' => false,
        ],
        [
            'attribute' => 'threads',
            'filter' => false,
            'format' => 'html',
            'value' => function ($model) {
                return "<span class='label label-default'>" . $model->threads . "/" . $model->max_threads . "</span>&nbsp;<span class='label label-success'> R: " . JobProcesses::getTotalRunning($model->id) . "</span>&nbsp;<span class='label label-danger'> S: " . JobProcesses::getTotalSleeping($model->id) . "</span>";
            },
            'options' => ['style' => 'min-width:180px;'],
        ],
        [
            'attribute' => 'sleep',
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
            'template' => '{start}{stop}{view}{update}{delete}',
            'width' => '150px;',
            'buttons' => [
                'start' => function ($url, $model) {
                    if (Yii::$app->user->canUpdate() && !$model->is_active) {
                        return Html::a('<i class="fa fa-check text-success"></i>', 'javascript:void(0);', ['title' => Lang::t('Start the process'), 'data-pjax' => 0, 'data-href' => $url, 'data-grid' => $model->shortClassName() . '-grid-pjax', 'class' => 'grid-update']);
                    } else {
                        return "";
                    }
                },
                'stop' => function ($url, $model) {
                    if (Yii::$app->user->canUpdate() && $model->is_active) {

                        return Html::a('<i class="fa fa-ban text-danger"></i>', 'javascript:void(0);', ['title' => Lang::t('Stop all processes'), 'data-pjax' => 0, 'data-href' => $url, 'data-grid' => $model->shortClassName() . '-grid-pjax', 'class' => 'grid-update']);
                    } else {
                        return "";
                    }
                },
            ]
        ],
    ],
]);
?>