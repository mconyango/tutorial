<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\Controller;
use backend\modules\auth\forms\LoginForm;
use backend\modules\auth\forms\PasswordResetRequestForm;
use backend\modules\auth\forms\ResetPasswordForm;
use backend\modules\auth\models\Users;
use Yii;
use yii\base\InvalidParamException;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

/**
 * Auth controller.
 * It is responsible for displaying static pages, and logging users in and out.
 */
class AuthController extends Controller
{
    //public $enableCsrfValidation = false;

    public $layout = '@backend/modules/auth/views/auth/main';

    public function actions()
    {
        return [
            'captcha' => [
                'class' => CaptchaAction::className(),
                'width' => 200,
                'height' => 100,
                'padding' => 10,
                'minLength' => 6,
                'maxLength' => 6,
            ],
        ];
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'request-password-reset', 'reset-password', 'captcha'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'login'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
                ],
            ],
        ];
    }


    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // get setting value for 'Login With Email'
        $lwe = Yii::$app->params['lwe'];

        // if "login with email" is true we instantiate LoginForm in "lwe" scenario
        $lwe ? $model = new LoginForm(['scenario' => 'lwe']) : $model = new LoginForm();

        // everything went fine, log in the user
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } // errors will be displayed
        else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success',
                    'Check your email for further instructions on how to reset your password.<br/>NOTE: If you do not get an email please check your spams folder and mark it as not spam');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error',
                    'Sorry, we are unable to reset password for email provided.');
            }
        } else {
            return $this->render('requestPasswordReset', [
                'model' => $model,
            ]);
        }
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post())
            && $model->validate() && $model->resetPassword()
        ) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

}
