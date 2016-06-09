<?php

namespace common\extensions\highchart;

use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * HighCharts widget
 * @uses Twitter Bootrap version 2.1.0
 * @author Fred <mconyango@gmail.com>
 */
class HighChart extends Widget
{

    /**
     * Graph data
     * @var array
     */
    public $data = [];

    /**
     * The number of charts in a page
     * @var int
     */
    public $chartCount = 1;

    /**
     * HTML options for the chart container
     * @var array
     */
    public $htmlOptions = [];

    /**
     * Graph container id
     * @var string
     */
    private $containerId;

    /**
     *
     * @var string
     */
    public $chartTemplate = '{filter_form}<hr/>{chart}';

    /**
     *
     * @var string
     */
    public $filterFormTemplate = '<div class="row"><div class="col-md-6">{graph_type}</div><div class="col-md-6">{date_range}</div></div>';

    /**
     *
     * @var array
     */
    public $filterFormHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $graphTypeFilterHtmlOptions = [
        'class' => '',
        'style' => 'width:auto;'
    ];

    /**
     *
     * @var array
     */
    public $dateRangeFilterHtmlOptions = [
        'class' => 'my-date-range-picker pull-right',
    ];

    /**
     *
     * @var bool
     */
    public $showDateRangeFilter = true;

    /**
     *
     * @var bool
     */
    public $showGraphTypeFilter = true;

    /**
     *
     * @var array
     */
    public $highChartOptions = [];

    /**
     *
     * @var bool
     */
    private $showFilter = true;

    /**
     *
     * @var string
     */
    private $dateRangeFrom = null;

    /**
     *
     * @var string
     */
    private $dateRangeTo = null;

    /**
     *
     * @var array
     */
    private $dateRangeFormat = ['php' => 'M d, Y', 'js' => 'MMM D, YYYY'];

    /**
     * Graph type filters (drop-down data)
     * @array type
     */
    public $graphTypeOptions = [];

    /**
     *
     * @var string
     */
    private $chartID;

    //----------------------//
    ///--------------------  //
    /*New properties*/
    /**
     * The data source class name
     * @var string string
     */
    public $modelClass;

    /**
     * SQL query options
     * @var array
     */
    public $queryOptions = [
        'filters' => [], //Any $key=>$value table filters where $key is a column name and $value is the columns value. This will lead to a series of AND conditions
        'condition' => '', //string MUST NOT BE ARRAY Any condition that must be passed to all query. This value is only necessary when passing other conditions which are not "AND". for conditions with "AND" use table_filters instead. e.g  "(`org_id`='3' OR `org_id`='6')",
        'params' => [], //params for the condition
        'dateField' => 'created_at',
        'dateRange' => '',//date range of query eg '2015-01-01 - 2015-12-31'
        'sum' => false, //If this value is FALSE then COUNT(*) will be applied. If you want to get the SUM(colum_name) then pass the column_name e.g "sum"=>"column_name"
        'enforceDate' => false,
    ];
    /**
     * @var string pie,line,spline,column,area,areaspline
     */
    public $graphType;

    /**
     * @var string
     */
    public $graphTitle = 'Report';

    /**
     * @var string
     */
    public $graphSubtitle;

    /**
     * @var array
     */
    private $xLabels;

    /**
     * @var int
     */
    private $xLabelsStep;
    /**
     * @see {getXAxisParams()}
     * @var array
     */
    private $xAxisParams = [];
    /**
     * @var int max labels in the X Axis
     */
    private $maxXLabels = 12;
    /**
     * @var string
     */
    public $yAxisLabel;
    /**
     * @var int
     */
    private $yAxisMin = 0;

    /**
     * @var array
     */
    public $series;

    /**
     * @var array
     */
    private $seriesOptions;
    /**
     * @var string //this will be used as pie-chart series name
     */
    public $defaultSeriesName;

    /**
     * @var array
     */
    private $colors = [];

    //date types

