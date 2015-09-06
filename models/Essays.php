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
            [['user_id', 'status', 'is_nominee'], 'integer'],
            [['photo_path'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 1000],
            ['create_date', 'safe'],
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
}