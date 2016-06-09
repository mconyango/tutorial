<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/09
 * Time: 8:02 PM
 */

namespace common\controllers;


use common\helpers\FileManager;
use harrytang\fineuploader\FineuploaderHandler;
use Yii;
use yii\base\Action;

class FineUploaderAction extends Action
{

    public function run()
    {
        $uploader = new FineuploaderHandler();
        //$uploader->allowedExtensions = ['jpeg', 'jpg', 'png', 'bmp', 'gif','pdf','xls','xls']; // all files types allowed by default
        //$uploader->sizeLimit = YOUR_PHP_MaxFileSizeLimit;
        $uploader->inputName = 'qqfile';// matches Fine Uploader's default inputName value by default
        //$uploader->chunksFolder = "chunks";
        if (Yii::$app->request->isPost) {
            // upload file
            $tmp_dir = FileManager::getTempDir();
            $result = $uploader->handleUpload($tmp_dir);
            if (isset($result['success']) && $result['success'] == true) {
                $file_name = $uploader->getName();
                $uuid = $result['uuid'];
                $result['path'] = $tmp_dir . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $file_name;
                $result['url'] = Yii::$app->request->getBaseUrl() . '/uploads/tmp/' . $uuid . '/' . $file_name;
            }
            echo json_encode($result);
        }
    }
}