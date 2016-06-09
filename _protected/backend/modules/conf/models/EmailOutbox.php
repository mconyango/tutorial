<?php

namespace backend\modules\conf\models;

use Yii;

/**
 * This is the model class for table "email_outbox".
 *
 * @property integer $id
 * @property string $message
 * @property string $subject
 * @property string $sender_name
 * @property string $sender_email
 * @property string $recipient_email
 * @property string $attachment
 * @property integer $type
 * @property integer $status
 * @property integer $ref_id
 * @property string $date_queued
 * @property string $date_sent
 * @property integer $created_by
 * @property integer $attempts
 */
class EmailOutbox extends EmailQueue
{
    const STATUS_SUCCESS = '1';
    const STATUS_FAILED = '0';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email_outbox}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return parent::attributes();
    }
}