    const DATE_TYPE_DAY = 'day';
    const DATE_TYPE_MONTH = 'month';
    const DATE_TYPE_YEAR = 'year';
    //define get params
    const GET_PARAM_GRAPH_TYPE = 'g_t';
    const GET_PARAM_DATE_RANGE = 'd_r';
    const GET_PARAM_HIGHCHART_FLAG = 'ajax_highchart_request';

    //graph types
    const GRAPH_PIE = 'pie';
    const GRAPH_LINE = 'line';
    const GRAPH_SPLINE = 'spline';
    const GRAPH_COLUMN = 'column';
    const GRAPH_AREA = 'area';
    const GRAPH_AREASPLINE = 'areaspline';
    //plot options
    /**
     * If the date range is 90days (~3 months) or less then show x labels in days
     * @var int
     */
    const MAX_X_INTERVAL_DAY = 90;

    /**
     * If the date range is greater than 60days(~2 months) or less than or equal to 366days (~1 year) show x labels in months
     * @var int
     */
    const MAX_X_INTERVAL_MONTH = 366;

    public function init()
    {
        $this->containerId = 'my_high_chart_' . $this->chartCount;
        $this->htmlOptions['id'] = $this->containerId;
        $this->htmlOptions['id'] = $this->containerId;
        $this->chartID = $this->containerId . '_chart';
        $this->filterFormHtmlOptions['id'] = $this->containerId . '_form';
        $this->dateRangeFilterHtmlOptions['id'] = $this->containerId . '_' . self::GET_PARAM_DATE_RANGE;
        $this->graphTypeFilterHtmlOptions['id'] = $this->containerId . '_' . self::GET_PARAM_GRAPH_TYPE;
        $this->setDefaults();

        if ($this->showGraphTypeFilter || $this->showDateRangeFilter)
            $this->showFilter = TRUE;
        else
            $this->showFilter = FALSE;
        list($from, $to) = static::explodeDateRange($this->dateRangeFormat['php']);
        $this->dateRangeFrom = $from;
        $this->dateRangeTo = $to;

        parent::init();
    }

    public function setDefaults()
    {
        //graph type
        $this->setGraphType(self::GRAPH_LINE);
        //query options
        $this->setQueryOptions();
        //titles and labels
        if (empty($this->yAxisLabel))
            $this->yAxisLabel = $this->graphTitle;
        //graph options
        $defaultGraphOptions = [
            'chart' => ['renderTo' => $this->chartID],
            'title' => ['text' => $this->graphTitle],
            'subtitle' => ['text' => $this->graphSubtitle],
            'credits' => ['enabled' => false],
            'exporting' => [
                'enabled' => true,
                'buttons' => [
                    'contextButton' => ['enabled' => true],
                ]
            ],
            'legend' => ['enabled' => true],
            'colors' => ['#2f7ed8', '#23eb55', '#910000', '#0d233a', '#c92a9b', '#1aadce', '#ff0', '#492970', '#f28f43', '#77a1e5', '#c42525', '#c6c92a', '#434348', '#FDD01C', '#8bbc21'],
            'series' => $this->series,
        ];

        $options = $this->graphType === self::GRAPH_PIE ? $this->getPieChartOptions($defaultGraphOptions) : $this->getGraphOptions($defaultGraphOptions);
        $this->highChartOptions = array_replace_recursive($options, $this->highChartOptions);
    }

    /**
     * @param array $defaults
     * @return array
     */
    protected function getGraphOptions($defaults)
    {
        return array_replace_recursive($defaults, [
            'chart' => ['type' => $this->graphType],
            'xAxis' => [
                'labels' => [
                    'rotation' => -45,
                    'align' => 'right',
                    'style' => ['fontSize' => '10px'],
                ]
            ],
            'yAxis' => [
                'title' => [
                    'text' => $this->yAxisLabel,
                    'style' => ['fontWeight' => 'normal'],
                ],
                'min' => $this->yAxisMin,
                'allowDecimals' => false,
                'minRange' => 10,
            ],
            'tooltip' => [
                'crosshairs' => true,
                'shared' => true,
            ],
        ]);
    }

