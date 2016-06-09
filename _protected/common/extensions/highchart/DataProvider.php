<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/08
 * Time: 11:20 PM
 */

namespace common\extensions\highchart;


use common\helpers\DateUtils;
use common\helpers\Lang;
use common\models\ActiveRecord;
use yii\helpers\ArrayHelper;

class DataProvider
{
    /**
     * The data source class name or object
     * @var ActiveRecord|string string
     */
    public $model;

    /**
     * SQL query options
     * @var array
     */
    public $queryOptions = [
        'filters' => [], //Any $key=>$value table filters where $key is a column name and $value is the columns value. This will lead to a series of AND conditions
        'condition' => '', //string|array Any condition that must be passed to all querys. This value is only necessary when passing other conditions which are not "AND". for conditions with "AND" use table_filters instead. e.g  "(`org_id`='3' OR `org_id`='6')",
        'params' => [], //params for the condition
        'date_field' => 'created_at',
        'sum' => false, //If this value is FALSE then COUNT(*) will be applied. If you want to get the SUM(colum_name) then pass the column_name e.g "sum"=>"column_name"
    ];

    /**
     * Options for the display of the graph
     * @var array
     */
    public $graphOptions = [
        'graphType' => null,
        'title' => 'Report',
        'subtitle' => NULL,
        'y_axis_label' => NULL,
        'default_series_name' => NULL, //this will be used as piechart series name
        'y_axis_min' => 0,
    ];

    /**
     * The highchart series
     * {@link  http://api.highcharts.com/highcharts#series}
     * @var array
     */
    private $series = [];

    /**
     * @see {HighCharts::getXAxisParams()}
     * @var array
     */
    private $xAxisParams = [];

    /**
     *
     * @var string
     */
    protected $graphType;

    /**
     * date range
     * @var type
     */
    public static $dateRange;

    /**
     * If the date range is 90days (~3 months) or less then show x labels in days
     * @var type
     */
    public static $max_x_interval_day = 90;

    /**
     * If the date range is greater than 60days(~2 months) or less than or equal to 366days (~1 year) show x labels in months
     * @var type
     */
    public static $max_x_interval_month = 366;

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

    /**
     * Get graph data e.g line graph,bar graph etc
     * @param string|ActiveRecord $model The model class name providing the data
     * @param array $queryOptions {@see $this->query_options}
     * @param array $graphOptions {@see $this->graph_options}
     * @param integer $maxLabels Maximum labels of x,y axis graph . Default is 12
     * @param array $seriesOptionsMethodName
     * @param boolean $enforceDateRange Default FALSE
     * @return array $data
     */
    public static function getData($model, array $queryOptions = [], array $graphOptions = [], $maxLabels = 12, $seriesOptionsMethodName = null, $enforceDateRange = false)
    {
        $dataProvider = new DataProvider();
        if (is_string($model)) {
            $model = new $model();
        }
        $dataProvider->model = $model;
        $dataProvider->setVariables($queryOptions, $graphOptions, $seriesOptionsMethodName);
        if ($dataProvider->graphType !== HighChartsHelper::GRAPH_PIE) {
            //get other graph data(with x and y axis)
            $dataProvider->xAxisParams = HighChartsHelper::getXAxisParams($maxLabels);
            $data = $dataProvider->getGraphData();
        } else {
            $data = $dataProvider->getPieData($enforceDateRange);
        }

        if (isset($_GET[HighChartsHelper::GET_PARAM_HIGHCHART_FLAG])) {
            echo json_encode($data);
            Yii::app()->end();
        } else
            return $data;
    }

