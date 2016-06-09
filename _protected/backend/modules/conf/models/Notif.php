<?php

namespace backend\modules\conf\models;

use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use backend\modules\conf\Constants;
use common\helpers\DateUtils;
use common\helpers\Lang;
use common\models\ActiveRecord;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "conf_notif".
 *
 * @property integer $id
 * @property string $notif_type_id
 * @property integer $user_id
 * @property integer $item_id
 * @property integer $is_read
 * @property integer $is_seen
 * @property string $created_at
 */
class Notif extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_notif}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notif_type_id', 'user_id', 'item_id'], 'required'],
            [['user_id', 'item_id', 'is_read', 'is_seen'], 'integer'],
            [['notif_type_id'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'notif_type_id' => Lang::t('Notif Type'),
            'user_id' => Lang::t('User'),
            'item_id' => Lang::t('Item'),
            'is_read' => Lang::t('Read'),
            'created_at' => Lang::t('Date'),
        ];
    }

    /**
     * Pushes a new notification
     * @param string $notif_type_id
     * @param int $item_id
     * @param array $user_ids
     * @param integer $created_by
     * @return bool
     */
    public static function pushNotif($notif_type_id, $item_id, $user_ids = [], $created_by = null)
    {
        if (!NotifTypes::exists(['id' => $notif_type_id, 'is_active' => 1])) {
            return false;
        }

        if (empty($user_ids)) {
            //get the users that should receive notif
            $user_ids = static::getNotifUsers($notif_type_id);
        }

        if (!empty($user_ids)) {
            $notif_data = [];
            $created_at = new Expression('NOW()');
            foreach ($user_ids as $user_id) {
                if ($user_id == $created_by)
                    continue;

                $notif_data[] = [
                    'notif_type_id' => $notif_type_id,
                    'user_id' => $user_id,
                    'item_id' => $item_id,
                    'created_at' => $created_at,
                ];
            }

            static::insertMultiple($notif_data);
        }
        //process email
        static::processEmail($notif_type_id, $user_ids, $item_id);
        //process sms
        static::proccessSms($notif_type_id, $user_ids, $item_id);
    }

    /**
     *
     * @param string $notif_type_id
     * @param array $user_ids
     * @param string $item_id
     * @return bool
     */
    private static function processEmail($notif_type_id, $user_ids, $item_id)
    {
        if (empty($user_ids))
            return FALSE;

        $notif_type = NotifTypes::getOneRow(['send_email', 'email_template', 'model_class_name'], ['id' => $notif_type_id]);
        if (!$notif_type['send_email'])
            return FALSE;

        $model_class_name = $notif_type['model_class_name'];
        /* @var $model \common\models\NotifInterface */
        $model = new $model_class_name();
        $message = $model->processTemplate($notif_type['email_template'], $item_id, $notif_type_id);

        if (!empty($message)) {
            static::sendEmail($notif_type_id, $user_ids, $message['text'], $item_id);
        }
    }

    /**
     *
     * @param string $notif_type_id
     * @param array $user_ids
     * @param string $item_id
     * @return bool
     */
    private static function proccessSms($notif_type_id, $user_ids, $item_id)
    {
        if (empty($user_ids))
            return FALSE;

        $notif_type = NotifTypes::getOneRow(['send_sms', 'sms_template', 'model_class_name'], ['id' => $notif_type_id]);
        if (!$notif_type['send_sms'])
            return FALSE;

        $model_class_name = $notif_type['model_class_name'];
        /* @var $model \common\models\NotifInterface */
        $model = new $model_class_name();
        $message = $model->processTemplate($notif_type['sms_template'], $item_id, $notif_type_id);
        if (!empty($message)) {
            static::sendSms($user_ids, $message['text'], $item_id);
        }
    }

    /**
     * Send notification as an sms
     * @param array $user_ids
     * @param string $message
     * @param string $item_id
     */
    private static function sendSms($user_ids, $message, $item_id)
    {
        //@vendor specific and only implemented on demand
    }

    /**
     * Update notification
     * System triggered notifications
     */
    public static function updateNotification()
    {
        $rowset = NotifTypes::getColumnData('model_class_name', ['notification_trigger' => NotifTypes::TRIGGER_SYSTEM, 'is_active' => 1]);
        foreach ($rowset as $model_class) {
            /* @var $model \common\models\NotifInterface */
            try {
                $model = new $model_class();
                $model->updateNotification();
            } catch (\Exception $e) {
                Yii::error($e->getTraceAsString());
            }
        }
    }

    /**
     * Get notification date
     * @param string $notif_type_id
     * @return string $date
     */
    public static function getNotificationDate($notif_type_id)
    {
        $notify_days_before = NotifTypes::getFieldByPk($notif_type_id, 'notify_days_before');
        if (empty($notify_days_before)) {
            return date('Y-m-d');
        }

        return DateUtils::addDate(date('Y-m-d'), (int)$notify_days_before, 'day');
    }

    /**
     * Get users who are supposed to receive a notification
     * @param string $notif_type_id
     * @return array $users
     */
    public static function getNotifUsers($notif_type_id)
    {
        $notify_all_users = NotifTypes::getFieldByPk($notif_type_id, 'notify_all_users');

        if ($notify_all_users)
            return Users::getColumnData('id', 'status=:t1 AND level_id<=:t2', [':t1' => Users::STATUS_ACTIVE, ':t2' => UserLevels::LEVEL_SUPER_ADMIN]);

        $users = NotifTypes::getScalar('users', ['id' => $notif_type_id]);
        if (!empty($users)) {
            $users = unserialize($users);
        } else {
            $users = [];
        }
        $roles = NotifTypes::getScalar('roles', ['id' => $notif_type_id]);
        if (!empty($roles)) {
            $users = array_merge($users, Users::getColumnData('id', ['role_id' => unserialize($roles)]));
        }
        return array_unique($users);
    }

    /**
     * Send notification as an email
     * @param string $notif_type_id
     * @param array $user_ids
     * @param string $message
     * @param string $item_id
     */
    private static function sendEmail($notif_type_id, $user_ids, $message, $item_id)
    {
        /* @var $settings \common\components\Setting */
        $settings = Yii::$app->setting;
        $from_name = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME, Yii::$app->name);
        $from_email = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_USERNAME);
        $subject = NotifTypes::getFieldByPk($notif_type_id, 'name');

        $email = [];
        foreach ($user_ids as $user_id) {
            //replace  '{{user}}' placeholder with the the user name
            $message = strtr($message, [
                '{{user}}' => NotifTypes::getFieldByPk($user_id, 'name')
            ]);

            $email[] = [
                'message' => $message,
                'subject' => $subject,
                'sender_name' => $from_name,
                'sender_email' => $from_email,
                'recipient_email' => Users::getFieldByPk($user_id, 'email'),
                'type' => EmailQueue::TYPE_NOTIFICATION,
                'ref_id' => $item_id,
            ];
        }

        EmailQueue::pushToQueue($email);
    }

    /**
     * Fetch notification
     * @param string $user_id
     * @return array
     */
    public static function fetchNotif($user_id = NULL)
    {
        if (empty($user_id))
            $user_id = Yii::$app->user->id;
        return static::getData('*', ['user_id' => $user_id], [], ['orderBy' => ['id' => SORT_DESC], 'limit' => 100]);
    }

    /**
     *
     * @param string $user_id
     * @return int
     */
    public static function getTotalUnSeenNotif($user_id = NULL)
    {
        if (empty($user_id))
            $user_id = Yii::$app->user->id;
        return static::getCount(['user_id' => $user_id, 'is_seen' => 0]);
    }

    /**
     *
     * @param string $notif_type_id
     * @param string $item_id
     * @return string $processed_template
     */
    public static function processTemplate($notif_type_id, $item_id)
    {
        $notif_type = NotifTypes::getOneRow('template,model_class_name', ['id' => $notif_type_id]);
        if (empty($notif_type))
            return FALSE;
        $model_class_name = $notif_type['model_class_name'];
        /* @var $model \common\models\NotifInterface */
        $model = new $model_class_name();
        return $model->processTemplate($notif_type['template'], $item_id, $notif_type_id);
    }

    /**
     *
     * @return bool
     */
    public static function markAsSeen()
    {
        return Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_seen' => 1], ['user_id' => Yii::$app->user->id])
            ->execute();
    }

    /**
     *
     * @param string $id
     * @return bool
     */
    public static function markAsRead($id = NULL)
    {
        $condition = ['user_id' => Yii::$app->user->id];
        if (!empty($id)) {
            $condition['id'] = $id;
        }

        return Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_read' => 1], $condition)
            ->execute();
    }

}