    /**
     * @param array $defaults
     * @return array
     */
    protected function getPieChartOptions($defaults)
    {
        return array_replace_recursive($defaults, [
            'chart' => [
                'type' => $this->graphType,
                'options3d' => [
                    'enabled' => true,
                    'alpha' => 45,
                    'beta' => 0,
                ]
            ],
            'tooltip' => [
                //pointFormat: '{point.y} {series.name}: <b>{point.percentage:.0f}%</b>',
                'pointFormat' => '{point.y}: <b>{point.percentage:.0f}%</b>',
            ],
            'plotOptions' => [
                'pie' => [
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'showInLegend' => true,
                    'depth' => 35,
                    'dataLabels' => [
                        'enabled' => true,
                        'color' => '#000000',
                        'connectorColor' => '#000000',
                        'format' => '<b>{point.name}</b>: {point.percentage:.0f} %',
                    ]
                ]
            ],
        ]);
    }

    public function run()
    {
        $this->getData();

        //set series
        $this->highChartOptions = array_replace_recursive($this->highChartOptions, [
            'series' => $this->series,
            'subtitle' => ['text' => $this->graphSubtitle],
            'colors' => $this->colors,
        ]);

        $graph_type_filter = '';
        $date_range_filter_container = '';
        $filter_form = '';
        if ($this->showGraphTypeFilter) {
            $graph_type_filter = Html::dropDownList(self::GET_PARAM_GRAPH_TYPE, $this->graphType, !empty($this->graphTypeOptions) ? $this->graphTypeOptions : static::graphTypes(), $this->graphTypeFilterHtmlOptions);
        }
        if ($this->showDateRangeFilter) {
            $date_range_string = $this->dateRangeFrom . ' - ' . $this->dateRangeTo;
            $date_range_filter = Html::tag('span', $date_range_string, []);
            $date_range_hidden = Html::hiddenInput(self::GET_PARAM_DATE_RANGE);
            $date_range_filter_container = Html::tag('div', $date_range_hidden . '<i class="fa fa-calendar fa-lg"></i>&nbsp;' . $date_range_filter . '&nbsp;<b class = "caret"></b>', $this->dateRangeFilterHtmlOptions);
        }
        if ($this->showFilter) {
            $filter_form .= Html::beginForm(Url::current(), 'get', $this->filterFormHtmlOptions);
            $filter_form .= strtr($this->filterFormTemplate, [
                '{graph_type}' => $graph_type_filter,
                '{date_range}' => $date_range_filter_container,
            ]);
            $filter_form .= Html::hiddenInput(self::GET_PARAM_HIGHCHART_FLAG, true);
            $filter_form .= Html::endForm();
        }

        $chart = Html::tag('div', '', ['id' => $this->chartID]);
        $contents = strtr($this->chartTemplate, [
            '{filter_form}' => $filter_form,
            '{chart}' => $chart,
        ]);

        echo Html::tag('div', $contents, $this->htmlOptions);

        $this->registerAssets();
    }

    protected function registerAssets()
    {
        $view = $this->getView();

        AssetBundle::register($view);

        $filterOptions = [
            'containerId' => $this->containerId,
            'showFilter' => $this->showFilter,
            'filterFormId' => $this->filterFormHtmlOptions['id'],
            'dateRangeFilterId' => $this->dateRangeFilterHtmlOptions['id'],
            'graphTypeFilterId' => $this->graphTypeFilterHtmlOptions['id'],
            'dateRangeFrom' => $this->dateRangeFrom,
            'dateRangeTo' => $this->dateRangeTo,
            'dateRangeFormat' => $this->dateRangeFormat['js'],
        ];
        $view->registerJs("MyApp.plugin.highCharts(" . Json::encode($this->highChartOptions) . "," . Json::encode($filterOptions) . ");", View::POS_READY, $this->containerId . $this->chartCount);
    }