    /**
     * Get x,y axis graph data
     * @return type
     */
    protected function getGraphData()
    {
        $dataSource = $this->model;
        $sum = $this->queryOptions['sum'];
        $dates = $this->xAxisParams['dates'];
        $from = current($dates);
        $to = end($dates);
        $x_labels = [];
        $date_type = $this->xAxisParams['date_type'];
        $query = $this->prepareQuery($from['date'], $to['date']);
        $base_condition = $query['condition'];
        $base_params = $query['params'];
        $series = !empty($this->series) ? $this->series : [];
        $date_field = $this->queryOptions['date_field'];

        foreach ($dates as $date) {
            $x_labels[] = $date['label'];
            $condition = !empty($base_condition) ? $base_condition . ' AND ' : $base_condition;
            $params = $base_params;
            if ($date_type === HighChartsHelper::DATE_TYPE_DAY) {
                $condition .= "(DATE(`{$date_field}`)=DATE(:{$date_field}))";
            } else if ($date_type === HighChartsHelper::DATE_TYPE_MONTH) {
                $condition .= "(MONTH(`{$date_field}`)=MONTH(:{$date_field}) AND YEAR(`{$date_field}`)=YEAR(:{$date_field}))";
            } else {
                $condition .= "(YEAR(`{$date_field}`)=YEAR(:{$date_field}))";
            }
            $params[":{$date_field}"] = $date['date'];

            foreach ($series as $k => $element) {
                $final_condition = !empty($element['condition']) ? $element['condition'] . ' AND ' . $condition : $condition;
                $final_params = !empty($element['params']) ? array_merge($element['params'], $params) : $params;
                $sum = isset($element['sum']) ? $element['sum'] : $sum;
                $data = $sum ? $dataSource::model()->getSum($sum, $final_condition, $final_params) : $dataSource::model()->getTotals($final_condition, $final_params);
                $series[$k]['data'][] = (float)round($data, 2);
            }
        }

        $series_colors = [];
        $new_series = [];
        foreach ($series as $k => $element) {
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
            array_push($new_series, $element);
        }

        return [
            'graphType' => $this->graphType,
            'series' => $new_series,
            'x_labels' => $x_labels,
            'subtitle' => isset($this->graphOptions['subtitle']) ? $this->graphOptions['subtitle'] : HighChartsHelper::getDateRange(),
            'y_axis_title' => $this->graphOptions['y_axis_label'],
            'y_axis_min' => $this->graphOptions['y_axis_min'],
            'step' => $this->xAxisParams['step'],
            'title' => $this->graphOptions['title'],
            'colors' => $series_colors,
        ];
    }

    /**
     * @param bool|false $enforce_date_range
     * @return array
     */
    public function getPieData($enforce_date_range = false)
    {
        $dataSource = $this->model;
        $sum = $this->queryOptions['sum'];
        $from = null;
        $to = null;
        if ($enforce_date_range) {
            $date_range = HighChartsHelper::explodeDateRange();
            $from = $date_range['from'];
            $to = $date_range['to'];
        }

        $query = $this->prepareQuery($from, $to);
        $base_condition = $query['condition'];
        $base_params = $query['params'];
        $series = $this->series;
        $series_colors = [];
        $data = isset($series[0]['data']) ? $series[0]['data'] : [];
        $series[0]['type'] = $this->graphType;
        $chart_title = $this->graphOptions['title'];
        $series[0]['name'] = !empty($this->graphOptions['default_series_name']) ? $this->graphOptions['default_series_name'] : $chart_title;

        foreach ($data as $k => $element) {
            $condition = $base_condition;
            if (!empty($condition) && !empty($element['condition']))
                $condition .= ' AND ';
            $condition .= $element['condition'];
            $params = !empty($element['params']) ? array_merge($element['params'], $base_params) : $base_params;
            $sum = isset($series[0]['data'][$k]['sum']) ? $series[0]['data'][$k]['sum'] : $sum;
            $data = $sum ? $dataSource::model()->getScalar($sum, $condition, $params) : $dataSource::model()->getTotals($condition, $params);
            $series[0]['data'][$k]['y'] = (float)round($data, 2);
            if (isset($series[0]['data'][$k]['condition']))
                unset($series[0]['data'][$k]['condition']);
            if (isset($series[0]['data'][$k]['params']))
                unset($series[0]['data'][$k]['params']);
            if (isset($series[0]['data'][$k]['sum']))
                unset($series[0]['data'][$k]['sum']);
            if (isset($series[0]['data'][$k]['color'])) {
                $series_colors[] = $series[0]['data'][$k]['color'];
                unset($series[0]['data'][$k]['color']);
            }
        }

        return [
            'graphType' => $this->graphType,
            'series' => $series,
            'subtitle' => isset($this->graphOptions['subtitle']) ? $this->graphOptions['subtitle'] : ($enforce_date_range ? HighChartsHelper::getDateRange() : null),
            'title' => $chart_title,
            'colors' => $series_colors,
        ];
    }

