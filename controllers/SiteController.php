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
use app\models\Socials;

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
			if(Yii::$app->user->identity->isModerator() || Yii::$app->user->identity->isClient()){
				return $this->redirect(["/admin/index"]);
			}else{
				return $this->AddEssay();
			}
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
    
    public function actionGallery()
    {
        $model = (new \yii\db\Query)
            ->select([
                "essays.*",
                "weeks.*", 
                "users.*", 
                "essays.id as essay_id", 
                "weeks.id as week_id", 
                new \yii\db\Expression("case when votes_count.ct is NULL then 0 else votes_count.ct end as c_votes")
            ])
            ->from("essays")
            ->innerJoin("users", "users.id = essays.user_id")
            ->innerJoin("weeks", "essays.create_date between weeks.date_start and weeks.date_end")
            ->leftJoin("(select votes.essay_id, count(votes.votes_id) as ct from votes group by votes.essay_id) as votes_count", "votes_count.essay_id = essays.id")
            ->where("essays.is_nominee = 1")
            ->andWhere("weeks.date_end <= :ndate", [":ndate" => date("Y-m-d")])
            ->orderBy("essays.id")
            ->all();
			
			$wEssays = [];
			if(count($model) > 0){
				foreach($model as $row){
					$wEssays[$row['week_id']][] = $row;
				}
			}
			
		//echo "<pre>";print_r($wEssays);echo "</pre>";exit;
        return $this->render('gallery', [
            'wEssays' => $wEssays,
        ]);
    }
    
    public function actionVote()
    { 
		$request = Yii::$app->request;
		$result = [];
		if($request->get("code")){
			switch($request->get("soc_type")){
				case "fb":
					$result = Socials::getFbinfoByCode($request->get("code"), \yii\helpers\Url::to(["site/vote", "soc_type" => $request->get("soc_type"),  "essay_id" => $request->get("essay_id")], true), $request->get("essay_id"));
				break;
				case "ok":
					$result = Socials::getOkinfoByCode($request->get("code"), \yii\helpers\Url::to(["site/vote", "soc_type" => $request->get("soc_type"),  "essay_id" => $request->get("essay_id")], true), $request->get("essay_id"));
				break;
				case "vk":
					$result = Socials::getVkinfoByCode($request->get("code"), \yii\helpers\Url::to(["site/vote", "soc_type" => $request->get("soc_type"),  "essay_id" => $request->get("essay_id")], true), $request->get("essay_id"));
				break;
			}
			
			return $this->renderPartial('social-result', $result);
		}
        
        return $this->renderPartial('social-result', ["status" => "error", "text" => "ошибка авторизации"]);
        
    }

    public function actionTest()
    { 
        return $this->render('test');
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