    protected function getData()
    {
        //set series
        $this->setSeriesOptions();

        if ($this->graphType !== self::GRAPH_PIE) {
            //get other graph data(with x and y axis)
            $this->xAxisParams = $this->getXAxisParams();
            $this->setGraphData();
        } else {
            $this->setPieData();
        }

        /* if (isset($_GET[self::GET_PARAM_HIGHCHART_FLAG])) {
             echo json_encode($data);
             Yii::app()->end();
         } else
             return $data;
        */
    }


    /**
     * Get x,y axis graph data
     */
    protected function setGraphData()
    {
        $sum = $this->queryOptions['sum'];
        $dates = $this->xAxisParams['dates'];
        $from = current($dates);
        $to = end($dates);
        $x_labels = [];
        $date_type = $this->xAxisParams['dateType'];
        list($base_condition, $base_params) = $this->prepareQuery($from['date'], $to['date']);
        $series_options = !empty($this->seriesOptions) ? $this->seriesOptions : [];
        $date_field = $this->queryOptions['dateField'];

        foreach ($dates as $date) {
            $x_labels[] = $date['label'];
            $condition = !empty($base_condition) ? $base_condition . ' AND ' : $base_condition;
            $params = $base_params;
            if ($date_type === self::DATE_TYPE_DAY) {
                $condition .= "(DATE([[{$date_field}]])=DATE(:{$date_field}))";
            } else if ($date_type === self::DATE_TYPE_MONTH) {
                $condition .= "(MONTH([[{$date_field}]])=MONTH(:{$date_field}) AND YEAR([[{$date_field}]])=YEAR(:{$date_field}))";
            } else {
                $condition .= "(YEAR([[{$date_field}]])=YEAR(:{$date_field}))";
            }
            $params[":{$date_field}"] = $date['date'];

            foreach ($series_options as $k => $element) {
                $final_condition = !empty($element['condition']) ? $element['condition'] . ' AND ' . $condition : $condition;
                $final_params = !empty($element['params']) ? array_merge($element['params'], $params) : $params;
                $sum = isset($element['sum']) ? $element['sum'] : $sum;
                /* @var $class_name string|\common\models\ActiveRecord */
                $class_name = $this->modelClass;
                $data = $sum ? $class_name::getSum($sum, $final_condition, $final_params) : $class_name::getCount($final_condition, $final_params);
                $series_options[$k]['data'][] = (float)round($data, 2);
            }
        }

        $this->xLabels = $x_labels;
        if (empty($this->graphSubtitle) && !empty($this->queryOptions['dateRange']))
            $this->graphSubtitle = $this->queryOptions['dateRange'];


        $series_colors = [];
        $series = [];
        foreach ($series_options as $k => $element) {
            $element['type'] = $this->graphType;
            if (isset($element['condition']))
                unset($element['condition']);
            if (isset($element['params']))
                unset($element['params']);
            if (isset($element['sum']))
                unset($element['sum']);
            if (isset($element['color'])) {
                $series_colors[] = $element['color'];
                unset($element['color']);
            }
            array_push($series, $element);
        }

        $this->series = $series;
        $this->xLabelsStep = $this->xAxisParams['step'];
        $this->colors = $series_colors;

        $this->highChartOptions = array_replace_recursive($this->highChartOptions, [
            'xAxis' => [
                'categories' => $this->xLabels,
                'labels' => [
                    'step' => $this->xLabelsStep,
                ]
            ],
        ]);
    }

