<?php

namespace backend\modules\conf\models;

use Yii;

/**
 * This is the model class for table "conf_notif_settings".
 *
 * @property integer $id
 * @property string $notif_type_id
 * @property string $is_active
 * @property integer $send_email
 * @property integer $send_sms
 * @property integer $user_id
 * @property string $created_at
 */
class NotifSettings extends \common\models\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf_notif_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notif_type_id', 'is_active', 'user_id'], 'required'],
            [['is_active'], 'string'],
            [['send_email', 'send_sms', 'user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['notif_type_id'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'notif_type_id' => 'Notif Type ID',
            'is_active' => 'Is Active',
            'send_email' => 'Send Email',
            'send_sms' => 'Send Sms',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }
}
