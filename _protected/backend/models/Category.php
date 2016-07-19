<?php

namespace backend\models;

use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Subcategory[] $subcategories
 */
class Category extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 128],
            [[self::SEARCH_FIELD],'safe','on'=>self::SCENARIO_SEARCH],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubcategories()
    {
        return $this->hasMany(Subcategory::className(), ['category_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function searchParams()
    {
        return [
            ['name','name'],
        ];
    }


}
