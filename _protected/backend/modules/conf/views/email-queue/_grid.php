<?php
/* @var $this yii\web\View */
use common\extensions\grid\GridView;

?>
<?php
$grid_id = 'email-queue-grid';
echo GridView::widget([
    'id' => $grid_id,
    'searchModel' => $model,
    'filterModel' => $model,
    'showExportButton' => false,
    'createButton' => ['visible' => false],
    'columns' => [
        [
            'attribute' => 'sender_email',
        ],
        [
            'attribute' => 'recipient_email',

        ],
        [
            'attribute' => 'subject',
        ],
        [
            'attribute' => 'message',
            'value' => function ($v) {
                return \Illuminate\Support\Str::limit($v->message, 50);
            },
            'format' => 'html',
            'filter' => false,
        ],
        [
            'attribute' => 'attachment',
            'value' => function ($v) {
                return !empty($v->attachment)?'YES':'NO';
            },
            'filter' => false,
        ],
        [
            'attribute' => 'attempts',
            'filter' => false,
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($v) {
                return \common\helpers\DateUtils::formatDate($v->created_at);
            },
            'filter' => false,
        ],
        [
            'class' => 'common\extensions\grid\ActionColumn',
            'template' => '{delete}',
        ],
    ],
]);
?>