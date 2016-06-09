<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "bank_names".
 *
 * @property integer $id
 * @property string $bank_code
 * @property string $bank_name
 * @property integer $is_active
 * @property string $mpesa_paybill
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $is_deleted
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class BankNames extends ActiveRecord implements ActiveSearchInterface
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
        return '{{%bank_names}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_code', 'bank_name'], 'required'],
            [['is_active'], 'integer'],
            [['bank_code'], 'string', 'max' => 3],
            [['bank_name', 'mpesa_paybill'], 'string', 'max' => 255],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bank_code' => Lang::t('Bank Code'),
            'bank_name' => Lang::t('Bank Name'),
            'is_active' => Lang::t('Active'),
            'mpesa_paybill' => Lang::t('M-PESA Paybill'),
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
            ['bank_code', 'bank_code'],
            ['bank_name', 'bank_name'],
            'is_active',
        ];
    }

    /**
     * @param string $code
     * @param string $field
     * @return string
     */
    public static function getFieldByCode($code,$field)
    {
        return static::getScalar($field,['bank_code'=>$code]);
    }
}
