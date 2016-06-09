<?php

namespace backend\modules\auth\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "auth_user_levels".
 *
 * @property integer $id
 * @property string $name
 * @property string $forbidden_items
 * @property integer $parent_id
 */
class UserLevels extends ActiveRecord implements ActiveSearchInterface
{

    use ActiveSearchTrait;

    const LEVEL_DEV = -1;
    const LEVEL_SUPER_ADMIN = 1;
    const LEVEL_ADMIN = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_user_levels}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['id', 'parent_id'], 'integer'],
            [['name'], 'string', 'max' => 60],
            [['id', 'name'], 'unique'],
            [['forbidden_items'], 'safe']
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
            'forbidden_items' => Lang::t('Forbidden Items'),
            'parent_id' => Lang::t('Parent'),
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
        ];
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (parent::beforeSave($insert)) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @return boolean whether the insertion or updating should continue.
     * If false, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        if ($this->id === self::LEVEL_DEV) {
            $this->parent_id = NULL;
            $this->forbidden_items = NULL;
        }
        if (!empty($this->forbidden_items))
            $this->forbidden_items = serialize($this->forbidden_items);

        return parent::beforeSave($insert);
    }

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        if (!empty($this->forbidden_items)) {
            $this->forbidden_items = unserialize($this->forbidden_items);
        }

        parent::afterFind();
    }


}