    /**
     * @return array
     */
    protected function setPieData()
    {
        $sum = $this->queryOptions['sum'];
        $from = null;
        $to = null;
        if (!empty($this->queryOptions['dateRange'])) {
            list($from, $to) = $this->explodeDateRange();
        }

        list($base_condition, $base_params) = $this->prepareQuery($from, $to);
        $series_options = $this->seriesOptions;
        $series_colors = [];
        $data = isset($series_options[0]['data']) ? $series_options[0]['data'] : [];
        $series_options[0]['type'] = $this->graphType;
        $series_options[0]['name'] = !empty($this->defaultSeriesName) ? $this->defaultSeriesName : $this->graphTitle;

        foreach ($data as $k => $element) {
            $condition = $base_condition;
            if (!empty($condition) && !empty($element['condition']))
                $condition .= ' AND ';
            $condition .= $element['condition'];
            $params = !empty($element['params']) ? array_merge($element['params'], $base_params) : $base_params;
            $sum = isset($series_options[0]['data'][$k]['sum']) ? $series_options[0]['data'][$k]['sum'] : $sum;
            /* @var $class_name string|\common\models\ActiveRecord */
            $class_name = $this->modelClass;
            $data = $sum ? $class_name::getScalar($sum, $condition, $params) : $class_name::getCount($condition, $params);
            $series_options[0]['data'][$k]['y'] = (float)round($data, 2);
            if (isset($series_options[0]['data'][$k]['condition']))
                unset($series_options[0]['data'][$k]['condition']);
            if (isset($series_options[0]['data'][$k]['params']))
                unset($series_options[0]['data'][$k]['params']);
            if (isset($series_options[0]['data'][$k]['sum']))
                unset($series_options[0]['data'][$k]['sum']);
            if (isset($series_options[0]['data'][$k]['color'])) {
                $series_colors[] = $series_options[0]['data'][$k]['color'];
                unset($series_options[0]['data'][$k]['color']);
            }
        }

        if (empty($this->graphSubtitle))
            $this->graphSubtitle = (!empty($this->queryOptions['dateRange']) ? $this->queryOptions['dateRange'] : null);

        $this->series = $series_options;
        $this->colors = $series_colors;
    }

    /**
     * prepare a highchart data query
     * @param string $from_date
     * @param string $to_date
     * @return string
     */
    protected function prepareQuery($from_date = null, $to_date = null)
    {
        //filters
        $condition = $this->queryOptions['condition'];
        $params = isset($this->queryOptions['params']) ? $this->queryOptions['params'] : [];
        $filters = ArrayHelper::getValue($this->queryOptions, 'filters', []);
        $date_field = $this->queryOptions['dateField'];

        foreach ($filters as $k => $v) {
            if (!empty($v)) {
                $condition .= !empty($condition) ? ' AND ' : '';
                $condition .= "([[{$k}]]=:{$k})";
                $params[":{$k}"] = $v;
            }
        }
        //date boundary
        if (!empty($from_date) && !empty($to_date)) {
            //make sure that data falls between "from date" and "to date"
            $condition .= !empty($condition) ? ' AND ' : '';
            $condition .= "(DATE([[{$date_field}]])>=DATE(:t1_from) AND DATE([[{$date_field}]])<=DATE(:t2_to))";
            $params[':t1_from'] = $from_date;
            $params[':t2_to'] = $to_date;
        }
        return [$condition, $params];
    }

    /**
     * Initialize the properties
     */
    protected function setQueryOptions()
    {
        if (empty($this->queryOptions['dateField']))
            $this->queryOptions['dateField'] = 'created_at';
        if (empty($this->queryOptions['sum']))
            $this->queryOptions['sum'] = false;
        //set date range
        if (isset($_GET[self::GET_PARAM_DATE_RANGE]))
            $this->queryOptions['dateRange'] = $_GET[self::GET_PARAM_DATE_RANGE];
        if (empty($this->queryOptions['enforceDate']))
            $this->queryOptions['enforceDate'] = false;
        if (empty($this->queryOptions['dateRange']) && $this->queryOptions['enforceDate'])
            $this->queryOptions['dateRange'] = static::getDefaultDateRange();

    }

    /**
     *
     * @param string $format
     * @return string
     */
    public static function getDefaultDateRange($format = 'M d, Y')
    {
        $from = DateUtils::addDate(date('Y-m-d'), -1, 'month', $format);
        $to = date($format, time());
        $date_range = $from . ' - ' . $to;
        return $date_range;
    }

    /**
     * Set graph type
     * @param string $value
     */
    protected function setGraphType($value)
    {
        if (isset($_GET[self::GET_PARAM_GRAPH_TYPE]))
            $this->graphType = $_GET[self::GET_PARAM_GRAPH_TYPE];
        else if (empty($this->graphType))
            $this->graphType = $value;
    }

