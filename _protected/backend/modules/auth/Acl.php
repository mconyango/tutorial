<?php

namespace backend\modules\auth;

/**
 * Handles most common ACL db operations
 * @author Fredrick <mconyango@gmail.com>
 * Rewritten to Yii2 on Monday 23th Nov 2015 from 8.21pm
 */
use backend\modules\auth\models\Permission;
use backend\modules\auth\models\Resources;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class Acl
{

    //define actions

    const ACTION_VIEW = 'can_view';
    const ACTION_CREATE = 'can_create';
    const ACTION_UPDATE = 'can_update';
    const ACTION_DELETE = 'can_delete';
    const ACTION_EXECUTE = 'can_execute';

    /**
     * @var
     */
    public static $_privileges;

    /**
     * Get cache key for ACL
     *
     * @param null $user_id
     * @return string
     */
    public static function getCacheKey($user_id = null){

        if (empty($user_id))
            $user_id = Yii::$app->user->id;
        return 'acl-cache' . $user_id;
    }

    /**
     * Gets system-wide privileges of a user;
     * @param int|null $user_id must be passed for api requests
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getPrivileges($user_id = null)
    {
        if (empty($user_id))
            $user_id = Yii::$app->user->id;

        $cache_key = self::getCacheKey($user_id);
        // try retrieving $data from cache
        $privileges = Yii::$app->cache->get($cache_key);
        if ($privileges === false) {
            /*
             * 1. get all the resources
             * 2. get user_type & role
             * 3. for each resource check whether it is forbidden
             * 4. If user type =-1(Dev) or 1(SuperAdmin) return true
             * 5. Check if the role has privilege
             */

            //get all resources
            $resources = Resources::getResources();

            /* @var $user Users */
            $user = Users::loadModel($user_id);
            $forbidden_items = static::getForbiddenItems($user->level_id);
            $permissions = Permission::getData('*', ['role_id' => $user->role_id]);
            //valid actions
            $action = [
                self::ACTION_VIEW,
                self::ACTION_CREATE,
                self::ACTION_UPDATE,
                self::ACTION_DELETE,
                self::ACTION_EXECUTE,
            ];

            $privileges = [];
            foreach ($resources as $row) {
                $resource = $row['id'];
                $permission = static::searchPermission($permissions, $resource);
                $privilege = [];
                foreach ($action as $act) {
                    $passed = static::isActionValid($row, $act);
                    if (!$passed) {
                        $privilege[$act] = 0;
                        continue;
                    }
                    //check forbidden resource
                    if (is_array($forbidden_items) && in_array($resource, $forbidden_items)) {
                        $privilege[$act] = 0;
                        continue;
                    }
                    //system dev,superadmin access everything else
                    if ($user->level_id == UserLevels::LEVEL_DEV || $user->level_id == UserLevels::LEVEL_SUPER_ADMIN) {
                        $privilege[$act] = 1;
                        continue;
                    }
                    //whatever a user accesses now is based on the user's roles
                    if (empty($permission)) {
                        $privilege[$act] = 0;
                        continue;
                    }
                    if ($permission[0][$act] == 0) {
                        $privilege[$act] = 0;
                        continue;
                    }
                    $privilege[$act] = 1;
                }
                $privileges[$resource] = $privilege;
            }


            // store $data in cache so that it can be retrieved next time
            Yii::$app->cache->set($cache_key, $privileges, 120);
        }

        return $privileges;
    }

    /**
     *
     * @param array $data
     * @param string $value
     * @param string $key
     * @return array
     */
    private static function searchPermission($data, $value, $key = 'resource_id')
    {
        $results = [];
        if (is_array($data)) {
            if (isset($data[$key]) && $data[$key] == $value)
                $results[] = $data;
            foreach ($data as $subarray)
                $results = array_merge($results, static::searchPermission($subarray, $value, $key));
        }
        return $results;
    }

    /**
     * This function checks whether a given user has a specified privilege on a specified auth_item
     * @param string $resource_id
     * @param string $action : privilege e.g {can_view,can_create,can_update,can_delete}
     * @param boolean $throw_exception whether to throw exception or not: True throw exception
     * @param string|null $user_id
     * @return bool True if the user has Privilege else false
     * @throws ForbiddenHttpException
     */
    public static function hasPrivilege($resource_id, $action, $throw_exception = true,$user_id=null)
    {
        $privileges = static::getPrivileges($user_id);
        if (empty($privileges[$resource_id][$action])) {
            if ($throw_exception) {
                throw new ForbiddenHttpException(Lang::t('403_error'));
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * get the forbidden resources for a given user type
     * @param string $user_level
     * @return array
     */
    public static function getForbiddenItems($user_level)
    {
        /* @var $model UserLevels */
        $model = UserLevels::loadModel($user_level);
        $forbidden_items = (array)$model->forbidden_items;
        if (!empty($model->parent_id))
            $forbidden_items = array_merge($forbidden_items, static::getForbiddenItems($model->parent_id));

        return $forbidden_items;
    }

    /**
     * check whether the given action is valid on a given resource
     * @param string $item
     * @param string $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public static function isActionValid($item, $action)
    {
        switch ($action) {
            case self::ACTION_VIEW:
                return $item['viewable'];
            case self::ACTION_CREATE:
                return $item['creatable'];
            case self::ACTION_UPDATE:
                return $item['editable'];
            case self::ACTION_DELETE:
                return $item['deletable'];
            case self::ACTION_EXECUTE:
                return $item['executable'];
            default :
                throw new BadRequestHttpException('Invalid $action passed');
        }
    }

}
