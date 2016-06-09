<?php

namespace common\controllers;

use Yii;

/**
 * Class flashable
 * @package bti\ftms\components\controller
 */
trait flashableTrait
{
    /**
     * @var string
     */
    public $successMessage = "The action completed successfully.";

    /**
     * @var string
     */
    public $warningMessage = "Warning!. The action completed, but please check yourself";

    /**
     * @var string
     */
    public $errorMessage = "Oops!. Action failed";

    /**
     * @var string
     */
    public $infoMessage = "The action completed successfully";

    /**
     * @return $this
     */
    public function flash()
    {

        return $this;
    }

    /**
     * @param string $message
     */
    public function success($message = '')
    {
        Yii::$app->session->setFlash('_flash_success', empty($message) ? $this->getSuccessMessage() : $message);
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * @param string $successMessage
     * @return $this
     */
    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;
        return $this;
    }

    /**
     * @param string $message
     */
    public function error($message = '')
    {
        Yii::$app->session->setFlash('_flash_error', empty($message) ? $this->getErrorMessage() : $message);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @param string $message
     */
    public function warning($message = '')
    {
        Yii::$app->session->setFlash('_flash_warning', empty($message) ? $this->getWarningMessage() : $message);
    }

    /**
     * @return string
     */
    public function getWarningMessage()
    {
        return $this->warningMessage;
    }

    /**
     * @param string $warningMessage
     * @return $this
     */
    public function setWarningMessage($warningMessage)
    {
        $this->warningMessage = $warningMessage;
        return $this;
    }

    /**
     * @param string $message
     */
    public function info($message = '')
    {
        Yii::$app->session->setFlash('_flash_info', empty($message) ? $this->getInfoMessage() : $message);
    }

    /**
     * @return string
     */
    public function getInfoMessage()
    {
        return $this->infoMessage;
    }

    /**
     * @param string $infoMessage
     * @return flashableTrait
     */
    public function setInfoMessage($infoMessage)
    {
        $this->infoMessage = $infoMessage;
        return $this;
    }
}