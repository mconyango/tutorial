<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/27
 * Time: 5:55 PM
 */

namespace backend\modules\conf\forms;


use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\models\Model;
use Yii;

class GMapSettings extends Model
{
    /**
     * @var string
     */
    public $api_key;
    /**
     * @var string
     */
    public $default_map_center;

    /**
     * @var string
     */
    public $default_map_type;

    /**
     * @var string
     */
    public $crowd_map_zoom;

    /**
     * @var string
     */
    public $single_view_zoom;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /**
         * @var $settings \common\components\Setting
         */
        $settings = Yii::$app->setting;

        $this->api_key = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_API_KEY);
        $this->default_map_center = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_DEFAULT_CENTER);
        $this->default_map_type = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_DEFAULT_MAP_TYPE);
        $this->crowd_map_zoom = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_CROWD_MAP_ZOOM);
        $this->single_view_zoom = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_SINGLE_VIEW_ZOOM);

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['api_key', 'default_map_center', 'default_map_type'], 'required'],
            [['crowd_map_zoom', 'single_view_zoom'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'api_key' => Lang::t('API Key'),
            'default_map_center' => Lang::t('Default Map Center'),
            'default_map_type' => Lang::t('Default Map Type'),
            'crowd_map_zoom' => Lang::t('Crowd Map Zoom'),
            'single_view_zoom' => Lang::t('Single View Zoom'),
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            /**
             * @var $settings \common\components\Setting
             */
            $settings = Yii::$app->setting;
            $settings->set(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_API_KEY, $this->api_key);
            $settings->set(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_DEFAULT_CENTER, $this->default_map_center);
            $settings->set(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_DEFAULT_MAP_TYPE, $this->default_map_type);
            $settings->set(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_CROWD_MAP_ZOOM, $this->crowd_map_zoom);
            $settings->set(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_SINGLE_VIEW_ZOOM, $this->single_view_zoom);
            $settings->commit();
            return true;
        }
        return false;
    }


}