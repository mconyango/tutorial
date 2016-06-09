<?php

namespace backend\modules\conf\models;

use common\models\ActiveRecord;
use Yii;

/**
 * This is the model class for table "conf_timezone".
 *
 * @property integer $id
 * @property string $name
 */
class Timezone extends ActiveRecord
{
    const DEFAULT_TIME_ZONE = 'Africa/Nairobi';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf_timezone';
    }

    /**
     * composes drop-down list data from a model using Html::listData function
     * @see CHtml::listData();
     * @param string $valueColumn
     * @param string $textColumn
     * @param boolean $tip
     * @param string $condition
     * @param array $params
     * @param array $options
     *
     *  <pre>
     *   array(
     *    "orderBy"=>""//String,
     *    "groupField"=>null//String could be anonymous function that gets the group field
     *    "extraColumns"=>[]// array : you must pass at least the grouping field if groupField is an anonymous function
     * )
     * </pre>
     *
     * @return array
     */
    public static function getListData($valueColumn = 'name', $textColumn = 'name', $tip = false, $condition = '', $params = [], $options = [])
    {
        return parent::getListData($valueColumn, $textColumn, $tip, $condition, $params, $options);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique', 'message' => '{value} already exists.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }


}