    /**
     * prepare a highchart data query
     * @param type $from_date
     * @param type $to_date
     * @return type
     */
    protected function prepareQuery($from_date = null, $to_date = null)
    {
        $dataSource = $this->model;
        //filters
        $condition = $this->queryOptions['condition'];
        $params = isset($this->queryOptions['params']) ? $this->queryOptions['params'] : [];
        $table_filters = $this->queryOptions['filters'];
        $date_field = $this->queryOptions['date_field'];

        foreach ($table_filters as $k => $v) {
            if (!empty($v) && $dataSource::model()->hasAttribute($k)) {
                $condition .= !empty($condition) ? ' AND ' : '';
                $condition .= "(`{$k}`=:{$k})";
                $params[":{$k}"] = $v;
            }
        }
        //date boundary
        if (!empty($from_date) && !empty($to_date)) {
            //make sure that data falls between "from date" and "to date"
            $condition .= !empty($condition) ? ' AND ' : '';
            $condition .= "(DATE(`{$date_field}`)>=DATE(:t1_from) AND DATE(`{$date_field}`)<=DATE(:t2_to))";
            $params[':t1_from'] = $from_date;
            $params[':t2_to'] = $to_date;
        }
        return [
            'condition' => $condition,
            'params' => $params,
        ];
    }

    /**
     * Initialize the properties
     * @param array $queryOptions
     * @param array $graphOptions
     * @param array $seriesMethodName
     */
    protected function setVariables(array $queryOptions, array $graphOptions, $seriesMethodName)
    {
        $dataSource = $this->model;
        if (!empty($queryOptions))
            $this->queryOptions = ArrayHelper::merge($this->queryOptions, $queryOptions);
        if (!empty($graphOptions))
            $this->graphOptions = ArrayHelper::merge($this->graphOptions, $graphOptions);
        if (empty($this->graphOptions['y_axis_label']))
            $this->graphOptions['y_axis_label'] = $this->graphOptions['title'];

        HighChartsHelper::init();

        if (!empty($this->graphOptions['graphType']))
            HighChartsHelper::setGraphType($this->graphOptions['graphType']);
        $this->graphType = HighChartsHelper::getGraphType();
        $this->series = !empty($seriesMethodName) ? $dataSource::model()->$seriesMethodName($this->graphType, $this->queryOptions) : $dataSource::model()->highChartsSeriesOptions($this->graphType, $this->queryOptions);
    }


    /**
     * get all the graph types
     * @return array $graphTypes
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

    /**
     *
     * @param string $format
     * @return string
     */
    public static function defaultDateRange($format = 'M d, Y')
    {
        $from = DateUtils::addDate(date('Y-m-d'), -1, 'month', $format);
        $to = date($format, time());
        $date_range = $from . ' - ' . $to;
        return $date_range;
    }

    /**
     * Set Date Range
     */
    public static function setDateRange()
    {
        if (isset($_GET[self::GET_PARAM_DATE_RANGE]))
            self::$dateRange = $_GET[self::GET_PARAM_DATE_RANGE];
        if (empty(self::$dateRange))
            self::$dateRange = static::defaultDateRange();
    }

