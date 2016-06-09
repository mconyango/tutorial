<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "bank_branches".
 *
 * @property integer $id
 * @property integer $bank_id
 * @property string $branch_code
 * @property string $branch_name
 * @property integer $is_active
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $is_deleted
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class BankBranches extends ActiveRecord implements ActiveSearchInterface
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
        return '{{%bank_branches}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'branch_code', 'branch_name'], 'required'],
            [['bank_id', 'is_active'], 'integer'],
            [['branch_code', 'branch_name'], 'string', 'max' => 255],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bank_id' => Lang::t('Bank'),
            'branch_code' => Lang::t('Branch Code'),
            'branch_name' => Lang::t('Branch Name'),
            'is_active' => Lang::t('Active'),
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
            ['branch_code', 'branch_code'],
            ['branch_name', 'branch_name'],
            'bank_id',
            'is_active',
        ];
    }
}
