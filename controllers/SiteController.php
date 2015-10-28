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

	/* action functions*/
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin() {
        $endDate = \app\models\Weeks::END_DATE;
		if(date('Y-m-d') > $endDate){
            return $this->render('unavailable', ['endDate' => $endDate]);
        }
        
        if (Yii::$app->request->isAjax && Yii::$app->request->get('sendCodePhone')) {
			return $this->sendCodePhone(Yii::$app->request->get('sendCodePhone'));
        }
        elseif (Yii::$app->request->isAjax && Yii::$app->request->get('checkCode') && Yii::$app->request->get('phone')) {
			return $this->checkCode(Yii::$app->request->get('phone'), Yii::$app->request->get('checkCode'));
        }
		
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
            ->innerJoin("weeks", "essays.create_date::date between weeks.date_start and weeks.date_end")
            ->leftJoin("(select votes.essay_id, count(votes.votes_id) as ct from votes group by votes.essay_id) as votes_count", "votes_count.essay_id = essays.id")
            ->where("essays.is_nominee = 1")
            ->andWhere("weeks.date_vote_start <= :ndate", [":ndate" => date("Y-m-d")])
            ->orderBy("weeks.id, essays.id")
            ->all();
			
			$wEssays = [];
			if(count($model) > 0){
				foreach($model as $row){
					$wEssays[$row['week_id']][] = $row;
				}
			}
			
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

    public function actionWinners()
    { 
        $main_winners = (new \yii\db\Query)
            ->select([
                "essays.*",
                "users.*", 
                "essays.id as essays_id",
                new \yii\db\Expression("case when votes_count.ct is NULL then 0 else votes_count.ct end as c_votes")
            ])
            ->from("essays")
            ->innerJoin("users", "users.id = essays.user_id")
            ->leftJoin("(select votes.essay_id, count(votes.votes_id) as ct from votes group by votes.essay_id) as votes_count", "votes_count.essay_id = essays.id")
            ->where("essays.is_winner = 1")
            ->orderBy("essays.id")
            ->all();
        
        $weeks = (new \yii\db\Query)
            ->select([
                "weeks.*"
            ])
            ->from("weeks")
            ->andWhere("date_vote_end < :ndate", [":ndate" => date('Y-m-d')])
            ->orderBy("id")
            ->all();
        
        return $this->render('winners', [
            'main_winners' => $main_winners,
            'weeks' => $weeks
        ]);
    }
    
    public function actionWinnerinfo()
    { 
        if((int)Yii::$app->request->post('essay_id') > 0){
            $main_winner = (new \yii\db\Query)
                ->select([
                    "essays.*",
                    "users.*",
                    new \yii\db\Expression("case when votes_count.ct is NULL then 0 else votes_count.ct end as c_votes")
                ])
                ->from("essays")
                ->innerJoin("users", "users.id = essays.user_id")
                ->leftJoin("(select votes.essay_id, count(votes.votes_id) as ct from votes group by votes.essay_id) as votes_count", "votes_count.essay_id = essays.id")
                ->andWhere(["essays.id" => (int)Yii::$app->request->post('essay_id')])
                ->one();
            
            if(empty($main_winner)){
                return "Информация не найдена";
            }
            
            return $this->renderPartial('winner-info', [
                'winner' => $main_winner
            ]);
        }
        else{
            return "Нет информации";
        }
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
	
	/* helper functions*/
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
    
	public function sendCodePhone($phone){
		$phone = preg_replace("/[^0-9]/", "", $phone);
		
		if($phone && strlen($phone) == 11 && substr($phone, 0, 1) == 7){
			$rand_code = \app\components\ShellHelper::randomCode(6);
			$have_phone = (new \yii\db\Query)
				->select('*')
				->from('phones')
				->where(['number' => $phone])
				->one();
			
			if(!empty($have_phone)){
				if($have_phone['try_count'] < 10){
					Yii::$app->db->createCommand()->update('phones', ['code' => $rand_code, 'try_count' => ((int)$have_phone['try_count'] + 1)],['number' => $phone])->execute();
					Yii::$app->db->createCommand()->insert('sms.outbox', ['creator_id_fk' => 1, 'source_number' => 'SHELLRIMULA',  'destination_number' => $phone, 'message_text' => $rand_code ])->execute();
					return json_encode(['status' => 'ok']);
				}
				else{
					return json_encode(['status' => 'error', 'text' => 'Превышен лимит отправки кода']);
				}
			}
			else{
				Yii::$app->db->createCommand()->insert('phones', ['number' => $phone, 'code' => $rand_code ])->execute();
				Yii::$app->db->createCommand()->insert('sms.outbox', ['creator_id_fk' => 1, 'source_number' => 'SHELLRIMULA',  'destination_number' => $phone, 'message_text' => $rand_code ])->execute();
				return json_encode(['status' => 'ok']);
			}
		}
		else{
			return json_encode(['status' => 'error', 'text' => 'Неверный формат номера телефона']);
		}
        
	}
	
	public function checkCode($phone,$code){
		$phone = preg_replace("/[^0-9]/", "", $phone);
		$have_phone = (new \yii\db\Query)
			->select('*')
			->from('phones')
			->where(['number' => $phone, 'code' => $code])
			->one();
			
			if(!empty($have_phone)){
				Yii::$app->db->createCommand()->update('phones', ['is_confirm' => 1], ['number' => $phone])->execute();
				return json_encode(['status' => 'ok']);
			}
			else{
				return json_encode(['status' => 'error', 'text' => 'Код введен неверно']);
			}
	}
	
}
