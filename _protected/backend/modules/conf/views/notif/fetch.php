<?php
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php
if (!empty($data)): ?>
    <?php foreach ($data as $row):
        $notif = Notif::processTemplate($row['notif_type_id'], $row['item_id']);
        if (!$notif) {
            continue;
        }
        ?>
        <a class="lv-item <?= !$row['is_read'] ? ' unread' : '' ?>" href="<?= $notif['url'] ?>"
           id="notif-<?= $row['id'] ?>"
           data-mark-as-read-url="<?= Url::to(['/conf/notif/mark-as-read', 'id' => $row['id']]) ?>"
           style="color: #333;">
            <div class="media">
                <div class="pull-left">
                    <i class="fa <?= NotifTypes::getIcon($row['notif_type_id']) ?> fa-fw fa-2x"></i>
                </div>
                <div class="media-body">
                    <div class="lv-title"><?= Html::decode($notif['title']) ?></div>
                    <small class="lv-small">
                        <?= Html::decode($notif['text']) ?>
                        <br>
                        <time class="pull-right font-xs text-muted">
                            <i><?= DateUtils::formatDate($row['created_at']) ?></i></time>
                    </small>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-transparent text-center">
        <h4><?= Lang::t('You have no notifications at the moment') ?></h4>
        <i class="fa fa-bell fa-2x fa-border"></i>
    </div>
<?php endif; ?>
