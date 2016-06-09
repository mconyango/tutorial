<?php

namespace common\extensions\lineItem;

use common\helpers\Lang;
use common\models\ActiveRecord;
use yii\base\Widget;
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * Description of RenderHeader
 *
 * @author Fred <mconyango@gmail.com>
 */
class LineItem extends Widget
{

    /**
     * The id of the html element containing this widget
     * @var string
     */
    public $container;

    /**
     *
     * @var ActiveRecord
     */
    public $model;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var array
     */
    public $itemsTableHtml;

    /**
     *
     * @var array
     */
    public $itemsTableHtmlOptions = ['class' => 'table table-bordered'];

    /**
     *
     * @var string
     */
    public $cancelUrl;

    /**
     *
     * @var string
     */
    public $finishUrl;

    /**
     *
     * @var string
     */
    public $addItemUrl;

    /**
     *
     * @var string
     */
    public $itemsTitle;

    /**
     *
     * @var string
     */
    public $formTag = 'form';

    /**
     *
     * @var string
     */
    public $formHtmlOptions = ['class' => 'form-horizontal', 'role' => 'form'];

    /**
     *
     * @var bool
     */
    public $addItemOnSave = true;

    /**
     *
     * @var array
     */
    public $contentWrapperHtmlOptions = [];

    /**
     *
     * @var string
     */
    public $primaryKeyField = 'id';

    /**
     *
     * @var string
     */
    public $template = '{{notif}}{{content}}{{buttons}}';

    /**
     *
     * @var string
     */
    public $notifHtmlOptions = [];

    /**
     *
     * @var string
     */
    public $showPanel = true;

    /**
     *
     * @var string
     */
    public $showHead = true;

    /**
     *
     * @var string
     */
    public $showButtons = true;

    /**
     *
     * @var string
     */
    public $itemsTemplate;

    /**
     *
     * @var string
     */
    public $itemsContainerHtmlOptions = [];

    /**
     *
     * @var string
     */
    public $addItemLinkHtmlOptions = ['class' => ''];

    /**
     *
     * @var string
     */
    public $addNewItemLabel;

    /**
     *
     * @var array
     */
    public $addItemHtmlOptions = [];

    /**
     *
     * @var string
     */
    public $addItemLabel;

    /**
     *
     * @var array
     */
    public $buttonsWrapperHtmlOptions = ['class' => 'well well-sm clearfix'];

    /**
     *
     * @var string
     */
    public $buttonsTemplate;

    /**
     *
     * @var array
     */
    public $finishButtonHtmlOptions = ['class' => 'btn btn-primary'];

    /**
     *
     * @var string
     */
    public $finishButtonLabel;

    /**
     *
     * @var array
     */
    public $cancelButtonHtmlOptions = ['class' => 'btn btn-default'];

    /**
     *
     * @var array
     */
    public $cancelButtonLabel;

    /**
     *
     * @var string
     */
    private $model_class_name;

    /**
     *
     * @var string
     */
    public $beforeFinish;

    /**
     *
     * @var string
     */
    public $afterFinish;

    /**
     *
     * @var string
     */
    public $beforeSave;

    /**
     *
     * @var string
     */
    public $afterSave;

    /**
     *
     * @var string
     */
    public $beforeDelete;

    /**
     *
     * @var string
     */
    public $afterDelete;

    /**
     *
     * @var string
     */
    public $itemIdFieldSelector;

    /**
     *
     * @var string
     */
    public $itemHeaderIdFieldSelector;

    /**
     *
     * @var string
     */
    public $saveItemSelector;

    /**
     *
     * @var string
     */
    public $deleteItemSelector;

    /**
     *
     * @var string
     */
    public $itemSelector;

    /**
     *
     * @var string
     */
    public $saveItemUrl;

    /**
     *
     * @var string
     */
    public $deleteItemUrl;

    public function init()
    {
        if (empty($this->container))
            $this->container = 'line_item_form_wrapper';

        if (empty($this->formHtmlOptions['id']))
            $this->formHtmlOptions['id'] = 'line_items_form';
        $this->formHtmlOptions['method'] = 'post';
        if (!empty($this->model))
            $this->model_class_name = $this->model->shortClassName();

        parent::init();
    }

