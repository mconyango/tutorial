<div class="page-footer">
    <div class="row" style="margin-left: -13px;margin-right: -13px">
        <div class="col-xs-12 col-sm-6">
            <span>
                <?= \Yii::$app->setting->get(\backend\modules\conf\Constants::SECTION_SYSTEM, \backend\modules\conf\Constants::KEY_APP_NAME, Yii::$app->name) ?>
                | &copy;<?= date('Y'); ?>
            </span>

        </div>
        <div class="col-xs-6 col-sm-6 text-right hidden-xs">
            <!-- end div-->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
</div>