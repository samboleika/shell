<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Essays;
use yii\data\Pagination;

class AdminController extends Controller
{
    public $layout = 'admin';
    
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' =>[
                    [
                        'allow'=>true,
                        'roles'=>['@'],
                        'matchCallback' => function($rule,$action) {
                            return $this->isAdmin($rule, $action);
                        }
                    ],
                    [
                        'allow'=>false,
                        'roles'=>['?']
                    ]
                ]
            ]
        ];
    }
    
    // проверка доступа, клиент или модер
    protected function isAdmin($rule, $action) {
        if(!Yii::$app->user->isGuest && (Yii::$app->user->identity->isModerator() || Yii::$app->user->identity->isClient())) {
            return true;
        }
        return false;
    }

    public function actions(){
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    // страница участников
    public function actionUsers(){
		
        $users = (new \yii\db\Query)
            ->select("users.*")
            ->from("users")
            ->where(['users.type' => 1]);
        
        if(Yii::$app->request->post('filter_firstname')){
            $users->andWhere(["firstname" => Yii::$app->request->post('filter_firstname')]);
        }
        
        if(Yii::$app->request->post('filter_lastname')){
            $users->andWhere(["lastname" => Yii::$app->request->post('filter_lastname')]);
        }
        
        if(Yii::$app->request->post('filter_phone')){
            $users->andWhere(["phone" => Yii::$app->request->post('filter_phone')]);
        }
        
        
        $pages = new Pagination(['totalCount' => $users->count(), 'pageSize' => 10]);
        
        return $this->render('users', [
            'model' => $users->offset($pages->offset)->limit($pages->limit)->all(),
            'filter_firstname' => Yii::$app->request->post('filter_firstname'),
            'filter_lastname' => Yii::$app->request->post('filter_lastname'),
            'filter_phone' => Yii::$app->request->post('filter_phone'),
            'pages' => $pages,
        ]);
    }
    
    // страница модерации работ
    public function actionIndex(){
        
        if(Yii::$app->request->isAjax && Yii::$app->request->get('changeStatus')){
            return Essays::changeStatus(Yii::$app->request->get('essay_id'), Yii::$app->request->get('status'));
        }
        
        $user_essays = (new \yii\db\Query)
            ->select("essays.*, users.*, essays.id as essay_id")
            ->from("users")
            ->rightJoin('essays', 'essays.user_id = users.id')
            ->where(['users.type' => 1]);
        
        if(Yii::$app->request->post('filter_status')){
            $user_essays->andWhere(['essays.status' => (int)Yii::$app->request->post('filter_status')]);
        }
        
        if(Yii::$app->request->post('filter_week')){
            $user_essays->rightJoin('weeks', ["weeks.id" => Yii::$app->request->post('filter_week')]);
            $user_essays->andWhere("essays.create_date between weeks.date_start and weeks.date_end");
        }
        
        $pages = new Pagination(['totalCount' => $user_essays->count(), 'pageSize' => 10]);
        
        return $this->render('index', [
            'model' => $user_essays->offset($pages->offset)->limit($pages->limit)->all(),
            'filter_week' => Yii::$app->request->post('filter_week'),
            'filter_status' => Yii::$app->request->post('filter_status'),
            'pages' => $pages,
        ]);
    }
    
    //страница ежэнедельного розыгрша, выбо номинантов
    public function actionWeekwin() {    
        //ставим статус номинант
        if(Yii::$app->request->isAjax && Yii::$app->request->get('setNominee')){
            return Essays::setNominee(Yii::$app->request->get('essay_id'));
        }
        
        $user_essays = (new \yii\db\Query)
            ->select("essays.*, users.*, essays.id as essay_id")
            ->from("users")
            ->rightJoin('essays', 'essays.user_id = users.id and essays.status = 2')
            ->where(['users.type' => 1]);
        
        if(Yii::$app->request->post('filter_status')){
            $user_essays->andWhere(['essays.is_nominee' => (int)Yii::$app->request->post('filter_status')]);
        }
        
        if(Yii::$app->request->post('filter_week')){
            $user_essays->rightJoin('weeks', ["weeks.id" => Yii::$app->request->post('filter_week')]);
            $user_essays->andWhere("essays.create_date between weeks.date_start and weeks.date_end");
        }
        
        if(Yii::$app->request->post('filter_firstname')){
            $user_essays->andWhere(["firstname" => Yii::$app->request->post('filter_firstname')]);
        }
        
        if(Yii::$app->request->post('filter_lastname')){
            $user_essays->andWhere(["lastname" => Yii::$app->request->post('filter_lastname')]);
        }
        
        $pages = new Pagination(['totalCount' => $user_essays->count(), 'pageSize' => 10]);
        
        return $this->render('week-win', [
            'model' => $user_essays->orderBy("essays.id")->offset($pages->offset)->limit($pages->limit)->all(),
            'filter_week' => Yii::$app->request->post('filter_week'),
            'filter_firstname' => Yii::$app->request->post('filter_firstname'),
            'filter_lastname' => Yii::$app->request->post('filter_lastname'),
            'filter_status' => Yii::$app->request->post('filter_status'),
            'pages' => $pages,
        ]);
        
    }
    
    //экспорт со страницы выбора номинантов
    public function actionExportweek() {
        if(Yii::$app->request->post("essay_id")){
            $user_essays = (new \yii\db\Query)
                ->select([
                    "users.lastname", 
                    "users.firstname", 
                    "users.phone", 
                    "essays.create_date", 
                    "essays.text", 
                    new \yii\db\Expression("case when essays.is_nominee = 1 then 'номинант' else '' end"), 
                    new \yii\db\Expression("case when essays.photo_path = '' then '' else CONCAT('".$_SERVER['SERVER_NAME'].Essays::ESSAY_PHOTOS_URL."', essays.photo_path) end"),
                    ])
                ->from("users")
                ->rightJoin('essays', 'essays.user_id = users.id and essays.status = 2')
                ->where(['users.type' => 1])
                ->andWhere(['IN', 'essays.id', Yii::$app->request->post("essay_id")])
                ->all();
            return \app\components\ShellHelper::exportCsv($user_essays);
        }
        print "Выберите хотя бы одну строку";
        return false;
    }
    
    //страница выбора победтилей
    public function actionMainwin() {   
        //ставим статус победитель
        if(Yii::$app->request->isAjax && Yii::$app->request->get('setWinner')){
            return Essays::setWinner(Yii::$app->request->get('essay_id'));
        }
        
        $user_essays = (new \yii\db\Query)
            ->select("essays.*, users.*, essays.id as essay_id")
            ->from("users")
            ->rightJoin('essays', 'essays.user_id = users.id and essays.status = 2')
            ->where(['users.type' => 1]);
        
        if(Yii::$app->request->post('filter_status')){
            $user_essays->andWhere(['essays.is_nominee' => (int)Yii::$app->request->post('filter_status')]);
        }
        
        if(Yii::$app->request->post('filter_week')){
            $user_essays->rightJoin('weeks', ["weeks.id" => Yii::$app->request->post('filter_week')]);
            $user_essays->andWhere("essays.create_date between weeks.date_start and weeks.date_end");
        }
        
        if(Yii::$app->request->post('filter_firstname')){
            $user_essays->andWhere(["firstname" => Yii::$app->request->post('filter_firstname')]);
        }
        
        if(Yii::$app->request->post('filter_lastname')){
            $user_essays->andWhere(["lastname" => Yii::$app->request->post('filter_lastname')]);
        }
        
        $pages = new Pagination(['totalCount' => $user_essays->count(), 'pageSize' => 10]);
        
        return $this->render('main-win', [
            'model' => $user_essays->orderBy("essays.id")->offset($pages->offset)->limit($pages->limit)->all(),
            'filter_week' => Yii::$app->request->post('filter_week'),
            'filter_firstname' => Yii::$app->request->post('filter_firstname'),
            'filter_lastname' => Yii::$app->request->post('filter_lastname'),
            'filter_status' => Yii::$app->request->post('filter_status'),
            'pages' => $pages,
        ]);
        
    }
    
    //экспорт со страницы выбора победителей
    public function actionExportmain() {
        if(Yii::$app->request->post("essay_id")){
            $user_essays = (new \yii\db\Query)
                ->select([
                    "users.lastname", 
                    "users.firstname", 
                    "users.phone", 
                    "essays.create_date", 
                    "essays.text", 
                    new \yii\db\Expression("case when essays.is_winner = 1 then 'победитель' else '' end"), 
                    new \yii\db\Expression("case when essays.photo_path = '' then '' else CONCAT('".$_SERVER['SERVER_NAME'].Essays::ESSAY_PHOTOS_URL."', essays.photo_path) end"),
                    ])
                ->from("users")
                ->rightJoin('essays', 'essays.user_id = users.id and essays.status = 2')
                ->where(['users.type' => 1])
                ->andWhere(['IN', 'essays.id', Yii::$app->request->post("essay_id")])
                ->all();
            return \app\components\ShellHelper::exportCsv($user_essays);
        }
        print "Выберите хотя бы одну строку";
        return false;
    }
    
    //статистика
    public function actionStatistic() {
        $user_essays = (new \yii\db\Query)
            ->select([
                new \yii\db\Expression("count(*) as allcount"),
                new \yii\db\Expression("count(case when essays.status = 2 then 1 else null end) as confirmed"),
                new \yii\db\Expression("count(case when essays.status = 3 then 1 else null end) as declined"),
            ])
            ->from("users")
            ->rightJoin('essays', 'essays.user_id = users.id')
            ->where(['users.type' => 1]);
        
        if(Yii::$app->request->post('filter_week')){
            $user_essays->rightJoin('weeks', ["weeks.id" => Yii::$app->request->post('filter_week')]);
            $user_essays->andWhere("essays.create_date between weeks.date_start and weeks.date_end");
        }
        
        return $this->render('statistic', [
            'model' => $user_essays->one(),
            'filter_week' => Yii::$app->request->post('filter_week'),
        ]);
        
    }

}