    /**
     *
     */
    protected function setSeriesOptions()
    {
        /* @var $class_name string | \common\extensions\highchart\HighChartInterface */
        $class_name = $this->modelClass;
        $this->seriesOptions = !empty($seriesMethodName) ? $class_name::$seriesMethodName($this->graphType, $this->queryOptions) : $class_name::highChartOptions($this->graphType, $this->queryOptions);
    }

    /**
     * Generate x-values
     * @return array
     */
    protected function getXAxisParams()
    {
        $max_label = (int)$this->maxXLabels;
        if (empty($max_label))
            $max_label = 12;
        $result = [];
        list($from, $to) = $this->explodeDateRange();
        $date_interval = DateUtils::getDateDiff($from, $to);
        $days_interval = $date_interval->days;

        if ($days_interval <= self::MAX_X_INTERVAL_DAY) {
            $x_interval = (int)round($days_interval / $max_label);
            $x_dates = DateUtils::generateDateSpan($from, $to, 1, 'day');
            $result['dateType'] = self::DATE_TYPE_DAY;
            $result['dates'] = $this->getXAxisDates($x_dates, 'M j, Y');
            $result['step'] = $x_interval;
        } else if ($days_interval > self::MAX_X_INTERVAL_DAY && $days_interval <= self::MAX_X_INTERVAL_MONTH) {
            //assume each month is 30days
            $x_interval = (int)round(($days_interval / 30) / $max_label);
            $x_dates = DateUtils::generateDateSpan($from, $to, $x_interval, 'month');
            $result['dateType'] = self::DATE_TYPE_MONTH;
            $result['dates'] = $this->getXAxisDates($x_dates, 'M Y');
            $result['step'] = 1;
        } else {
            //assume each year is 365days
            $x_interval = (int)round(($days_interval / 365) / $max_label);
            $x_dates = DateUtils::generateDateSpan($from, $to, $x_interval, 'year');
            $result['dateType'] = self::DATE_TYPE_YEAR;
            $result['dates'] = $this->getXAxisDates($x_dates, 'Y');
            $result['step'] = 1;
        }
        $result['graphType'] = $this->graphType;

        return $result;
    }

    /**
     * @param array $dates
     * @param string $format
     * @return array
     */
    protected function getXAxisDates($dates, $format)
    {
        $previous_formatted = [];
        $x_axis_dates = [];
        foreach ($dates as $date) {
            $formatted = DateUtils::formatDate($date, $format);
            if (!in_array($formatted, $previous_formatted)) {
                array_push($x_axis_dates, [
                    'date' => $date,
                    'label' => $formatted,
                ]);
            }
            $previous_formatted[] = $formatted;
        }
        return $x_axis_dates;
    }

    /**
     *  Format daterange into from and to array values
     * @param string $format
     * @return array
     */
    protected function explodeDateRange($format = 'Y-m-d')
    {
        if (empty($this->queryOptions['dateRange']))
            $this->queryOptions['dateRange'] = $this->getDefaultDateRange();
        $date_range = explode('-', $this->queryOptions['dateRange']);
        $from = DateUtils::formatDate(trim($date_range[0]), $format);
        $to = isset($date_range[1]) ? DateUtils::formatDate(trim($date_range[1]), $format) : NULL;
        return [trim($from), trim($to)];
    }

    /**
     * get all the graph types
     * @return array
     */
    public static function graphTypes()
    {
        return [
            self::GRAPH_PIE => Lang::t('Pie'),
            self::GRAPH_LINE => Lang::t('Line'),
            self::GRAPH_SPLINE => Lang::t('Smooth Line'),
            self::GRAPH_COLUMN => Lang::t('Bar/Column'),
            self::GRAPH_AREA => Lang::t('Area'),
            self::GRAPH_AREASPLINE => Lang::t('Smooth Area'),
        ];
    }

}
