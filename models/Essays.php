<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "essays".
 *
 * @property integer $id
 * @property string $photo_path
 * @property string $text
 * @property integer $user_id
 * @property integer $satus
 */
class Essays extends \yii\db\ActiveRecord
{
    const ESSAY_PHOTO_PATH = "essay_images/";
    const ESSAY_PHOTOS_URL = "/essay_images/";
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'essays';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'required'],
            [['user_id', 'status', 'is_nominee', 'is_winner'], 'integer'],
            [['photo_path'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 1000],
            ['create_date', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'photo_path' => 'Photo Path',
            'text' => 'Text',
            'user_id' => 'User ID',
            'satus' => 'Satus',
        ];
    }
    
    public static function getStatusName($id){
        $statuses = [
            1 => "Не обработано",
            2 => "Принято",
            3 => "Не принято"
        ];
        
        return isset($statuses[$id])?$statuses[$id]:0;
    }
    
    public  function getUser() {
		$user = User::find()->where(['users.id' => $this->user_id])->one();
		return $user;
    } 
	
    public static function getPhoto($path) {
        $photo = self::ESSAY_PHOTO_PATH . $path;
        if(file_exists($photo) && $path != ""){
            return self::ESSAY_PHOTOS_URL . $path;
        }
        return "";
    } 
    
    // смена статуса эссе, в общем модерация
    public static function changeStatus($id, $status) {
        $essay = self::findOne($id);
        if(!empty($essay) && self::getStatusName($status)){
            $essay->status = $status;
            if($essay->save()){
                return json_encode(["status" => "ok"]);
            }
        }
        
        return json_encode(["status" => "error"]);
    }
    
    //номинирование эссе
    public static function setNominee($id) {
        $essay = self::findOne($id);
        
        if(!empty($essay)){
			//проверка на  кол-во уже наминированных, не больше 5 должно быть
            $nomanees = (new \yii\db\Query)
                ->select("count(essays.*) as count")
                ->from("essays")
                ->rightJoin("weeks",":date between weeks.date_start and weeks.date_end", [":date" => $essay->create_date])
                ->where("essays.create_date::date  between weeks.date_start and weeks.date_end and essays.is_nominee = 1")
                ->one();
            
            if($nomanees && $nomanees["count"] > 4){
                return json_encode(["status" => "error", "text" => "5 номинантов уже выбрано на этой неделе"]);
            }
			
			//проверка есть ли уже номинированая работа у участника
            $user_nomanee = (new \yii\db\Query)
                ->select("essays.id")
                ->from("essays")
				->rightJoin("users", ["users.id" => $essay->user_id])
                ->rightJoin("weeks",":date between weeks.date_start and weeks.date_end and :date <> weeks.date_end", [":date" => $essay->create_date])
                ->where("essays.create_date::date  between weeks.date_start and weeks.date_end and essays.is_nominee = 1 and essays.user_id = users.id")
                ->one();
				
            if(!empty($user_nomanee)){
                return json_encode(["status" => "error", "text" => "у этого участника уже есть номинированая работа на этой неделе"]);
            }
			
			$essay->is_nominee = 1;
			if($essay->save()){
				$phone = preg_replace("/[^0-9]/", "", $essay->user->phone);
				Yii::$app->db->createCommand()->insert('sms.outbox', ['creator_id_fk' => 1, 'source_number' => 'SHELLRIMULA',  'destination_number' => $phone, 'message_text' => "Ваша работа стала одной из лучших и примет участие в голосовании!" ])->execute();
                   
				return json_encode(["status" => "ok"]);
			}
        }
        
        return json_encode(["status" => "error"]);
    }
    
    // делаем статус победитель
    public static function setWinner($id) {
        $essay = self::findOne($id);
        
        if(!empty($essay)){
            $nomanees = (new \yii\db\Query)
                ->select("count(essays.*) as count")
                ->from("essays")
                ->where("essays.is_winner = 1")
                ->one();
            
            if(!$nomanees || $nomanees["count"] < 5){
                $essay->is_winner = 1;
                if($essay->save()){
					return json_encode(["status" => "ok"]);
                }
            }
            else{
                return json_encode(["status" => "error", "text" => "5 победителей уже выбрано"]);
            }
        }
        
        return json_encode(["status" => "error"]);
    }
	
	public function canVote(){
        $essay_in = (new \yii\db\Query)
            ->from("weeks")
            ->where(":date >= date_vote_start and :date < date_vote_end", [":date" => date('Y-m-d')])
            ->andWhere(":essay_date >= date_start and :essay_date <= date_end", [":essay_date" => date('Y-m-d', strtotime($this->create_date))])
            ->one();
        
		if($this->is_nominee && !empty($essay_in)){
			return true;
		}
		else{ 
			return false;
		}
	}
    
    public function getWeekWeener($week_id) {
        $u_essay = (new \yii\db\Query)
        ->select([
            "essays.*",
            "users.*", 
            "essays.id as essay_id",
            new \yii\db\Expression("case when votes_count.ct is NULL then 0 else votes_count.ct end as c_votes")
        ])
        ->from("essays")
        ->innerJoin("users", "users.id = essays.user_id")
        ->innerJoin("weeks", "essays.create_date::date between weeks.date_start and weeks.date_end")
        ->leftJoin("(select votes.essay_id, count(votes.votes_id) as ct from votes group by votes.essay_id) as votes_count", "votes_count.essay_id = essays.id")
        ->where("essays.is_nominee = 1")
        ->andWhere(["weeks.id" => $week_id])
        ->orderBy("c_votes DESC")
        ->one();
        
        return $u_essay;
    }
	
}