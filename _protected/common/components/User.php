<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/05/13
 * Time: 7:28 PM
 */

namespace common\components;


use backend\modules\auth\Acl;
use Yii;

class User extends \yii\web\User
{
    /**
     * Checks if the user can view the resource
     * @param $resource
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function canView($resource = null)
    {
        $resource = static::getResource($resource);
        return Acl::hasPrivilege($resource, Acl::ACTION_VIEW, FALSE);
    }

    /**
     * Checks if the user can delete the resource
     * @param $resource
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function canDelete($resource = null)
    {
        $resource = static::getResource($resource);
        return Acl::hasPrivilege($resource, Acl::ACTION_DELETE, FALSE);
    }

    /**
     * Checks if the user can update the resource
     * @param $resource
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function canUpdate($resource = null)
    {
        $resource = static::getResource($resource);
        return Acl::hasPrivilege($resource, Acl::ACTION_UPDATE, FALSE);
    }

    /**
     * Checks if the user can create the resource
     *
     * @param $resource
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function canCreate($resource = null)
    {
        $resource = static::getResource($resource);
        return Acl::hasPrivilege($resource, Acl::ACTION_CREATE, FALSE);
    }

    /**
     * @param null $resource
     * @return mixed|null
     */
    public static function getResource($resource = null)
    {
        if (empty($resource))
            $resource = Yii::$app->controller->resource;
        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function beforeLogout($identity)
    {
        // clear the cache key for ACL, when the user logs out
        $key = Acl::getCacheKey(Yii::$app->user->id);
        Yii::$app->cache->delete($key);
        return parent::beforeLogout($identity);
    }
}