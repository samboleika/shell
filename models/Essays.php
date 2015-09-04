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
    const ESSAY_PHOTO_PATH = "../essay_images/";
    
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
            [['user_id', 'status'], 'integer'],
            [['photo_path'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 1000]
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
}