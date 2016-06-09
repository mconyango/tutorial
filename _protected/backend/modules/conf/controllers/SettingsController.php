<?php

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\conf\forms\Settings;
use common\helpers\FileManager;
use common\helpers\Lang;
use Yii;

class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->resourceLabel = 'Settings';
        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = new Settings();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * @param null $scope
     * @return string|\yii\web\Response
     */
    public function actionRuntime($scope = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        $log_file = '';
        if (empty($scope))
            $scope = 'backend';
        if (isset($_POST['log_file'])) {
            $log_file = $_POST['log_file'];
        }
        if (isset($_POST['scope'])) {
            $scope = $_POST['scope'];
            if (!isset($_POST['clear']))
                $log_file = null;
        }

        $base_path = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . '_protected' . DIRECTORY_SEPARATOR . $scope . DIRECTORY_SEPARATOR . 'runtime';
        $base_path = $base_path . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        $log_files = FileManager::getDirectoryFiles($base_path . '*.log*');
        if (empty($log_file)) {
            if (!empty($log_files)) {
                $arr = $log_files;
                reset($arr);
                $log_file = key($arr);
            } else
                $log_file = $base_path . 'app.log';
        }


        if (isset($_POST['clear'])) {
            if (file_exists($log_file)) {
                @unlink($log_file);
            }

            return $this->redirect(['runtime', 'scope' => $scope]);
        }

        return $this->render('runtime', [
            'log_files' => $log_files,
            'log_file' => $log_file,
            'scope' => $scope,
        ]);
    }
}
