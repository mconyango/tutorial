<?php

/**
 * Extension for Google Map
 *
 * @author Fred <mconyango@gmail.com>
 */
class GmapGeocode extends CWidget {

        /**
         * Model associated with the geocode
         * @ActiveRecord $model
         */
        public $model;

        /**
         * geocode url
         * @string type
         */
        public $geocode_url;

        /**
         * lat,lng centre of the map
         * @var type
         */
        private $map_centre;
        public $lat;
        public $lng;

        /**
         *
         * @var type
         */
        public $lat_field = 'latitude';

        /**
         *
         * @var type
         */
        public $lng_field = 'longitude';

        /**
         *
         * @var type
         */
        public $address_field = 'location';

        /**
         *
         * @var type
         */
        public $template = '<div class="form-group">{{lat}}{{lng}}</div>{{map}}<div class="form-group">{{address}}</div>';

        /**
         * Template for latitude field
         * @var type
         */
        public $lat_field_template = '<div class="col-md-6">{{label}}{{input}}</div>';

        /**
         *
         * @var type
         */
        public $lat_field_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $lat_label_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $show_lat_label = false;

        /**
         * Template for longitude field
         * @var type
         */
        public $lng_field_template = '<div class="col-md-6">{{label}}{{input}}</div>';

        /**
         *
         * @var type
         */
        public $lng_field_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $lng_label_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $show_lng_label = false;

        /**
         * html options of the map wrapper
         * @var type
         */
        public $map_wrapper_htmlOptions = array();

        /**
         * address field template
         * @var type
         */
        public $address_field_template = '{{label}}<div class="col-md-7">{{input}}</div><div class="col-md-2">{{search_button}}</div>';

        /**
         *
         * @var type
         */
        public $address_field_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $address_label_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $address_search_htmlOptions = array();

        /**
         *
         * @var type
         */
        public $show_address_label = true;

        public function init() {
                //lat field html options
                if (empty($this->lat_field_htmlOptions['class'])) {
                        $this->lat_field_htmlOptions['class'] = 'form-control';
                }
                if (!$this->show_lat_label) {
                        $this->lat_field_htmlOptions['placeholder'] = $this->model->getAttributeLabel($this->lat_field);
                }
                $this->lat_field_htmlOptions['readonly'] = true;
                //lng field html options
                if (empty($this->lng_field_htmlOptions['class'])) {
                        $this->lng_field_htmlOptions['class'] = 'form-control';
                }
                if (!$this->show_lng_label) {
                        $this->lng_field_htmlOptions['placeholder'] = $this->model->getAttributeLabel($this->lng_field);
                }
                $this->lng_field_htmlOptions['readonly'] = true;

                //address field html options
                if (empty($this->address_field_htmlOptions['class'])) {
                        $this->address_field_htmlOptions['class'] = 'form-control';
                }
                if (empty($this->address_field_htmlOptions['placeholder'])) {
                        $this->address_field_htmlOptions['placeholder'] = Lang::t('Search location on the map.');
                }
                if ($this->show_address_label) {
                        if (empty($this->address_label_htmlOptions['class']))
                                $this->address_label_htmlOptions['class'] = 'col-md-2 control-label';
                }
                if (empty($this->address_search_htmlOptions['class']))
                        $this->address_search_htmlOptions['class'] = 'btn btn-sm btn-default';
                $this->address_search_htmlOptions['type'] = 'button';
                $this->address_search_htmlOptions['id'] = 'my_geocode_address_search';

                if (empty($this->map_wrapper_htmlOptions['id'])) {
                        $this->map_wrapper_htmlOptions['id'] = 'my_gmap_geocode';
                }

                if (empty($this->lat) || empty($this->lng)) {
                        $this->map_centre = Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_DEFAULT_CENTER);
                        if (!empty($this->map_centre)) {
                                $center = explode(',', $this->map_centre);
                                $this->lat = $center[0];
                                $this->lng = isset($center[1]) ? $center[1] : NULL;
                        }
                }

                parent::init();
        }

        public function run() {
                //lat
                $lat_label = '';
                if ($this->show_lat_label) {
                        $lat_label = CHtml::activeLabelEx($this->model, $this->lat_field, $this->lat_label_htmlOptions);
                }
                $lat_field = CHtml::activeTextField($this->model, $this->lat_field, $this->lat_field_htmlOptions);
                $lat = Common::myStringReplace($this->lat_field_template, array(
                            '{{label}}' => $lat_label,
                            '{{input}}' => $lat_field,
                ));
                //lng
                $lng_label = '';
                if ($this->show_lng_label) {
                        $lng_label = CHtml::activeLabelEx($this->model, $this->lng_field, $this->lng_label_htmlOptions);
                }
                $lng_field = CHtml::activeTextField($this->model, $this->lng_field, $this->lng_field_htmlOptions);
                $lng = Common::myStringReplace($this->lng_field_template, array(
                            '{{label}}' => $lng_label,
                            '{{input}}' => $lng_field,
                ));

                //address
                $address_label = '';
                if ($this->show_address_label) {
                        $address_label = CHtml::activeLabelEx($this->model, $this->address_field, $this->address_label_htmlOptions);
                }
                $address_field = CHtml::activeTextField($this->model, $this->address_field, $this->address_field_htmlOptions);
                $search_button = CHtml::tag('button', $this->address_search_htmlOptions, CHtml::tag('i', array('class' => 'fa fa-search'), '') . ' ' . Lang::t('Search'), true);
                $address = Common::myStringReplace($this->address_field_template, array(
                            '{{label}}' => $address_label,
                            '{{input}}' => $address_field,
                            '{{search_button}}' => $search_button,
                ));

                $map_wrapper = CHtml::tag('div', $this->map_wrapper_htmlOptions, "", true);
                //<div class="form-group">{{lat}}{{lng}}</div>{{map}}<div class="form-group">{{address}}</div>
                $html = Common::myStringReplace($this->template, array(
                            '{{lat}}' => $lat,
                            '{{lng}}' => $lng,
                            '{{map}}' => $map_wrapper,
                            '{{address}}' => $address,
                ));
                echo $html;
                $this->registerAssets();
        }

        protected function registerAssets() {
                $assets = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
                $baseUrl = Yii::app()->assetManager->publish($assets);
                $model_class_name = $this->model->getClassName();
                $options = CJavaScript::encode(array(
                            'lat' => $this->lat,
                            'lng' => $this->lng,
                            'geocode_url' => $this->geocode_url,
                            'lat_field_id' => $model_class_name . '_' . $this->lat_field,
                            'lng_field_id' => $model_class_name . '_' . $this->lng_field,
                            'address_field_id' => $model_class_name . '_' . $this->address_field,
                            'map_wrapper_id' => $this->map_wrapper_htmlOptions['id'],
                            'address_search_field_id' => $this->address_search_htmlOptions['id'],
                            'zoom' => (int) Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_SINGLE_VIEW_ZOOM),
                ));
                Yii::app()->clientScript
                        ->registerScriptFile('http://maps.googleapis.com/maps/api/js?key=' . Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_API_KEY) . '&sensor=false', CClientScript::POS_END)
                        ->registerScriptFile($baseUrl . '/js/geocode.js', CClientScript::POS_END)
                        ->registerScript(microtime(), "MyGmapGeocode.init(" . $options . ");", CClientScript::POS_READY);
        }

}