    public function run()
    {
        $inner_html = $this->processTemplate();
        if (empty($this->formHtmlOptions['action']))
            $this->formHtmlOptions['action'] = $this->finishUrl;
        echo Html::tag($this->formTag, $inner_html, $this->formHtmlOptions);

        $this->registerScripts();
    }

    protected function processTemplate()
    {
        if (empty($this->template))
            $this->template = '{{notif}}{{content}}{{buttons}}';
        $html = strtr($this->template, [
            '{{notif}}' => $this->getNotifHtml(),
            '{{content}}' => $this->getContentHtml(),
            '{{buttons}}' => ($this->showButtons) ? $this->getButtonsHtml() : '',
        ]);
        return $html;
    }

    protected function getContentHtml()
    {
        if (empty($this->content))
            $this->content = '{{items}}';
        $content = strtr($this->content, [
            '{{items}}' => $this->getItemsHtml(),
        ]);
        $hidden_fields = $this->getHeadHiddenFieldHtml();
        $inner_html = $content . $hidden_fields;
        return Html::tag('div', $inner_html, $this->contentWrapperHtmlOptions);
    }

    protected function getButtonsHtml()
    {
        if (empty($this->model))
            return "";

        if (null === $this->buttonsTemplate) {
            $this->buttonsTemplate = '<div class="pull-right">{{finish}}&nbsp;&nbsp;{{cancel}}</div>';
        } elseif (empty($this->buttonsTemplate)) {
            $this->buttonsWrapperHtmlOptions['class'] = "";
        }
        $this->finishButtonHtmlOptions['type'] = 'button';
        if (empty($this->finishButtonHtmlOptions['id']))
            $this->finishButtonHtmlOptions['id'] = 'finish-line-item-button';
        if (empty($this->finishUrl))
            $this->finishUrl = Url::to(['finish']);
        $this->finishButtonHtmlOptions['data-href'] = $this->finishUrl;
        if (empty($this->finishButtonLabel))
            $this->finishButtonLabel = Lang::t('Finish');

        $this->cancelButtonHtmlOptions['type'] = 'button';
        if (empty($this->cancelButtonHtmlOptions['id']))
            $this->cancelButtonHtmlOptions['id'] = 'cancel-line-item-button';
        if (empty($this->cancelUrl))
            $this->cancelUrl = Url::to(['cancel']);
        $this->cancelUrl = Url::getReturnUrl($this->cancelUrl);
        $this->cancelButtonHtmlOptions['data-href'] = $this->cancelUrl;
        if (empty($this->cancelButtonLabel))
            $this->cancelButtonLabel = Lang::t('Cancel');

        $inner_html = strtr($this->buttonsTemplate, [
            '{{finish}}' => Html::tag('button', $this->finishButtonLabel, $this->finishButtonHtmlOptions),
            '{{cancel}}' => Html::tag('button', $this->cancelButtonLabel, $this->cancelButtonHtmlOptions),
        ]);
        return Html::tag('div', $this->buttonsWrapperHtmlOptions, $inner_html);
    }

    protected function getHeadHiddenFieldHtml()
    {
        if (!empty($this->model)) {
            if (empty($this->primaryKeyField))
                $this->primaryKeyField = 'id';
            return Html::activeHiddenInput($this->model, $this->primaryKeyField);
        } else
            return "";
    }

    protected function getItemsHtml()
    {
        $this->itemsContainerHtmlOptions['class'] = 'panel panel-default';
        if (null === $this->itemsTemplate && $this->showPanel) {
            $this->itemsTemplate = '<div class="panel-heading"><h3 class="panel-title">{{items_title}}</h3></div>'
                . '{{items_table}}'
                . '<div class="panel-footer clearfix"><ul class="list-inline" style="text-align:right;"><li>{{auto_add_checkbox}}</li><li>&nbsp;&nbsp;&nbsp;</li><li>{{new_row_link}}</li></ul></div>';
        }
        $auto_add_checkbox = $this->getAutoAddCheckBoxHtml();
        $new_row_link = $this->getNewRowLinkHtml();
        if (empty($this->itemsTableHtmlOptions['id']))
            $this->itemsTableHtmlOptions['id'] = 'line-items-table';
        $inner_html = strtr($this->itemsTemplate, [
            '{{items_title}}' => $this->itemsTitle,
            '{{items_table}}' => Html::tag('table', $this->itemsTableHtml, $this->itemsTableHtmlOptions),
            '{{auto_add_checkbox}}' => $auto_add_checkbox,
            '{{new_row_link}}' => $new_row_link,
        ]);
        return Html::tag('div', $inner_html, $this->itemsContainerHtmlOptions);
    }

