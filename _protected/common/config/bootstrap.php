<?php
Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('appRoot', '/' . basename(dirname(dirname(dirname(__DIR__)))));
Yii::setAlias('uploads', dirname(dirname(dirname(__DIR__))) . '/uploads');

//global constants
//define('UPLOADS_DIR', 'uploads');
define('TEMP_DIR', 'tmp');
