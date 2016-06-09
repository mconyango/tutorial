<?php
namespace common\extensions\lineItem;

use common\helpers\Lang;
use common\models\ActiveRecord;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Description of lineItem
 *
 * @author Fred <mconyango@gmail.com>
 */
class LineItemRow extends Widget
{

    /**
     *
     * @var ActiveRecord
     */
    public $model;

    /**
     * One or More td
     * <pre>
     * <td>content</td>
     * </pre>
     * @var string
     */
    public $td;

    /**
     *
     * @var int
     */
    public $index = 1;

    /**
     *
     * @var string
     */
    public $primaryKeyField = 'id';

    /**
     * This is also header_id field
     * @var string
     */
    public $foreignKeyField;

    /**
     *
     * @var string
     */
    public $trHtmlOptions = ['class' => 'line-item'];

    /**
     *
     * @var string
     */
    public $buttonsTemplate;

    /**
     *
     * @var string
     */
    public $buttonsHtml;

    /**
     *
     * @var bool
     */
    public $showSaveButton = true;

    /**
     *
     * @var bool
     */
    public $showDeleteButton = true;

    /**
     *
     * @var array
     */
    public $buttonsWrapperHtmlOptions = ['style' => 'min-width: 80px;text-align: center;'];

    /**
     *
     * @var array
     */
    public $saveButtonHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $deleteButtonHtmlOptions = ['class' => 'text-danger'];

    /**
     *
     * @var string
     */
    public $saveButtonLabel = '<i class="fa fa-check-circle fa-2x"></i>';

    /**
     *
     * @var string
     */
    public $deleteButtonLabel = '<i class="fa fa-minus-circle fa-2x"></i>';

    /**
     *
     * @var string
     */
    private $saveCssClass = 'save-line-item';

    /**
     *
     * @var string
     */
    private $deleteCssClass = 'delete-line-item';

    /**
     *
     * @var string
     */
    private $modelClassName;

    public function init()
    {
        $this->modelClassName = $this->model->shortClassName();
        parent::init();
    }

    public function run()
    {
        echo $this->getRowHtml();
    }

    protected function getRowHtml()
    {
        $template = '{{cells}}{{buttons}}';
        $this->setButtonsHtml();
        $this->trHtmlOptions['id'] = $this->modelClassName . '_' . $this->index;
        $row_contents = strtr($template, [
            '{{cells}}' => $this->td,
            '{{buttons}}' => $this->buttonsHtml,
        ]);
        return Html::tag('tr', $row_contents, $this->trHtmlOptions);
    }

    protected function setButtonsHtml()
    {
        if (null !== $this->buttonsHtml)
            return false;
        if (null === $this->buttonsTemplate)
            $this->buttonsTemplate = '{{save_button}}&nbsp;&nbsp;{{delete_button}}';
        $this->saveButtonHtmlOptions['title'] = Lang::t('Save');
        if (empty($this->saveButtonHtmlOptions['class']))
            $this->saveButtonHtmlOptions['class'] = $this->saveCssClass;
        else
            $this->saveButtonHtmlOptions['class'] .= ' ' . $this->saveCssClass;
        $save_button_class = $this->model->getIsNewRecord() ? 'text-warning' : 'text-success';
        $this->saveButtonHtmlOptions['class'] .= ' ' . $save_button_class;
        if (!$this->showSaveButton)
            $this->saveButtonHtmlOptions['class'] .= ' hidden';
        $save_button = Html::a($this->saveButtonLabel, 'javascript:void(0);', $this->saveButtonHtmlOptions);

        $this->deleteButtonHtmlOptions['title'] = Lang::t('Delete');
        if (empty($this->deleteButtonHtmlOptions['class']))
            $this->deleteButtonHtmlOptions['class'] = $this->deleteCssClass;
        else
            $this->deleteButtonHtmlOptions['class'] .= ' ' . $this->deleteCssClass;
        if (!$this->showDeleteButton)
            $this->deleteButtonHtmlOptions['class'] .= ' hidden';
        $delete_button = Html::a($this->deleteButtonLabel, 'javascript:void(0);', $this->deleteButtonHtmlOptions);

        $inner_html = strtr($this->buttonsTemplate, [
            '{{save_button}}' => $save_button,
            '{{delete_button}}' => $delete_button,
        ]);

        $inner_html .= $this->getHiddenFieldsHtml();
        $this->buttonsHtml = Html::tag('td', $inner_html, $this->buttonsWrapperHtmlOptions);
    }

    protected function getHiddenFieldsHtml()
    {
        $html = Html::activeHiddenInput($this->model, $this->primaryKeyField, ['id' => "", 'class' => $this->modelClassName . '-' . $this->primaryKeyField]);
        if (!empty($this->foreignKeyField))
            $html .= Html::activeHiddenInput($this->model, $this->foreignKeyField, ['id' => "", 'class' => $this->modelClassName . '-' . $this->foreignKeyField]);
        return $html;
    }

}
