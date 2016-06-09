<?php
use backend\models\Category;
use yii\bootstrap\Html;

?>
    <form class="" action="">

        <div class="panel panel-default">

            <div class="panel-heading">
                <h3 class="panel-title">Showing Dependent Dropdown Lists</h3>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <div class="col-md-4">
                        <label class="control-label">Category</label><br/>
                        <?= Html::dropDownList('category_id', null, Category::getListData(), ['class' => 'form-control input-lg', 'id' => 'category_id']) ?>
                    </div>

                    <div class="col-md-4">
                        <label class="control-label">Sub-Category</label><br/>
                        <?= Html::dropDownList('subcategory_id', null, \backend\models\Subcategory::getListData(), ['class' => 'form-control input-lg', 'id' => 'subcategory_id']) ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php
$options = [
    'categorySelector' => '#category_id',
    'subcategorySelector' => '#subcategory_id',
    'url' => \yii\helpers\Url::to(['filter-subcategory'])
];
$this->registerJs("MyApp.tutorial.dependentLists(" . \yii\helpers\Json::encode($options) . ");");
?>


<div id="sample-element"></div>
