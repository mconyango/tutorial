<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_mpesa_api_credentials".
 *
 * @property integer $id
 * @property string $description
 * @property string $sp_id
 * @property string $password
 * @property string $service_id
 * @property string $initiator_identifier
 * @property string $initiator_password
 * @property string $organization_shortcode
 * @property integer $customer_id
 * @property integer $is_active
 * @property string $created_at
 * @property integer $created_by
 * @property integer $is_deleted
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class MpesaApiCredentials extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_mpesa_api_credentials}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'sp_id', 'password', 'service_id', 'initiator_identifier', 'initiator_password', 'organization_shortcode'], 'required'],
            [['customer_id', 'is_active'], 'integer'],
            [['description', 'initiator_identifier'], 'string', 'max' => 60],
            [['sp_id', 'service_id', 'organization_shortcode'], 'string', 'max' => 30],
            [['password', 'initiator_password'], 'string', 'max' => 128],
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
            'description' => Lang::t('Description'),
            'sp_id' => Lang::t('Sp ID'),
            'password' => Lang::t('Password'),
            'service_id' => Lang::t('Service ID'),
            'initiator_identifier' => Lang::t('Initiator Identifier'),
            'initiator_password' => Lang::t('Initiator Password'),
            'organization_shortcode' => Lang::t('Organization Shortcode'),
            'customer_id' => Lang::t('Customer ID'),
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
            ['sp_id', 'sp_id'],
            ['service_id', 'service_id'],
            'customer_id',
            'is_active',
        ];
    }
}
