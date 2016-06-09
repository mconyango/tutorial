<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_notif_types".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $template
 * @property string $email_template
 * @property string $sms_template
 * @property integer $send_email
 * @property integer $send_sms
 * @property integer $notify_all_users
 * @property integer $notify_days_before
 * @property string $model_class_name
 * @property string $fa_icon_class
 * @property string $notification_trigger
 * @property integer $is_active
 * @property string $users
 * @property string $roles
 * @property string $created_at
 * @property integer $created_by
 */
class NotifTypes extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    //notification_trigger
    const TRIGGER_MANUAL = '1';
    const TRIGGER_SYSTEM = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf_notif_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'template', 'model_class_name'], 'required'],
            [['email_template', 'notification_trigger'], 'string'],
            [['send_email', 'send_sms', 'notify_all_users', 'notify_days_before', 'is_active'], 'integer'],
            [['id', 'model_class_name'], 'string', 'max' => 60],
            [['name'], 'string', 'max' => 128],
            [['description', 'sms_template'], 'string', 'max' => 255],
            [['template'], 'string', 'max' => 500],
            [['users', 'roles'], 'safe'],
            [['fa_icon_class'], 'string', 'max' => 30],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'name' => Lang::t('Name'),
            'description' => Lang::t('Description'),
            'template' => Lang::t('Template'),
            'email_template' => Lang::t('Email Template'),
            'sms_template' => Lang::t('SMS Template'),
            'send_email' => Lang::t('Send Email'),
            'send_sms' => Lang::t('Send SMS'),
            'notify_all_users' => Lang::t('Notify Everyone'),
            'notify_days_before' => Lang::t('Notify earlier'),
            'users' => Lang::t('Users'),
            'roles' => Lang::t('Roles'),
            'model_class_name' => Lang::t('Model Class Name'),
            'fa_icon_class' => Lang::t('Font Awesome Icon Class'),
            'notification_trigger' => Lang::t('Notification trigger'),
            'is_active' => Lang::t('Active'),
        ];
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (parent::beforeSave($insert)) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @return boolean whether the insertion or updating should continue.
     * If false, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        if (!empty($this->users)) {
            $this->users = serialize($this->users);
        }
        if (!empty($this->roles)) {
            $this->roles = serialize($this->roles);
        }
        return parent::beforeSave($insert);
    }

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        if (!empty($this->users)) {
            $this->users = unserialize($this->users);
        }
        if (!empty($this->roles)) {
            $this->roles = unserialize($this->roles);
        }
        parent::afterFind();
    }

    /**
     * Get icon for the notification type
     * @param string $id
     * @return string
     */
    public static function getIcon($id)
    {
        $icon = static::getFieldByPk($id, 'fa_icon_class');
        if (empty($icon))
            $icon = 'fa-bell';
        return $icon;
    }

    /**
     *
     * @return array
     */
    public static function notificationTriggerOptions()
    {
        return [
            self::TRIGGER_MANUAL => Lang::t('Manual'),
            self::TRIGGER_SYSTEM => Lang::t('System'),
        ];
    }

    /**
     * Search params for the active search
     * ```php
     *   return [
     *       ["name","_searchField","AND|OR"],//default is AND only include this param if there is a need for OR condition
     *       'id',
     *       'email'
     *   ];
     * ```
     * @return array
     */
    public function searchParams()
    {
        return [
            ['name', 'name'],
        ];
    }
}
