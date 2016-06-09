<?php

namespace backend\modules\auth\models;

use common\models\ActiveRecord;
use Yii;

/**
 * This is the model class for table "auth_permission".
 *
 * @property integer $id
 * @property integer $role_id
 * @property string $resource_id
 * @property integer $can_view
 * @property integer $can_create
 * @property integer $can_update
 * @property integer $can_delete
 * @property integer $can_execute
 */
class Permission extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_permission}}';
    }

    /**
     *
     * @param string $resource_id
     * @param string $role_id
     * @param string $action
     * @param string $default
     * @return bool|mixed|null
     */
    public static function getValue($resource_id, $role_id, $action, $default = NULL)
    {
        $model = static::findOne(['role_id' => $role_id, 'resource_id' => $resource_id]);
        if ($model !== NULL)
            return $model->$action;
        if ($default !== NULL)
            return $default;
        return FALSE;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'resource_id'], 'required'],
            [['role_id', 'can_view', 'can_create', 'can_update', 'can_delete', 'can_execute'], 'integer'],
            [['resource_id'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role',
            'resource_id' => 'Resource',
            'can_view' => 'Can View',
            'can_create' => 'Can Create',
            'can_update' => 'Can Update',
            'can_delete' => 'Can Delete',
            'can_execute' => 'Can Execute',
        ];
    }

    /**
     *
     * @param int $resource_id
     * @param int $role_id
     * @param array $values
     * @return bool
     */
    public function setPermission($resource_id, $role_id, $values)
    {
        $model = $this->findOne(['role_id' => $role_id, 'resource_id' => $resource_id]);
        if ($model === NULL) {
            $model = new Permission();
            $model->resource_id = $resource_id;
            $model->role_id = $role_id;
        }

        foreach ($values as $key => $val) {
            $model->$key = (int)$val;
        }
        if ($model->save())
            return TRUE;
        return FALSE;
    }
}
