<?php
/* @var $this yii\web\View */

use backend\modules\auth\Acl;
use backend\modules\auth\models\Permission;
use backend\modules\auth\models\Resources;
use backend\modules\auth\models\Roles;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model backend\modules\auth\models\Roles */


$this->title = Html::encode($model->name);
$this->params['breadcrumbs'] = [
    ['label' => 'Roles', 'url' => ['index']],
    $this->title
];

$model_class_name = Permission::shortClassName();
?>
    <div class="row">
        <div class="col-md-12">
            <div class="wells well-lights">
                <?= Html::beginForm(Url::current(), 'POST', ['class' => '', 'id' => 'my-roles-view-form']) ?>
                <div class="row">
                    <div class="col-sm-8">
                        <h1><?= Html::encode($this->title) ?>
                            <small><?= Html::encode($model->description) ?></small>
                        </h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="btn-toolbar pull-right">
                            <div class="btn-group">
                                <button class="btn btn-primary" type="submit"><i
                                        class="fa fa-check"></i> <?= Lang::t('Save Changes') ?></button>
                            </div>
                            <div class="btn-group">
                                <a class="btn btn-danger" href="<?= Url::to(['index']) ?>"><i
                                        class="fa fa-times"></i> <?= Lang::t('Close') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-group"></i> <?= Lang::t('Add Users') ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= Html::dropDownList('users', Roles::getUsers($model->id), Users::getListData('id', 'name', FALSE, ['status' => Users::STATUS_ACTIVE]), ['multiple' => 'multiple', 'class' => 'select2']); ?>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-edit"></i> <?= Lang::t('Review Privileges') ?>
                            <span class="pull-right"><button class="btn btn-link my-select-all" type="button"
                                                             style="padding-top: 1px;"><?= Lang::t('Check All') ?></button></span>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                            <tr style="background-color:inherit;background-image: none">
                                <th><?= Lang::t('System Resources') ?></th>
                                <th><?= Lang::t('Can View') ?></th>
                                <th><?= Lang::t('Can Create') ?></th>
                                <th><?= Lang::t('Can Update') ?></th>
                                <th><?= Lang::t('Can Delete') ?></th>
                                <th><?= Lang::t('Can Execute') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($resources as $r): ?>
                                <tr>
                                    <td>
                                        <?= $r['name'] ?>
                                    </td>
                                    <td>
                                        <?php if (Resources::getFieldByPk($r['id'], 'viewable') == 1): ?>
                                            <?= Html::hiddenInput($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_VIEW . ']', 0) ?>
                                            <?= Html::checkBox($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_VIEW . ']', Permission::getValue($r['id'], $model->id, Acl::ACTION_VIEW), ['class' => 'my-roles-checkbox']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <?php if (Resources::getFieldByPk($r['id'], 'creatable') == 1): ?>
                                            <?= Html::hiddenInput($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_CREATE . ']', 0) ?>
                                            <?= Html::checkBox($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_CREATE . ']', Permission::getValue($r['id'], $model->id, Acl::ACTION_CREATE), ['class' => 'my-roles-checkbox']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <?php if (Resources::getFieldByPk($r['id'], 'editable') == 1): ?>
                                            <?= Html::hiddenInput($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_UPDATE . ']', 0) ?>
                                            <?= Html::checkBox($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_UPDATE . ']', Permission::getValue($r['id'], $model->id, Acl::ACTION_UPDATE), ['class' => 'my-roles-checkbox']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <?php if (Resources::getFieldByPk($r['id'], 'deletable') == 1): ?>
                                            <?= Html::hiddenInput($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_DELETE . ']', 0) ?>
                                            <?= Html::checkBox($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_DELETE . ']', Permission::getValue($r['id'], $model->id, Acl::ACTION_DELETE), ['class' => 'my-roles-checkbox']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <?php if (Resources::getFieldByPk($r['id'], 'executable') == 1): ?>
                                            <?= Html::hiddenInput($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_EXECUTE . ']', 0) ?>
                                            <?= Html::checkBox($model_class_name . '[' . $r['id'] . '][' . Acl::ACTION_EXECUTE . ']', Permission::getValue($r['id'], $model->id, Acl::ACTION_EXECUTE), ['class' => 'my-roles-checkbox']) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
<?php
$this->registerJs("MyApp.modules.auth.roles();");
?>