    protected function getAutoAddCheckBoxHtml()
    {
        if (!$this->addItemOnSave)
            return "";
        if (empty($this->addItemLabel))
            $this->addItemLabel = Lang::t('Add new line on save');
        $template = '{{checkbox}}&nbsp;&nbsp;<span class="text-muted">{{label}}</span>';
        if (empty($this->addItemHtmlOptions['id']))
            $this->addItemHtmlOptions['id'] = 'auto-add-new-row-on-save';
        $checkbox = Html::checkBox($this->addItemHtmlOptions['id'], $this->addItemOnSave, $this->addItemHtmlOptions);
        return strtr($template, [
            '{{checkbox}}' => $checkbox,
            '{{label}}' => $this->addItemLabel,
        ]);
    }

    protected function getNewRowLinkHtml()
    {
        if (empty($this->addItemLinkHtmlOptions['id']))
            $this->addItemLinkHtmlOptions['id'] = 'add-new-item-line';
        if (empty($this->addNewItemLabel))
            $this->addNewItemLabel = Lang::t('Add New Line');
        $this->addNewItemLabel .= ' <i class="fa fa-level-up"></i>';
        if (empty($this->addItemUrl))
            $this->addItemUrl = Url::to(['addItem']);
        $this->addItemLinkHtmlOptions['data-href'] = $this->addItemUrl;

        return Html::a($this->addNewItemLabel, 'javascript:void(0);', $this->addItemLinkHtmlOptions);
    }

    protected function getNotifHtml()
    {
        if (empty($this->notifHtmlOptions['id']))
            $this->notifHtmlOptions['id'] = 'line-items-notif-wrapper';
        return Html::tag('div', "", $this->notifHtmlOptions);
    }

    protected function registerScripts()
    {
        $view = $this->getView();
        AssetBundle::register($view);

        $options = [
            'selectors' => [
                'container' => '#' . $this->container,
                'itemsTable' => !empty($this->itemsTableHtmlOptions['id']) ? '#' . $this->itemsTableHtmlOptions['id'] : null,
                'headerIdField' => !empty($this->model_class_name) ? '#' . $this->model_class_name . '_' . $this->primaryKeyField : null,
                'addItem' => !empty($this->addItemLinkHtmlOptions['id']) ? '#' . $this->addItemLinkHtmlOptions['id'] : null,
                'cancel' => '#' . $this->cancelButtonHtmlOptions['id'],
                'submit' => !empty($this->finishButtonHtmlOptions['id']) ? '#' . $this->finishButtonHtmlOptions['id'] : null,
                'notification' => !empty($this->notifHtmlOptions['id']) ? '#' . $this->notifHtmlOptions['id'] : null,
                'addNewItemOnSave' => isset($this->addItemHtmlOptions['id']) ? '#' . $this->addItemHtmlOptions['id'] : null,
                'itemIdField' => $this->itemIdFieldSelector,
                'itemHeaderIdField' => $this->itemHeaderIdFieldSelector,
                'saveItem' => !empty($this->saveItemSelector) ? $this->saveItemSelector : '.save-line-item',
                'deleteItem' => !empty($this->deleteItemSelector) ? $this->deleteItemSelector : '.delete-line-item',
                'item' => !empty($this->itemSelector) ? $this->itemSelector : 'tr.line-item',
            ],
            'saveItemUrl' => !empty($this->saveItemUrl) ? $this->saveItemUrl : Url::to(['saveItem']),
            'deleteItemUrl' => !empty($this->deleteItemUrl) ? $this->deleteItemUrl : Url::to(['deleteItem']),
        ];

        foreach (['beforeFinish', 'afterFinish', 'afterSave', 'beforeSave', 'beforeDelete', 'afterDelete'] as $event) {
            if ($this->$event !== null) {
                if ($this->$event instanceof JsExpression)
                    $options[$event] = $this->$event;
                else
                    $options[$event] = new JsExpression($this->$event);
            }
        }

        $view->registerJs("MyApp.plugin.lineItem(" . Json::encode($options) . ")");
    }

}
