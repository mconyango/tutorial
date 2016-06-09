<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_location".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $country
 * @property string $description
 * @property integer $is_active
 * @property string $created_at
 * @property integer $created_by
 * @property integer $is_deleted
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class Location extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_location}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['is_active'], 'integer'],
            [['name', 'country'], 'string', 'max' => 128],
            [['description'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 20],
            [['code'], 'unique'],
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
            'name' => Lang::t('Name'),
            'code' => Lang::t('Code'),
            'country' => Lang::t('Country'),
            'description' => Lang::t('Description'),
            'is_active' => Lang::t('Active'),
            'created_at' => Lang::t('Created At'),
            'created_by' => Lang::t('Created By'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function searchParams()
    {
        return [
            ['name', 'name'],
            ['code', 'code'],
            'is_active',
        ];
    }
}
