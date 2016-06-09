<?php

namespace backend\modules\conf\models;

use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\db\Expression;
use yii\mail\MessageInterface;
use yii\swiftmailer\Mailer;

/**
 * This is the model class for table "email_queue".
 *
 * @property integer $id
 * @property string $message
 * @property string $subject
 * @property string $sender_name
 * @property string $sender_email
 * @property string $recipient_email
 * @property string $attachment
 * @property string $cc
 * @property string $bcc
 * @property integer $type
 * @property integer $ref_id
 * @property string $created_at
 * @property integer $created_by
 * @property integer $attempts
 * @property string $pop_key
 */
class EmailQueue extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    const MAX_ATTEMPTS = 10;
    //define email types
    const TYPE_CREATED_USER = '1';
    const TYPE_NEW_USER = '2';
    const TYPE_RESET_PASSWORD = '3';
    const TYPE_PASSWORD_RECOVERY = '4';
    const TYPE_ACTIVATE_ACCOUNT = '5';
    const TYPE_NOTIFICATION = '6';
    const TYPE_TRANSACTION_INVOICE = '7';
    const TYPE_TRANSACTION_RECEIPT = '8';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email_outbox_queue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message', 'sender_email', 'recipient_email'], 'required'],
            [['message'], 'string'],
            [['type', 'ref_id'], 'integer'],
            [['subject'], 'string', 'max' => 255],
            [['sender_name'], 'string', 'max' => 60],
            [['sender_email', 'recipient_email'], 'email'],
            [['attachment', 'cc', 'bcc'], 'safe'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message' => Lang::t('Message'),
            'subject' => Lang::t('Subject'),
            'sender_name' => Lang::t('Sender Name'),
            'sender_email' => Lang::t('From'),
            'recipient_name' => Lang::t('Recipient Name'),
            'attachment' => Lang::t('Attachment'),
            'cc' => Lang::t('Cc'),
            'bcc' => Lang::t('Bcc'),
            'recipient_email' => Lang::t('To'),
            'created_at' => Lang::t('Queued At'),
            'created_by' => Lang::t('Created By'),
        ];
    }


    /**
     * Insert email to be sent to the queue
     * @param array $attributes
     * @return boolean TRUE if saved else FALSE
     */
    public static function pushToQueue($attributes)
    {
        if (empty($attributes))
            return false;

        $model = new EmailQueue();
        $model->created_at = new Expression('NOW()');
        $model->attempts = 0;
        foreach ($attributes as $k => $v) {
            $model->{$k} = $v;
        }

        return $model->save(false);
    }

    /**
     * Should be called by the daemon
     */
    public static function processQueue()
    {
        $pop_key = Utils::uuid();
        $outbox_data = [];
        $now = new Expression('NOW()');
        $success_ids = [];
        /* @var $setting \common\components\Setting */
        $setting = Yii::$app->setting;
        /* @var $mailer Mailer */
        $mailer = Yii::createObject([
            'class' => Mailer::className(),
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_HOST),
                'username' => $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_USERNAME),
                'password' => $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PASSWORD),
                'port' => $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PORT),
                'encryption' => $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_SECURITY),
            ],
        ]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $queue = self::fetchQueue($pop_key);
            foreach ($queue as $row) {
                $success = self::sendEmail($row, $mailer);
                if ($success) {
                    $success_ids[] = $row['id'];
                    //save to outbox
                    $outbox_data[] = [
                        'message' => $row['message'],
                        'subject' => $row['subject'],
                        'sender_name' => $row['sender_name'],
                        'sender_email' => $row['sender_email'],
                        'recipient_email' => $row['recipient_email'],
                        'attachment' => $row['attachment'],
                        'attachment_mime_type' => $row['attachment_mime_type'],
                        'cc' => $row['cc'],
                        'bcc' => $row['bcc'],
                        'type' => $row['type'],
                        'status' => $success ? EmailOutbox::STATUS_SUCCESS : EmailOutbox::STATUS_FAILED,
                        'ref_id' => $row['ref_id'],
                        'date_queued' => $row['created_at'],
                        'date_sent' => $now,
                        'created_by' => $row['created_by'],
                        'attempts' => $row['attempts'],
                    ];
                }

            }
            if (!empty($success_ids)) {
                static::deleteAll(['id' => $success_ids]);
            }
            //set pop_key for failed emails to null for retry
            static::updateAll(['pop_key' => null], ['pop_key' => $pop_key]);
            //save email to email outbox
            EmailOutbox::insertMultiple($outbox_data);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
        }
    }

    /**
     * Send Emails
     * @param array $queue
     * @param $mailer Mailer
     * @return bool
     */
    private static function sendEmail($queue, $mailer)
    {
        /* @var $setting \common\components\Setting */
        $setting = Yii::$app->setting;
        $template = $setting->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_THEME);
        if (empty($template) || !strpos($template, '{{content}}'))
            $template = '{{content}}';

        $body = strtr($template, [
            '{{content}}' => $queue['message'],
            '{{subject}}' => $queue['subject'],
        ]);
        //send email
        /* @var $email MessageInterface */
        $email = $mailer->compose(null, []);
        if (!empty($queue['attachment'])) {
            $email->attach($queue['attachment'], ['contentType' => $queue['attachment_mime_type']]);
        }
        if (!empty($queue['cc'])) {
            $email->setCc(explode(',', $queue['cc']));
        }
        if (!empty($queue['bcc'])) {
            $email->setBcc(explode(',', $queue['bcc']));
        }

        return $email
            ->setFrom([$queue['sender_email'] => $queue['sender_name']])
            ->setTo($queue['recipient_email'])
            ->setSubject($queue['subject'])
            ->setHtmlBody($body)
            ->send($mailer);
    }

    /**
     *
     * @param $pop_key
     * @return array
     * @throws \yii\db\Exception
     */
    private static function fetchQueue($pop_key)
    {
        $limit = 10;
        //update the queue
        $sql = 'UPDATE ' . static::tableName() . ' SET [[pop_key]]=:pop_key,[[attempts]]=[[attempts]]+1 WHERE [[attempts]]<:max_attempts AND [[pop_key]] IS NULL LIMIT ' . $limit;
        Yii::$app->db->createCommand($sql, [':max_attempts' => self::MAX_ATTEMPTS, ':pop_key' => $pop_key])
            ->execute();

        return static::getData(['*'], ['pop_key' => $pop_key]);
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
            ['recipient_email', 'recipient_email'],
            ['sender_email', 'sender_email'],
            ['subject', 'subject'],
            'type',
        ];
    }
}
