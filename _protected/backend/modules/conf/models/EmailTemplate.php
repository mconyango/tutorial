<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_email_template".
 *
 * @property string $id
 * @property string $name
 * @property string $subject
 * @property string $body
 * @property string $sender
 * @property string $comments
 * @property string $created_at
 * @property integer $created_by
 */
class EmailTemplate extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf_email_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'subject', 'body', 'sender'], 'required'],
            [['body'], 'string'],
            [['sender'], 'email'],
            [['id', 'name'], 'string', 'max' => 128],
            [['id'], 'unique'],
            [['subject', 'sender', 'comments'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'subject' => Lang::t('Subject'),
            'body' => Lang::t('Body'),
            'sender' => Lang::t('From'),
            'name' => Lang::t('Name'),
            'comments' => Lang::t('Comments'),
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
}
