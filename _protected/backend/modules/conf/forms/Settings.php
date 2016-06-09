<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 2:10 PM
 */

namespace backend\modules\conf\forms;


use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\models\Model;
use Yii;

class Settings extends Model
{
    /**
     *
     * @var string
     */
    public $company_name;
    /**
     *
     * @var string
     */
    public $app_name;

    /**
     *
     * @var string
     */
    public $company_email;

    /**
     *
     * @var string
     */
    public $default_timezone;

    /**
     *
     * @var integer
     */
    public $country_id;

    /**
     *
     * @var integer
     */
    public $items_per_page;

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
        $this->app_name = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME, Yii::$app->name);
        $this->company_name = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_COMPANY_NAME, Yii::$app->name);
        $this->company_email = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_COMPANY_EMAIL);
        $this->default_timezone = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_DEFAULT_TIMEZONE, 'Africa/Nairobi');
        $this->items_per_page = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_ITEMS_PER_PAGE, 50);
        $this->country_id = $settings->get(Constants::SECTION_SYSTEM, Constants::KEY_COUNTRY_ID);

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
            [['company_name', 'app_name', 'default_timezone', 'country_id', 'items_per_page'], 'required'],
            [['items_per_page'], 'integer'],
            [['company_email'], 'email', 'message' => 'Enter a valid Email Address.'],
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
            'company_name' => Lang::t('Company Name'),
            'app_name' => Lang::t('App Name'),
            'company_email' => Lang::t('Email'),
            'default_timezone' => Lang::t('Default Timezone'),
            'country_id' => Lang::t('Country'),
            'items_per_page' => Lang::t('Items Per Page'),
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            /**
             * @var $settings \common\components\Setting
             */
            $settings = Yii::$app->setting;
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_APP_NAME, $this->app_name);
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_COMPANY_NAME, $this->company_name);
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_COMPANY_EMAIL, $this->company_email);
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_DEFAULT_TIMEZONE, $this->default_timezone);
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_COUNTRY_ID, $this->country_id);
            $settings->set(Constants::SECTION_SYSTEM, Constants::KEY_ITEMS_PER_PAGE, $this->items_per_page);

            $settings->commit();
            return true;
        }
        return false;
    }
}