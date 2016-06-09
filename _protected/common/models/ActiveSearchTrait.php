<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 1:56 PM
 */

namespace common\models;


use backend\modules\conf\Constants;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\helpers\ArrayHelper;

trait ActiveSearchTrait
{

    /**
     *
     * ```php
     *   [
     *     'defaultOrder'=>['name'=>SORT_ASC],
     *     'condition'=>"",// string or array
     *     'params'=>[],
     *     'pageSize'=>30,
     *     'enablePagination'=>true,
     *   ]
     * ```
     * @var array
     */
    private $_searchOptions;

    /**
     * Creates data provider instance with search query applied
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        /* @var $this \common\models\ActiveRecord */
        $defaultOrder = ArrayHelper::getValue($this->_searchOptions, 'defaultOrder', []);
        $condition = ArrayHelper::getValue($this->_searchOptions, 'condition', '');
        $params = ArrayHelper::getValue($this->_searchOptions, 'params', []);
        $pageSize = ArrayHelper::getValue($this->_searchOptions, 'pageSize', Yii::$app->setting->get(Constants::SECTION_SYSTEM, Constants::KEY_ITEMS_PER_PAGE, 30));
        $enablePagination = ArrayHelper::getValue($this->_searchOptions, 'enablePagination', true);
        $query = static::find()->where($condition, $params);

        if ($enablePagination) {
            $pagination = [
                'pageSize' => $pageSize,
            ];
        } else {
            $pagination = false;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => $pagination,
            'sort' => new Sort([
                'defaultOrder' => $defaultOrder,
            ])
        ]);

        $this->load(Yii::$app->request->queryParams);

        foreach ($this->searchParams() as $filter) {
            if (!is_array($filter)) {
                $query->andFilterWhere([$filter => $this->{$filter}]);
            } else {
                $operator = !empty($filter[3]) ? $filter[3] : 'like';
                if (!empty($filter[2]) && strtolower($filter[2]) === 'or') {
                    $query->orFilterWhere([$operator, $filter[0], $this->{$filter[1]}]);

                } else {
                    $query->andFilterWhere([$operator, $filter[0], $this->{$filter[1]}]);
                }
            }
        }

        if ($this->hasAttribute('is_deleted')) {
            $query->andFilterWhere(['is_deleted' => 0]);
        }

        return $dataProvider;
    }

    /**
     * ```php
     *   [
     *     'defaultOrder'=>['name'=>SORT_ASC],
     *     'condition'=>"",// string or array
     *     'params'=>[],
     *     'pageSize'=>30,
     *   ]
     * ```
     * @param array $options
     *
     * @return ActiveRecord $model
     */
    public static function searchModel(array $options)
    {
        $class_name = static::className();
        $model = new $class_name(['scenario' => ActiveRecord::SCENARIO_SEARCH]);
        $model->_searchOptions = $options;

        return $model;
    }
}