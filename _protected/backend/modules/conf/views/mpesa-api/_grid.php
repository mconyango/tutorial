<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\MpesaApiCredentials */
use backend\modules\customer\models\Customer;
use common\extensions\grid\GridView;
use common\helpers\Utils;

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'columns' => [
        [
            'attribute' => 'description',
        ],
        [
            'attribute' => 'sp_id',
            'filter'=>false,
        ],
        [
            'attribute' => 'service_id',
            'filter'=>false,
        ],
        [
            'attribute'=>'initiator_identifier',
            'filter'=>false,
        ],
        [
            'attribute'=>'organization_shortcode',
            'filter'=>false,
        ],
        [
            'attribute'=>'customer_id',
            'value'=>function($v){
                return Customer::getFieldByPk($v->customer_id,'name');
            },
            'filter'=> Customer::getListData(),
        ],
        [
            'attribute' => 'is_active',
            'value' => function ($model) {
                return Utils::decodeBoolean($model->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'class' => common\extensions\grid\ActionColumn::className(),
            'template' => '{update}{delete}',
        ],
    ],
]);
?>