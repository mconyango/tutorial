<?php

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/23
 * Time: 10:05 PM
 */

namespace common\controllers;

use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\ModulesEnabled;
use Yii;
use yii\web\Controller as WebController;

class Controller extends WebController
{
    //user flash messages
    const FLASH_SUCCESS = 'success';
    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_INFO = 'info';

    //common labels
    const LABEL_CREATE = 'Add';
    const LABEL_UPDATE = 'Edit';
    /**
     * @var
     */
    public $resource;

    /**
     * @var
     */
    public $moduleKey;

    /**
     * @var
     */
    public $activeMenu;

    /**
     * @var
     */
    public $activeSubMenu;

    /**
     * @var
     */
    public $resourceLabel;

    /**
     * @var
     */
    public $pageTitle;


    public function init()
    {
        parent::init();
    }


    /**
     * Should be called before any action the require ACL
     * @param string $action
     */
    public function hasPrivilege($action = NULL)
    {
        if (NULL === $action)
            $action = Acl::ACTION_VIEW;

        Acl::hasPrivilege($this->resource, $action);
    }

    /**
     * Before action event
     *
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (!Yii::$app->user->isGuest && Users::isRequirePasswordChange() && $this->route !== 'auth/user/change-password') {
                return $this->redirect(['/auth/user/change-password']);
            }
            return true;
        }
        return false;
    }

    /**
     * @param $menu
     * @return bool
     */
    public function isMenuActive($menu)
    {
        return ($this->activeMenu === $menu);
    }

    /**
     * @param $menu
     * @return bool
     */
    public function isSubMenuActive($menu)
    {
        return ($this->activeSubMenu === $menu);
    }
}
