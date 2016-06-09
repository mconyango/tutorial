<?php

namespace backend\modules\auth\models;

use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "auth_audit_trail".
 *
 * @property integer $id
 * @property integer $action
 * @property string $action_description
 * @property string $url
 * @property string $ip_address
 * @property integer $user_id
 * @property string $data_before_action
 * @property string $data_after_action
 * @property string $fields_changed
 * @property string $created_at
 */
class AuditTrail extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    //actions
    const ACTION_VIEW = 1;
    const ACTION_CREATE = 2;
    const ACTION_UPDATE = 3;
    const ACTION_DELETE = 4;

    public $enableAuditTrail = false;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_audit_trail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'action_description', 'url', 'user_id'], 'required'],
            [['action', 'user_id'], 'integer'],
            [['data_before_action', 'data_after_action'], 'string'],
            [['created_at'], 'safe'],
            [['action_description', 'url', 'fields_changed'], 'string', 'max' => 1000],
            [['ip_address'], 'string', 'max' => 30],
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
            'action' => Lang::t('Action'),
            'action_description' => Lang::t('Action Description'),
            'url' => Lang::t('URL'),
            'ip_address' => Lang::t('Ip Address'),
            'user_id' => Lang::t('User'),
            'data_before_action' => Lang::t('Data Before Action'),
            'data_after_action' => Lang::t('Data After Action'),
            'fields_changed' => Lang::t('Fields Changed'),
            'created_at' => Lang::t('Time'),
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
            ['ip_address', 'ip_address'],
            'action',
            'user_id',
        ];
    }

    /**
     * @param $action
     * @return string
     */
    public static function decodeAction($action)
    {
        $decoded = $action;
        switch ($action) {
            case self::ACTION_VIEW:
                $decoded = 'View';
                break;
            case self::ACTION_CREATE:
                $decoded = 'Create';
                break;
            case self::ACTION_UPDATE:
                $decoded = 'Update';
                break;
            case self::ACTION_DELETE:
                $decoded = 'Delete';
                break;
        }
        return $decoded;
    }

    /**
     * @param bool $tip
     * @return array
     */
    public static function actionOptions($tip = false)
    {
        $options = [];
        if ($tip) {
            $options[''] = '';
        }
        $options[self::ACTION_VIEW] = static::decodeAction(self::ACTION_VIEW);
        $options[self::ACTION_CREATE] = static::decodeAction(self::ACTION_CREATE);
        $options[self::ACTION_UPDATE] = static::decodeAction(self::ACTION_UPDATE);
        $options[self::ACTION_DELETE] = static::decodeAction(self::ACTION_DELETE);

        return $options;
    }

    /**
     * @param ActiveRecord $model
     * @param string $action
     * @return bool
     */
    public static function addAuditTrail($model, $action)
    {
        $changedAttributes = $model->changedAttributes;
        if (!Utils::isWebApp())
            return false;
        if (Yii::$app->user->isGuest)
            return false;

        if ($action === self::ACTION_UPDATE && empty($model->changedAttributes)) {
            return false;
        }

        if ($action === self::ACTION_CREATE || $action === self::ACTION_DELETE) {
            $changedAttributes = $model->attributes;
        }

        $protectedFields = [
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'is_deleted',
            'deleted_at',
            'deleted_by',
            'deactivated_by',
            'deactivated_at',
        ];
        foreach ($changedAttributes as $k => $v) {
            if (in_array($k, $protectedFields)) {
                unset($changedAttributes[$k]);
            }
        }

        $audit = new AuditTrail();
        $audit->action = $action;
        $audit->ip_address = Yii::$app->request->getUserIP();
        $audit->url = Yii::$app->request->getAbsoluteUrl();
        $audit->user_id = Yii::$app->user->id;

        if (!empty($changedAttributes)) {
            if ($action === self::ACTION_UPDATE || $action === self::ACTION_DELETE) {
                $audit->data_before_action = serialize($changedAttributes);
            }
            if ($action === self::ACTION_CREATE || $action === self::ACTION_UPDATE) {
                $audit->fields_changed = serialize(array_keys($changedAttributes));
                $audit->data_after_action = serialize(static::getDataAfterAction($model, $changedAttributes));
            }
        }

        $audit->action_description = static::getActionDescription($model, $action);

        $audit->save();
    }

    /**
     * @param ActiveRecord $model
     * @param array $changedAttributes
     * @return array
     */
    private static function getDataAfterAction($model, $changedAttributes)
    {
        $dataAfter = [];

        foreach ($changedAttributes as $k => $v) {
            $dataAfter[$k] = $model->{$k};
        }

        return $dataAfter;
    }

    /**
     * @param ActiveRecord $model
     * @param string $action
     * @return null|string
     */
    private static function getActionDescription($model, $action)
    {
        $actionDescription = null;
        switch ($action) {
            case self::ACTION_CREATE:
                $actionDescription = $model->actionCreateDescriptionTemplate;
                break;
            case self::ACTION_UPDATE:
                $actionDescription = $model->actionUpdateDescriptionTemplate;
                break;
            case self::ACTION_DELETE:
                $actionDescription = $model->actionDeleteDescriptionTemplate;
                break;

        }

        $actionDescription = $actionDescription = strtr($actionDescription, ['{{table}}' => $model->tableName(), '{{id}}' => $model->primaryKey]);

        return $actionDescription;
    }

}
