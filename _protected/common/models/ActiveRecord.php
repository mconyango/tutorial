<?php

namespace common\models;

use backend\modules\auth\models\AuditTrail;
use common\helpers\DateUtils;
use common\helpers\DbUtils;
use common\helpers\Lang;
use common\helpers\Url;
use common\helpers\Utils;
use Yii;
use yii\db\ActiveRecord as AR;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * ActiveRecord is the customized base activeRecord class.
 * All Model classes for this application should extend from this base class.
 * @author Fred <mconyango@gmail.com>
 * Created on 2015-11-17
 */
abstract class ActiveRecord extends AR
{
    //used by getStats() function
    const STATS_TODAY = '1';
    const STATS_THIS_WEEK = '2';
    const STATS_LAST_WEEK = '3';
    const STATS_THIS_MONTH = '4';
    const STATS_LAST_MONTH = '5';
    const STATS_THIS_YEAR = '6';
    const STATS_LAST_YEAR = '7';
    const STATS_ALL_TIME = '8';
    const STATS_DATE_RANGE = '9';
    //special constants
    const SCENARIO_SEARCH = 'search';
    const SEARCH_FIELD = '_searchField';

    //audit trail
    /**
     * @var string
     */
    public $actionCreateDescriptionTemplate = 'Created a resource. Table={{table}}, id={{id}}';
    /**
     * @var string
     */
    public $actionUpdateDescriptionTemplate = 'Updated a resource. Table={{table}}, id={{id}}';
    /**
     * @var string
     */
    public $actionDeleteDescriptionTemplate = 'Deleted a resource. Table={{table}}, id={{id}}';
    /**
     * @var bool
     */
    public $enableAuditTrail = true;
    /**
     * @var array
     */
    public $changedAttributes;

    /**
     * Set empty values to NULL b4 save
     * @var boolean
     */
    public $setEmptyValuesNullBeforeSave = true;

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     *
     * ~~~
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ~~~
     *
     * Note that a behavior class must extend from [[Behavior]]. Behavior names can be strings
     * or integers. If the former, they uniquely identify the behaviors. If the latter, the corresponding
     * behaviors are anonymous and their properties and methods will NOT be made available via the component
     * (however, the behaviors can still respond to the component's events).
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     *
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    /**
     * Get short classname (without the namespace)
     * @return string
     */
    public static function shortClassName()
    {
        $reflect = new \ReflectionClass(static::className());
        return $reflect->getShortName();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->setEmptyValuesNullBeforeSave) {
                $this->setEmptyValuesNull();
            }

            // insertion operation, when insert===true. otherwise, its an update operation
            if ($insert) {
                //created_by
                if (Utils::isWebApp() && $this->hasAttribute('created_by') && empty($this->created_by) && !Yii::$app->user->isGuest) {
                    $this->created_by = Yii::$app->user->id;
                }
                //created_at
                if ($this->hasAttribute('created_at') && empty($this->created_at)) {
                    $this->created_at = DateUtils::mysqlTimestamp();
                }
                // update operation
                if ($this->hasAttribute('updated_at') && empty($this->updated_at)) {
                    $this->updated_at = DateUtils::mysqlTimestamp();
                }
            } else {
                //updated_by
                if (Utils::isWebApp() && $this->hasAttribute('updated_by') && empty($this->updated_by) && !Yii::$app->user->isGuest) {
                    $this->updated_by = Yii::$app->user->id;
                }
                // update operation
                if ($this->hasAttribute('updated_at') && empty($this->updated_at)) {
                    $this->updated_at = DateUtils::mysqlTimestamp();
                }
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->changedAttributes = $changedAttributes;
        if ($this->enableAuditTrail) {
            $action = $insert ? AuditTrail::ACTION_CREATE : AuditTrail::ACTION_UPDATE;
            AuditTrail::addAuditTrail($this, $action);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        if ($this->enableAuditTrail) {
            AuditTrail::addAuditTrail($this, AuditTrail::ACTION_DELETE);
        }
        parent::afterDelete();
    }

    /**
     * Load model function. With an option to throw the 404 exception
     * @param string|int $condition , or primary key value
     * @param bool $throwException
     * @return ActiveRecord $model
     * @throws NotFoundHttpException
     */
    public static function loadModel($condition, $throwException = true)
    {
        $model = static::findOne($condition);
        if ($model === null) {

            if ($throwException)
                throw new NotFoundHttpException('The requested resource was not found.');
        }
        return $model;
    }

    /**
     * Get a scalar value from the table
     * @param string|array $column
     * @param string|array $condition
     * @param array $params
     * @return string
     */
    public static function getScalar($column, $condition = '', $params = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->select($column)
            ->from(static::tableName())
            ->where($condition, $params)
            ->limit(1)
            ->scalar(static::getDb());
    }

    /**
     * Get a row of a table
     * @param string|array $columns
     * @param string|array $condition
     * @param array $params
     * @return mixed
     */
    public static function getOneRow($columns = '*', $condition = '', $params = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->select($columns)
            ->from(static::tableName())
            ->where($condition, $params)
            ->limit(1)
            ->one(static::getDb());
    }

    /**
     * get column data of a table
     * @param string|array $column
     * @param string|array $condition
     * @param array $params
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @param array $options
     * @return mixed
     */
    public static function getColumnData($column, $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->select($column)
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['orderBy']))
            $command->orderBy($options['orderBy']);
        if (!empty($options['limit']))
            $command->limit($options['limit']);
        if (!empty($options['offset']))
            $command->offset($options['offset']);

        return $command->column(static::getDb());
    }

