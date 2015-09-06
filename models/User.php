<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord; 
use yii\web\IdentityInterface;
/**
 * This is the model class for table "users".
 * 
 */
class User extends \yii\db\ActiveRecord  implements IdentityInterface
{
    /*
     * статусы пользователей
     */
    const STATUS_PARTICIPANT = 1;// участнгик акции
    const STATUS_CLIENT = 2;//клиент, который может только смотреть админку
    const STATUS_MODERATOR = 3;//модератор, может делать все в админке
    
    public static function tableName()
    {
        return 'users';
    }
    
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'firstname', 'lastname'], 'string', 'max' => 100],
            [['password', 'phone'], 'string', 'max' => 255],
            ['city', 'string', 'max' => 255],
            [['username'], 'unique'],
            ['birth_date', 'safe'],
            [['firstname', 'lastname', 'phone', 'city', 'username', 'birth_date'], 'filter', 'filter'=>'htmlspecialchars']
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'userid' => 'Userid',
            'username' => 'Username',
            'password' => 'Password'
        ];
    }    
    
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
 
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }
    
    public static function findByPasswordResetToken($token)
    {
        $expire = \Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token
        ]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    public function validatePassword($password)
    {
        return $this->password == sha1($password);
    }
    
    public function setPassword($password)
    {
        //$this->password = Yii::$app->security->generatePasswordHash($password);
        $this->password = sha1($password);
    }
    
    public function generateAuthKey()
    {
        //$this->auth_key =Yii::$app->security->generateRandomKey();
        $this->auth_key = "";
    }
    
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomKey() . '_' . time();
    }
    
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function isClient() {
        return ($this->type == self::STATUS_CLIENT)?true:false;
    }
    
    public function isModerator() {
        return ($this->type == self::STATUS_MODERATOR)?true:false;
    }
    
    public static function getYearsOld($birth_date){
        return floor((time() - strtotime($birth_date))/31556926);
    }
    
}