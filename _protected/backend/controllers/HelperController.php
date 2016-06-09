<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 11:06 PM
 */

namespace backend\controllers;


use common\controllers\Controller;
use common\helpers\FileManager;
use Yii;
use yii\helpers\Json;

class HelperController extends Controller
{

    public function actionUploadRedactor($dir = NULL, $baseurl = NULL)
    {
        // files storage folder
        if (empty($dir))
            $dir = FileManager::createDir(FileManager::getUploadsDir() . 'images' . DIRECTORY_SEPARATOR . 'redactor');
        if (empty($baseurl))
            $baseurl = Yii::$app->urlManagerFrontend->createAbsoluteUrl(['uploads/images/redactor']);

        $response = FileManager::uploadImage($dir, 'file');
        if (isset($response['error'])) {
            return Json::encode(['error' => $response['error']]);
        }

        return Json::encode(['filelink' => $baseurl . '/' . $response['file_name']]);
    }
}