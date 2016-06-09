<?php

namespace backend\modules\conf\models;

use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "conf_currency".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property integer $is_active
 * @property integer $decimal_places
 * @property string $decimal_separator
 * @property string $thousands_separator
 * @property integer $add_space
 * @property string $created_at
 * @property integer $created_by
 */
class Currency extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * Initializes the object.
     * This method is called at the end of the constructor.
     * The default implementation will trigger an [[EVENT_INIT]] event.
     * If you override this method, make sure you call the parent implementation at the end
     * to ensure triggering of the event.
     */
    public function init()
    {
        if (is_null($this->is_active))
            $this->is_active = 1;
        parent::init();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_currency}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'symbol'], 'required'],
            [['is_active', 'decimal_places', 'add_space'], 'integer'],
            [['id'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 128],
            [['symbol'], 'string', 'max' => 60],
            [['decimal_separator', 'thousands_separator'], 'string', 'max' => 1],
            [['id', 'name', 'symbol'], 'unique'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('Code'),
            'name' => Lang::t('Name'),
            'symbol' => Lang::t('Symbol'),
            'is_active' => Lang::t('Active'),
            'decimal_places' => Lang::t('Decimal Places'),
            'decimal_separator' => Lang::t('Decimal Separator'),
            'thousands_separator' => Lang::t('Thousands Separator'),
        ];
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
            ['id', 'id'],
            ['name', 'name'],
            'is_active',
        ];
    }


    /**
     * Get currency symbol
     * @param string $id
     * @return string
     */
    public static function getSymbol($id)
    {
        $symbol = static::getFieldByPk($id, 'symbol');
        return !empty($symbol) ? $symbol : $id;
    }

    /**
     * Get currency
     * @param string $column
     * @param string $currency_id
     * @return string
     */
    public static function getCurrency($column = 'id', $currency_id = null)
    {
        if (empty($currency_id))
            $currency_id = Yii::$app->setting->get(Constants::SECTION_SYSTEM, Constants::KEY_CURRENCY,'USD');
        if ($column === 'id')
            return $currency_id;
        return static::getFieldByPk($currency_id, $column);
    }

    /**
     * Format money
     * @param double $amount
     * @param string $currency_id
     * @param bool $show_currency
     * @param bool $show_currency_symbol
     * @param bool $prefix_currency
     * @return string formatted amount
     */
    public static function formatMoney($amount, $currency_id = null, $show_currency = true, $show_currency_symbol = true, $prefix_currency = true)
    {
        if (empty($currency_id))
            $currency_id = static::getCurrency('id');
        $currency = static::getOneRow(['decimal_places', 'decimal_separator', 'thousands_separator'], ['id' => $currency_id]);
        $decimal_places = ArrayHelper::getValue($currency, 'decimal_places', '2');
        $decimal_separator = ArrayHelper::getValue($currency, 'decimal_separator', '.');
        $thousands_separator = ArrayHelper::getValue($currency, 'thousands_separator', ',');
        $amount = number_format((float)$amount, $decimal_places, $decimal_separator, $thousands_separator);
        if (!$show_currency)
            return $amount;

        $currency_name = static::getCurrency($column = $show_currency_symbol ? 'symbol' : 'id', $currency_id);
        $template = $prefix_currency ? '{currency}{amount}' : '{amount}{currency}';
        return strtr($template, [
            '{currency}' => $currency_name,
            '{amount}' => $amount,
        ]);
    }

    /**
     * Decimal places options
     */
    public static function decimalPlacesOptions()
    {
        return \common\helpers\Utils::generateIntegersList(0, 5);
    }

    /**
     * @return array
     */
    public static function decimalSeparatorOptions()
    {
        return [
            '.' => Lang::t('DOT'),
            ',' => Lang::t('COMMA'),
        ];
    }

    /**
     * @return array
     */
    public static function thousandsSeparatorOptions()
    {
        return [
            ',' => Lang::t('COMMA'),
            ' ' => Lang::t('SPACE'),
            '.' => Lang::t('DOT'),
        ];
    }

    /**
     * Decode separator
     * @param string $separator
     * @return string
     */
    public static function decodeSeparator($separator)
    {
        switch ($separator) {
            case '.':
                return Lang::t('DOT');
            case ',':
                return Lang::t('COMMA');
            default :
                return Lang::t('SPACE');
        }
    }
}