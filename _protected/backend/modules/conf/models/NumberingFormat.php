<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "conf_numbering_format".
 *
 * @property string $id
 * @property string $name
 * @property integer $next_number
 * @property integer $min_digits
 * @property string $prefix
 * @property string $suffix
 * @property string $preview
 * @property string $created_at
 * @property integer $created_by
 */
class NumberingFormat extends ActiveRecord implements ActiveSearchInterface
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
        if (is_null($this->min_digits))
            $this->min_digits = 3;
        if (is_null($this->next_number))
            $this->next_number = 1;
        parent::init();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf_numbering_format';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['next_number', 'min_digits'], 'integer'],
            [['id'], 'string', 'max' => 60],
            [['name'], 'string', 'max' => 255],
            [['prefix', 'suffix'], 'string', 'max' => 5],
            [['preview'], 'string', 'max' => 128],
            [['id', 'name'], 'unique'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'type' => Lang::t('Type'),
            'description' => Lang::t('Description'),
            'next_number' => Lang::t('Next Number'),
            'min_digits' => Lang::t('Minimum Digits'),
            'prefix' => Lang::t('Prefix'),
            'suffix' => Lang::t('Suffix'),
            'preview' => Lang::t('Preview'),
        ];
    }

    /**
     * Get next formatted number
     * @param string $id
     * @param boolean $increment_next_number
     * @return string $formatted_number
     */
    public static function getNextFormattedNumber($id, $increment_next_number = true)
    {
        $format = static::getOneRow('*', ['id' => $id]);
        $next_number = ArrayHelper::getValue($format, 'next_number', 1);
        $min_digits = ArrayHelper::getValue($format, 'min_digits', 3);
        $prefix = ArrayHelper::getValue($format, 'prefix', '');
        $suffix = ArrayHelper::getValue($format, 'suffix', '');
        $template = '{{prefix}}{{number}}{{suffix}}';

        $number = str_pad($next_number, $min_digits, "0", STR_PAD_LEFT);
        if (!empty($format) && $increment_next_number) {
            $next_number++;
            Yii::$app->db->createCommand()
                ->update(static::tableName(), ['next_number' => $next_number], ['id' => $format['id']])
                ->execute();
        }
        return strtr($template, [
            '{{prefix}}' => $prefix,
            '{{number}}' => $number,
            '{{suffix}}' => $suffix,
        ]);
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
        ];
    }
}
