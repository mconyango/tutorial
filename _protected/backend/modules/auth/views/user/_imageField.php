<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model \backend\modules\auth\models\Users */
/* @var $this \yii\web\View */

$photo_src = \backend\modules\auth\models\Users::getProfileImageUrl($model->id, 128, $model->profile_image);
//$photo_src = 'http://placehold.it/150x150';
$class_name = strtolower($model->shortClassName());
$temp_selector = '#' . $class_name . '-temp_profile_image';
$preview_img_id = $class_name . '-profile-image-preview';
$notif_id = 'upload-notif';

?>
<div class="form-group">
    <?=\yii\bootstrap\Html::activeLabel($model,'profile_image',['class'=>'control-label col-md-3'])?>
    <div class="col-md-8">
        <?= Html::activeHiddenInput($model, 'temp_profile_image') ?>
        <div class="row">
            <div class="col-md-6">
                <img id="<?= $preview_img_id ?>" class="thumbnail default-profile-photo" src="<?= $photo_src ?>"
                     data-src="<?= $photo_src ?>">
            </div>
            <div class="col-md-6">
                <div class="uploader">
                    <?= harrytang\fineuploader\Fineuploader::widget([
                        'buttonIcon' => 'fa fa-open',
                        'buttonLabel' => 'Browse Image',
                        'options' => [
                            'request' => [
                                'endpoint' => Url::to(['upload-image']),
                                'params' => [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken]
                            ],
                            'validation' => [
                                'allowedExtensions' => ['jpeg', 'jpg', 'png'],
                            ],
                            'classes' => [
                                'success' => 'alert alert-success hidden',
                                'fail' => 'alert alert-error'
                            ],
                            'multiple' => false,
                        ],
                        'events' => [
                            'complete' => '
                            if(responseJSON.success){
                               $("#' . $preview_img_id . '").attr("src",responseJSON.url);
                               $("' . $temp_selector . '").val(responseJSON.path);
                            }else{
                               MyApp.utils.showAlertMessage(responseJSON.error,"error","#' . $notif_id . '");
                            }',
                        ],
                    ]) ?>
                </div>
                <div id="<?= $notif_id ?>"></div>
            </div>
        </div>
    </div>
</div>