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
            ['create_date', 'safe'],
            [['text'], 'filter', 'filter'=>'htmlspecialchars']
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
        $essay = Essays::findOne($id);
        
        if(!empty($essay)){
			//проверка на  кол-во уже наминированных, не больше 5 должно быть
            $nomanees = (new \yii\db\Query)
                ->select("count(essays.*) as count")
                ->from("essays")
                ->rightJoin("weeks",":date between weeks.date_start and weeks.date_end", [":date" => $essay->create_date])
                ->where("essays.create_date  between weeks.date_start and weeks.date_end and essays.is_nominee = 1")
                ->one();
            
            if($nomanees && $nomanees["count"] > 4){
                return json_encode(["status" => "error", "text" => "5 номинантов уже выбрано на этой неделе"]);
            }
			
			//проверка есть ли уже номинированая работа у участника
            $user_nomanee = (new \yii\db\Query)
                ->select("essays.id")
                ->from("essays")
				->rightJoin("users", ["users.id" => $essay->user_id])
                ->rightJoin("weeks",":date between weeks.date_start and weeks.date_end", [":date" => $essay->create_date])
                ->where("essays.create_date  between weeks.date_start and weeks.date_end and essays.is_nominee = 1 and essays.user_id = users.id")
                ->one();
				
            if(!empty($user_nomanee)){
                return json_encode(["status" => "error", "text" => "у этого участника уже есть номинированая работа на этой неделе"]);
            }
			
			$essay->is_nominee = 1;
			if($essay->save()){
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
}