    /**
     * Gets a particular field of a table
     * @param mixed $id The Primary Key of the model table
     * @param string $column The field to be returned
     * @return boolean Returns the field if found else returns false
     */
    public static function getFieldByPk($id, $column)
    {
        if (empty($id))
            return null;

        $primary_key = static::getPrimaryKeyColumn();

        return static::getScalar($column, [$primary_key => $id]);
    }

    /**
     * Get the row counts
     * @param string|array $condition
     * @param array $params
     * @return int $count
     */
    public static function getCount($condition = '', $params = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->from(static::tableName())
            ->where($condition, $params)
            ->count('*', static::getDb());
    }

    /**
     * Get the sum of rows
     * @param string $column Field to be summed
     * @param string|array $condition
     * @param array $params
     * @param array $options
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @return double
     */
    public static function getSum($column, $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['groupBy']))
            $command->groupBy($options['groupBy']);
        return $command->sum($column, static::getDb());
    }

    /**
     * Get rowset using the query builder e.g Yii::app()->db->createCommand()
     * @param mixed $columns
     * @param mixed $condition
     * @param array $params
     * @param array $options
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @return array $data
     */
    public static function getData($columns = ['*'], $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->select($columns)
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['orderBy']))
            $command->orderBy($options['orderBy']);
        if (!empty($options['groupBy']))
            $command->groupBy($options['groupBy']);
        if (!empty($options['limit']))
            $command->limit($options['limit']);
        if (!empty($options['offset']))
            $command->offset($options['offset']);
        return $command->all(static::getDb());
    }

    /**
     * @param array|string $condition
     * @param array $params
     * @return bool
     */
    public static function exists($condition, $params = [])
    {
        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return static::find()
            ->where($condition, $params)
            ->exists(static::getDb());
    }

    /**
     * Gets the primary key column name of a table
     * @return string primary key column name
     */
    public static function getPrimaryKeyColumn()
    {
        $primaryKey = static::primaryKey();
        return $primaryKey[0];
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
    public static function getListData($valueColumn = 'id', $textColumn = 'name', $tip = false, $condition = '', $params = [], $options = [])
    {
        $valueFieldAlias = 'id';
        $textFieldAlias = 'name';
        $columns = [
            "[[{$valueColumn}]] as [[{$valueFieldAlias}]]",
            !strpos($textColumn, '(') ? "[[{$textColumn}]] as [[{$textFieldAlias}]]" : "{$textColumn} as [[{$textFieldAlias}]]",
        ];
        if (!empty($options['extraColumns'])) {
            foreach ($options['extraColumns'] as $c) {
                $columns[] = "[[{$c}]]";
            }

        }
        $options['orderBy'] = ArrayHelper::getValue($options, 'orderBy', $textFieldAlias);

        list($condition, $params) = static::appendIDefaultConditions($condition, $params, ['is_active' => 1, 'is_deleted' => 0]);

        $data = static::getData($columns, $condition, $params, $options);
        if ($tip !== false && null !== $tip) {
            $tip = $tip === true ? "[select one]" : $tip;
            $first_row = [$valueFieldAlias => "", $textFieldAlias => $tip];
            if (!empty($options['extraColumns'])) {
                foreach ($options['extraColumns'] as $column) {
                    $first_row[$column] = '';
                }
            }

            $data = array_merge([$first_row], $data);
        }

        $groupField = ArrayHelper::getValue($options, 'groupField', null);

        return ArrayHelper::map($data, $valueFieldAlias, $textFieldAlias, $groupField);
    }

    /**
     * Gets the next Integer  ID for non-auto_increment integer keys
     * @param string $column the id column. default is the primary key column
     * @param int $start_from start ID
     * @return int the next integer id
     */
    public static function getNextIntegerID($column = NULL, $start_from = 0)
    {
        if (empty($column))
            $column = static::getPrimaryKeyColumn();

        $max_id = (new Query())
            ->from(static::tableName())
            ->max('[[' . $column . ']]', static::getDb());

        if (empty($max_id))
            $max_id = $start_from;
        return $max_id + 1;
    }

    /**
     * Insert multiple records to the db
     * Note: No validation done here
     * ```php
     *   $rows = array(
     *   array('tom',30),
     *   array('Fred',28),
     * );
     * ```
     * @param array $rows
     * @param string|null $table
     * @param Connection $db
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function insertMultiple($rows, $table = null, $db = null)
    {
        if (empty($rows))
            return false;
        if (empty($table))
            $table = static::tableName();
        $columns = array_keys(current($rows));
        if (null === $db) {
            $db = Yii::$app->db;
        }
        return $db->createCommand()->batchInsert($table, $columns, $rows)->execute();
    }

    /**
     * Set empty values to NULL b4 insert/update
     */
    protected function setEmptyValuesNull()
    {
        foreach ($this->attributes as $k => $v) {
            if (is_string($v) && trim($v) === "") {
                $this->{$k} = null;
            }
        }
    }

    /**
     * @param $id
     * @param string|null $primaryKeyField
     * @param bool $permanent
     * @return bool
     * @throws \Exception
     */
    public static function softDelete($id, $primaryKeyField = null, $permanent = false)
    {
        $field = 'is_deleted';
        $value = 1;
        if (!empty($primaryKeyField))
            $model = static::findOne([$primaryKeyField => $id]);
        else
            $model = static::findOne($id);
        if ($model === null)
            return false;

        if ($permanent || !$model->hasAttribute($field)) {
            return $model->delete();
        } else {
            $attributes = [
                $field => $value,
            ];

            if ($model->hasAttribute('deleted_at'))
                $attributes['deleted_at'] = DateUtils::mysqlTimestamp();
            if ($model->hasAttribute('deleted_by'))
                $attributes['deleted_by'] = Yii::$app->user->id;

            if ($model->updateAll($attributes, [static::getPrimaryKeyColumn() => $id])) {
                $model->afterDelete();
                return true;
            }
            return false;
        }
    }

    /**
     * @param string $condition
     * @param array $params
     * @param array $columns
     * @return array
     */
    public static function appendIDefaultConditions($condition = '', $params = [], $columns = ['is_deleted' => 0])
    {
        /* @var $model ActiveRecord */
        $class_name = static::className();
        $model = new $class_name();
        foreach ($columns as $k => $v) {
            if ($model->hasAttribute($k)) {
                list($condition, $params) = DbUtils::appendCondition($k, $v, $condition, $params);
            }
        }
        return [$condition, $params];
    }

    /**
     * Get stats
     * @param string $durationType e.g today,this_week,this_month,this_year, defaults to null (all time)
     * @param string $condition
     * @param array $params
     * @param mixed $sum if false then the count is return else returns the sum of the $sum field: defaults to FALSE
     * @param string $dateField The date field of the table to be queried for duration stats. defaults to "date_created"
     * @param string $from date_range from
     * @param string $to date_range to
     *
     * @return integer count or sum
     */
    public static function getStats($durationType, $condition = '', $params = [], $sum = false, $dateField = 'created_at', $from = null, $to = null)
    {
        $today = date('Y-m-d');
        $this_month = DateUtils::formatDate($today, 'm');
        $this_year = DateUtils::formatDate($today, 'Y');

        switch ($durationType) {
            case self::STATS_TODAY:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castDATE($dateField, static::getDb()), $today, $condition, $params);
                break;
            case self::STATS_THIS_WEEK:
                list($condition, $params) = DbUtils::YEARWEEKCondition($dateField, $today, static::getDb(), $condition, $params);
                break;
            case self::STATS_LAST_WEEK:
                $date = DateUtils::addDate($today, '-7', 'day');
                list($condition, $params) = DbUtils::YEARWEEKCondition($dateField, $date, static::getDb(), $condition, $params);
                break;
            case self::STATS_THIS_MONTH:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $this_year, $condition, $params);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castMONTH($dateField, static::getDb()), $this_month, $condition, $params);
                break;
            case self::STATS_LAST_MONTH:
                $date = DateUtils::addDate($today, '-1', 'month');
                $year = DateUtils::formatDate($date, 'Y');
                $month = DateUtils::formatDate($date, 'm');
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $year, $condition, $params);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castMONTH($dateField, static::getDb()), $month, $condition, $params);
                break;
            case self::STATS_THIS_YEAR:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $this_year, $condition, $params);
                break;
            case self::STATS_LAST_YEAR:
                $year = DateUtils::formatDate(DateUtils::addDate($today, '-1', 'year'), 'Y');
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $year, $condition, $params);
                break;
        }

        if (!empty($from) && !empty($to)) {
            if (!empty($condition))
                $condition .= ' AND ';
            $casted_date = DbUtils::castDATE($dateField, static::getDb());
            $condition .= $casted_date . '>=:from AND ' . $casted_date . '<=:to)';
            $params[':from'] = $from;
            $params[':to'] = $to;
        }

        if ($sum)
            return static::getSum($sum, $condition, $params);
        else
            return static::getCount($condition, $params);
    }

    /**
     * Performs simple ajax save
     * @param string $view
     * @param string $redirect_route
     * @param array $redirect_params
     * @param string|null $success_msg
     * @return bool|string
     */
    public function simpleAjaxSave($view = '_form', $redirect_route = 'index', $redirect_params = [], $success_msg = null)
    {
        if (empty($success_msg))
            $success_msg = Lang::t('SUCCESS_MESSAGE');

        if ($this->load(Yii::$app->request->post())) {
            if ($this->save()) {
                if ($redirect_route === 'index' || $redirect_route === 'create')
                    $redirect_url = Url::to(array_merge([$redirect_route], (array)$redirect_params));
                else {
                    $primary_key_field = static::getPrimaryKeyColumn();
                    $redirect_url = Url::to(array_merge([$redirect_route, 'id' => $this->{$primary_key_field}], (array)$redirect_params));
                }
                return Json::encode(['success' => true, 'message' => $success_msg, 'redirectUrl' => Url::getReturnUrl($redirect_url)]);
            } else {
                return Json::encode(['success' => false, 'message' => $this->getErrors()]);
            }
        }

        return Yii::$app->controller->renderAjax($view, [
            'model' => $this,
        ]);
    }

    /**
     * Performs simple non ajax save
     * @param string $view
     * @param string $redirect_action
     * @param array $redirect_params
     * @param string|null $success_msg
     *
     * @return string|\yii\web\Response
     */
    public function simpleNonAjaxSave($view = 'view', $redirect_action = 'view', $redirect_params = [], $success_msg = null)
    {
        if (empty($success_msg))
            $success_msg = Lang::t('SUCCESS_MESSAGE');

        if ($this->load(Yii::$app->request->post())) {
            if ($this->save()) {
                $primary_key_field = static::getPrimaryKeyColumn();
                if ($redirect_action === 'index' || $redirect_action === 'create')
                    $redirect_url = Url::to(array_merge([$redirect_action], (array)$redirect_params));
                else {
                    $redirect_url = Url::to(array_merge([$redirect_action, 'id' => $this->{$primary_key_field}], (array)$redirect_params));
                }
                Yii::$app->session->setFlash('success', $success_msg);

                return Yii::$app->controller->redirect($redirect_url);

            }
        }

        return Yii::$app->controller->render($view, [
            'model' => $this,
        ]);
    }

    /**
     * Performs simple non-ajax save
     * @param string $view
     * @param string $redirect_action
     * @param string|null $success_msg
     * @return mixed
     */
    public function simpleSave($view = 'create', $redirect_action = 'index', $success_msg = null)
    {
        if (empty($success_msg))
            $success_msg = Lang::t('SUCCESS_MESSAGE');

        if ($this->load(Yii::$app->request->post()) && $this->save()) {
            Yii::$app->session->setFlash('success', $success_msg);
            if ($redirect_action === 'index' || $redirect_action === 'create') {
                $redirect_url = Url::to([$redirect_action]);
            } else {
                $primary_key_field = static::getPrimaryKeyColumn();
                $redirect_url = Url::to([$redirect_action, 'id' => $this->{$primary_key_field}]);
            }

            return Yii::$app->controller->redirect(Url::getReturnUrl($redirect_url));
        }

        return Yii::$app->controller->render($view, [
            'model' => $this,
        ]);
    }

    /**
     * Sets default column values as defined in the db
     * (Yii2 dropped support for setting model attributes as per the defaults set in the db)
     * @param array $columns key=>value where key is the column name and the value is the column value.
     */
    public function setDefaults($columns)
    {
        if ($this->getScenario() !== self::SCENARIO_SEARCH) {
            foreach ($columns as $k => $v) {
                if ($this->hasAttribute($k) && is_null($this->{$k}))
                    $this->{$k} = $v;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information or unnecessary information
        //is_deleted,deleted_at,deleted_by

        $excluded_fields = [
            'is_deleted',
            'deleted_at',
            'deleted_by',
            'deactivated_at',
            'deactivated_by',
        ];

        foreach ($excluded_fields as $f) {
            if ($this->hasAttribute($f)) {
                unset($fields[$f]);
            }
        }

        return $fields;
    }


}
