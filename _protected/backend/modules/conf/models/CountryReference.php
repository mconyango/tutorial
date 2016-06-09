<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_country_reference".
 *
 * @property integer $id
 * @property string $name
 * @property string $country_code
 * @property string $name_a
 * @property string $flag
 * @property string $date_created
 */
class CountryReference extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_country_reference}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'flag'], 'string', 'max' => 128],
            [['country_code'], 'string', 'max' => 4],
            [['name_a'], 'string', 'max' => 10],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Lang::t('Country'),
            'country_code' => Lang::t('Country Code'),
            'name_a' => Lang::t('Abbreviation'),
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
            ['name', 'name'],
            ['name_a', 'name_a'],
            ['country_code', 'country_code'],
        ];
    }
}
