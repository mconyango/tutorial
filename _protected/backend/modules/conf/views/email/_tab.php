<?php
use common\helpers\Lang;
use yii\helpers\Url;

?>
<ul class="nav nav-tabs my-nav">
    <li><a href="<?= Url::to(['index']) ?>"><?= Lang::t('Email Templates') ?></a></li>
    <li><a href="<?= Url::to(['settings']) ?>"><?= Lang::t('Email Settings') ?></a></li>
</ul>