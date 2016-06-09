<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 2:03 PM
 */

namespace common\models;


class Model extends \yii\base\Model
{
    /**
     * Get short classname (without the namespace)
     * @return string
     */
    public static function shortClassName()
    {
        $reflect = new \ReflectionClass(static::className());
        return $reflect->getShortName();
    }
}