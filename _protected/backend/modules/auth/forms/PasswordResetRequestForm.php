<?php
namespace backend\modules\auth\forms;

use backend\modules\auth\models\Users;
use backend\modules\conf\Constants;
use backend\modules\conf\models\EmailQueue;
use backend\modules\conf\models\EmailTemplate;
use common\models\Model;
use Yii;

/**
 * Password reset request form.
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => Users::className(),
                'filter' => ['status' => Users::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool Whether the email was send.
     */
    public function sendEmail()
    {
        /* @var $user Users */
        $user = Users::findOne([
            'status' => Users::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if ($user) {
            $user->generatePasswordResetToken();

            if ($user->save(false)) {
                //add email queue here.
                return $this->queueEmail($user);
            }
        }

        return false;
    }

    /**
     * @param Users $user
     * @return bool
     */
    protected function queueEmail($user)
    {
        $template = EmailTemplate::getOneRow('*', ['id' => 'forgot_password_email']);

        if (empty($template))
            return FALSE;

        /* @var $setting \common\components\Setting */
        $setting = Yii::$app->setting;

        $app_name = $setting->get(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME, Yii::$app->name);
        //placeholders: {name},{url},
        $message = strtr($template['body'], [
            '{{name}}' => $user->name,
            '{{url}}' => Yii::$app->getUrlManager()->createAbsoluteUrl(['/auth/auth/reset-password', 'token' => $user->password_reset_token]),
        ]);

        $email = [
            'message' => $message,
            'subject' => $template['subject'],
            'sender_name' => $app_name,
            'sender_email' => $template['sender'],
            'recipient_email' => $this->email,
            'type' => EmailQueue::TYPE_RESET_PASSWORD,
            'ref_id' => $user->id,
        ];
        return EmailQueue::pushToQueue($email);
    }
}
