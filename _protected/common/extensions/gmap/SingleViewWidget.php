<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/27
 * Time: 5:45 PM
 */

namespace common\extensions\gmap;


use backend\modules\conf\Constants;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

class SingleViewWidget extends Widget
{
    /**
     * html options of the map wrapper
     * @var array
     */
    public $mapWrapperHtmlOptions = [];


    /**
     * @var string
     */
    public $latitude;
    /**
     * @var
     */
    public $longitude;

    /**
     * @var string
     */
    public $infowindowContent;

    /**
     * @var int
     */
    public $zoom;

    /**
     * @var string
     */
    public $mapType;

    /**
     * @var bool
     */
    public $panControl = true;
    /**
     * @var bool
     */
    public $zoomControl = true;
    /**
     * @var bool
     */
    public $scaleControl = true;
    /**
     * @var string
     */
    public $markerColor = 'FF0000';

    /**
     * lat,lng centre of the map
     * @var string
     */
    private $mapCentre;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->mapWrapperHtmlOptions['id'])) {
            $this->mapWrapperHtmlOptions['id'] = 'gmap-single-view-xx';
        }

        /* @var $settings \common\components\Setting */
        $settings = Yii::$app->setting;
        if (empty($this->latitude) || empty($this->longitude)) {
            $this->mapCentre = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_DEFAULT_CENTER);
            if (!empty($this->mapCentre)) {
                $center = explode(',', $this->mapCentre);
                $this->latitude = $center[0];
                $this->longitude = isset($center[1]) ? $center[1] : NULL;
            }
        }
        if (empty($this->zoom)) {
            $this->zoom = $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_SINGLE_VIEW_ZOOM, 6);
        }

        if (empty($this->mapType))
            $this->mapType = GmapUtils::MAP_TYPE_HYBRID;

        parent::init();
    }

    public function run()
    {
        echo Html::tag('div', "", $this->mapWrapperHtmlOptions);
        $this->registerAssets();
    }

    protected function registerAssets()
    {
        $view = $this->getView();
        /* @var $settings \common\components\Setting */
        $settings = Yii::$app->setting;
        $map_js = 'https://maps.googleapis.com/maps/api/js?key=' . $settings->get(Constants::SECTION_GOOGLE_MAP, Constants::KEY_GOOGLE_MAP_API_KEY, 'AIzaSyDVdy5_ajfxOQYIjDmHYRt_l0k20YFus-I');
        $view->registerJsFile($map_js);
        AssetBundle::register($view);

        $options = [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'mapWrapperId' => $this->mapWrapperHtmlOptions['id'],
            'infowindowContent' => $this->infowindowContent,
            'zoom' => $this->zoom,
            'mapType' => $this->mapType,
            'panControl' => $this->panControl,
            'zoomControl' => $this->zoomControl,
            'scaleControl' => $this->scaleControl,
            'markerColor' => $this->markerColor,
        ];
        $view->registerJs("MyApp.gmap.singleView(" . Json::encode($options) . ");");
    }


}