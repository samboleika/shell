<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\ContactForm;
use app\models\EssayForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        $signupForm = new SignupForm();
        $loginForm = new LoginForm();
        if (!\Yii::$app->user->isGuest || $signupForm->load(Yii::$app->request->post()) && $signupForm->signup()) {
            return $this->AddEssay();
        }
        
        if ($loginForm->load(Yii::$app->request->post()) && $loginForm->login()) {
            return $this->AddEssay();
        }
        
        return $this->render('login', [
            'loginForm' => $loginForm,
            'signupForm' => $signupForm,
        ]);
    }

    public function AddEssay() {
        $essayForm = new EssayForm();
        
        if (Yii::$app->request->isAjax && $essayForm->load(Yii::$app->request->post())) {
            if($essayForm->saveEssay()){
                return json_encode(["status" => "ok"]);
            }
            else{
                return json_encode(["status" => "error"]);
            }
            
        }
        
        return $this->render('add-essay', [
            'model' => $essayForm
        ]);
    }
    
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}