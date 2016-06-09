<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 1:04 PM
 */

namespace backend\modules\conf\forms;


use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\models\Model;
use Yii;

class EmailSettings extends Model
{
    /**
     * @var
     */
    public $email_host;

    /**
     * @var
     */
    public $email_port;
    /**
     * @var
     */
    public $email_username;

    /**
     * @var
     */
    public $email_password;

    /**
     * @var
     */
    public $email_security;

    /**
     * @var
     */
    public $email_theme;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        /**
         * @var $settings \common\components\Setting
         */
        $settings = Yii::$app->setting;
        $this->email_host = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_HOST);
        $this->email_port = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PORT);
        $this->email_username = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_USERNAME);
        $this->email_password = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PASSWORD);
        $this->email_security = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_SECURITY);
        $this->email_theme = $settings->get(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_THEME);

        parent::init();
    }


    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['email_host', 'email_port', 'email_username', 'email_password', 'email_security', 'email_theme'], 'required'],
            [['email_port'], 'integer'],
            [['email_username'], 'email'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email_host' => Lang::t('Mail Host'),
            'email_port' => Lang::t('Mail host port'),
            'email_username' => Lang::t('Mail username'),
            'email_password' => Lang::t('Mail password'),
            'email_security' => Lang::t('Mail security'),
            'email_theme' => Lang::t('Mail Theme'),
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            /**
             * @var $settings \common\components\Setting
             */
            $settings = Yii::$app->setting;
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_HOST, $this->email_host);
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PORT, $this->email_port);
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_USERNAME, $this->email_username);
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_PASSWORD, $this->email_password);
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_SECURITY, $this->email_security);
            $settings->set(Constants::SECTION_EMAIL, Constants::KEY_EMAIL_THEME, $this->email_theme);

            $settings->commit();
            return true;
        }
        return false;
    }

}