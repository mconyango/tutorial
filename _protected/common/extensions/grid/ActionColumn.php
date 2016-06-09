<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 3:01 AM
 */

namespace common\extensions\grid;

use backend\modules\auth\Acl;
use common\helpers\Lang;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ActionColumn extends \kartik\grid\ActionColumn
{

    public $width = '100px';
    /**
     * Render default action buttons
     *
     * @return string
     */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url) {
                $options = $this->viewOptions;
                $title = Lang::t('View');
                $icon = '<span class="fa fa-eye"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(['title' => $title, 'data-pjax' => '0'], $options);
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    return '<li>' . Html::a($label, $url, $options) . '</li>' . PHP_EOL;
                } else {
                    return Html::a($label, $url, $options);
                }
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model) {
                $options = $this->updateOptions;
                $title = Lang::t('Update');
                $icon = '<span class="fa fa-pencil text-success"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(['title' => $title, 'data-pjax' => '0', 'class' => 'show_modal_form', 'data-grid' => $model->shortClassName() . '-grid-pjax'], $options);
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    $link = \Yii::$app->user->canUpdate($this->grid->view->context->resource) ? Html::a($label, $url, $options) : '';
                    $li = !empty($link) ? '<li>' . $link . '</li>' . PHP_EOL : '';
                    return $li;
                } else {
                    return \Yii::$app->user->canUpdate($this->grid->view->context->resource) ? Html::a($label, $url, $options) : '';
                }
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model) {
                $options = $this->deleteOptions;
                $title = Lang::t('Delete');
                $icon = '<span class="fa fa-trash text-danger"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(
                    [
                        'title' => $title,
                        'data-confirm-message' => Lang::t('DELETE_CONFIRM'),
                        'data-href' => $url,
                        'data-pjax' => '0',
                        'class' => 'grid-update',
                        'data-grid' => $model->shortClassName() . '-grid-pjax'
                    ],
                    $options
                );
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    $link = \Yii::$app->user->canDelete($this->grid->view->context->resource) ? Html::a($label, 'javascript:void(0);', $options) : '';
                    $li = !empty($link) ? '<li>' . $link . '</li>' . PHP_EOL : '';
                    return $li;
                } else {
                    return \Yii::$app->user->canDelete($this->grid->view->context->resource) ? Html::a($label, 'javascript:void(0);', $options) : '';
                }
            };
        }
    }
}