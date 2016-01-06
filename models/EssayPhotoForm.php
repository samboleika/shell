<?php

namespace app\models;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Essays;

/**
 * модель для добавления фото ессэ
 */
class EssayPhotoForm extends Model
{
    public $photo;
    public $essay_id;
    public $photo_path;
    
    static $valid_exts = ['jpeg', 'jpg', 'png'];
    CONST MAX_SIZE = 3200000;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['photo', 'essay_id'], 'required'],
            ['photo', 'file', 'extensions' => self::$valid_exts, 'maxSize' => self::MAX_SIZE, 'wrongExtension' => "Неверный формат изображения, только: jpg, png, jpeg", 'tooBig' => "Слишком большой размер файла, максимум: 3MB"],
        ];
    }
    public function updatePhotoEssay() {
        $essay = Essays::findOne($this->essay_id);
        $photo = UploadedFile::getInstance($this, 'photo');
        
        if(!empty($essay) && isset($photo) && $photo->tempName && in_array( $photo->getExtension(), self::$valid_exts ) && $photo->size < self::MAX_SIZE){
            $photo_name = uniqid() . '.' . $photo->getExtension();
            $essay->photo_path = $photo_name;
            $this->photo_path = $photo_name;
            if($photo->saveAs(Essays::ESSAY_PHOTO_PATH . $photo_name) && $essay->save()) {
               return true;
            }
        }
        return false;
    }
}
