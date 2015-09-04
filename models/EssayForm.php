<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Essays;

/**
 * модель для добавления ессэ
 */
class EssayForm extends Model
{
    public $text;
    public $photo;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['text', 'required'],
            ['text', 'string', 'max' => 1000],
            ['photo', 'file', 'extensions' => ['jpeg', 'jpg', 'png'], 'maxSize' => 3200000, 'wrongExtension' => "Неверный формат изображения, только: jpg, png, jpeg", 'tooBig' => "Слишком большой размер файла, максимум: 3MB"],
        ];
    }

    public function saveEssay() {
        $essay = new Essays;
        $essay->text = htmlentities($this->text);
        $essay->user_id = Yii::$app->user->id;
        $photo = UploadedFile::getInstancesByName('photo');
        if(isset($photo[0]) && $photo[0]->tempName){
            $photo_name = uniqid() . '.' . $photo[0]->getExtension();
            $essay->photo_path = $photo_name;
            if($essay->validate()) {
               $photo[0]->saveAs(Essays::ESSAY_PHOTO_PATH . $photo_name);
            }
        }
        return $essay->save();
    }
}