    /**
     * Get date range string
     * @return type
     */
    public static function getDateRange()
    {
        return self::$dateRange;
    }

    /**
     * Set graph type
     * @param type $value
     */
    public static function setGraphType($value = NULL)
    {
        if (isset($_GET[self::GET_PARAM_GRAPH_TYPE]))
            self::$graphType = $_GET[self::GET_PARAM_GRAPH_TYPE];
        else if (!empty($value))
            self::$graphType = $value;
        else
            self::$graphType = self::GRAPH_PIE;
        $graph_type = self::graphTypes();
        if (!array_key_exists(self::$graphType, $graph_type))
            self::$graphType = array_shift(array_keys($graph_type));
    }

    public static function getGraphType()
    {
        return self::$graphType;
    }

    /**
     *  Format daterange into from and to array values
     * @param type $format
     * @return type
     */
    public static function explodeDateRange($format = 'Y-m-d')
    {
        $date_range = explode('-', self::$dateRange);
        $from = Common::formatDate(trim($date_range[0]), $format);
        $to = isset($date_range[1]) ? Common::formatDate(trim($date_range[1]), $format) : NULL;
        return [
            'from' => trim($from),
            'to' => trim($to),
        ];
    }

    /**
     * Generate xvalues
     * @param type $max_label
     * @return array
     */
    public static function getXAxisParams($max_label = NULL)
    {
        $max_label = (int)$max_label;
        if (empty($max_label))
            $max_label = 12;
        $result = [];
        $date_range = self::explodeDateRange();
        $from = $date_range['from'];
        $to = $date_range['to'];
        $date_interval = Common::getDateDiff($from, $to);
        $days_interval = $date_interval->days;

        if ($days_interval <= self::$max_x_interval_day) {
            $x_interval = (int)round($days_interval / $max_label);
            $x_dates = Common::generateDateSpan($from, $to, 1, 'day');
            $result['date_type'] = self::DATE_TYPE_DAY;
            $result['dates'] = self::getXAxisDates($x_dates, 'M j, Y');
            $result['step'] = $x_interval;
        } else if ($days_interval > self::$max_x_interval_day && $days_interval <= self::$max_x_interval_month) {
            //assume each month is 30days
            $x_interval = (int)round(($days_interval / 30) / $max_label);
            $x_dates = Common::generateDateSpan($from, $to, $x_interval, 'month');
            $result['date_type'] = self::DATE_TYPE_MONTH;
            $result['dates'] = self::getXAxisDates($x_dates, 'M Y');
            $result['step'] = 1;
        } else {
            //assume each year is 365days
            $x_interval = (int)round(($days_interval / 365) / $max_label);
            $x_dates = Common::generateDateSpan($from, $to, $x_interval, 'year');
            $result['date_type'] = self::DATE_TYPE_YEAR;
            $result['dates'] = self::getXAxisDates($x_dates, 'Y');
            $result['step'] = 1;
        }
        $result['graph_type'] = self::$graphType;

        return $result;
    }

    private static function getXAxisDates($date_range_dates, $format)
    {
        $previous_formated = [];
        $x_axis_dates = [];
        foreach ($date_range_dates as $date) {
            $formated = Common::formatDate($date, $format);
            if (!in_array($formated, $previous_formated)) {
                array_push($x_axis_dates, [
                    'date' => $date,
                    'label' => $formated,
                ]);
            }
            $previous_formated[] = $formated;
        }
        return $x_axis_dates;
    }

    /**
     * Colors of the graph
     * @return type
     */
    public static function colors()
    {
        return [
            '#2f7ed8',
            '#0d233a',
            '#c92a9b',
            '#23eb55',
            '#910000',
            '#1aadce',
            '#ff0',
            '#492970',
            '#f28f43',
            '#77a1e5',
            '#c42525',
            '#c6c92a',
            '#434348',
            '#FDD01C',
            '#8bbc21',
        ];
    }
}