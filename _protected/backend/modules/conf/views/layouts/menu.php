<?php
use backend\modules\conf\Constants;
use common\helpers\Lang;
use yii\helpers\Url;

?>
<?php if (Yii::$app->user->canView(Constants::RES_SETTINGS)): ?>
    <li class="<?= Yii::$app->controller->isMenuActive(Constants::MENU_SETTINGS) ? 'active' : '' ?>">
        <a href="<?= Url::to(['/conf/settings/index']) ?>">
            <i class="fa fa-lg fa-fw fa-cogs"></i>
            <span class="menu-item-parent"><?= Lang::t('SETTINGS') ?></span>
        </a>
    </li>
<?php endif; ?>