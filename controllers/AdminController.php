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

    public function actionIndex(){
        
        if(Yii::$app->request->isAjax && Yii::$app->request->get('changeStatus')){
            return $this->changeStatus(Yii::$app->request->get('essay_id'), Yii::$app->request->get('status'));
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
    
    public function actionWeekwin() {    
        if(Yii::$app->request->isAjax && Yii::$app->request->get('setNominee')){
            return $this->setNominee(Yii::$app->request->get('essay_id'));
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
    
    protected function changeStatus($id, $status) {
        $essay = Essays::findOne($id);
        if(!empty($essay) && Essays::getStatusName($status)){
            $essay->status = $status;
            if($essay->save()){
                return json_encode(["status" => "ok"]);
            }
        }
        
        return json_encode(["status" => "error"]);
    }
    
    protected function setNominee($id) {
        $essay = Essays::findOne($id);
        
        if(!empty($essay)){
            $nomanees = (new \yii\db\Query)
                ->select("count(essays.*) as count")
                ->from("essays")
                ->rightJoin("weeks",":date between weeks.date_start and weeks.date_end", [":date" => $essay->create_date])
                ->where("essays.create_date  between weeks.date_start and weeks.date_end and essays.is_nominee = 1")
                ->one();
            
            if(!$nomanees || $nomanees["count"] < 5){
                $essay->is_nominee = 1;
                if($essay->save()){
                    return json_encode(["status" => "ok"]);
                }
            }
            else{
                return json_encode(["status" => "error", "text" => "5 номинантов уже выбрано на этой неделе"]);
            }
        }
        
        return json_encode(["status" => "error"]);
    }

}
