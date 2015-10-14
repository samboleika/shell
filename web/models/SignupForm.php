<?php
namespace app\models;

use app\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $phone;
    public $password;
    public $password_repeat;
    public $firstname;
    public $lastname;
    public $birth_date;
    public $city;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'password', 'password_repeat', 'firstname', 'lastname', 'birth_date', 'city'], 'required'],
            ['phone', 'unique', 'targetClass' => 'app\models\User', 'message' => 'Этот телефон уже зарегистрирован.'],
            ['phone', 'string', 'min' => 11, 'max' => 20],

            [['password'], 'string', 'min' => 6, 'max' => 20],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>'Пароли не совпадают'],
   
            ['city', 'string', 'max' => 255],
            ['birth_date', 'safe'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate() && $this->checkConfirmPhone()) {
            $user = new User();
            $user->username = $this->phone;
            $user->phone = $this->phone;
            $user->firstname = $this->firstname;
            $user->lastname = $this->lastname;
            $user->birth_date = $this->birth_date;
            $user->date_create = date('Y-m-d');
            $user->city = $this->city;
            $user->setPassword($this->password);
            $user->generateAuthKey(); 
            $myDate = \DateTime::createFromFormat('d.m.Y', $this->birth_date);
            if($myDate){
                $user->birth_date = $myDate->format('Y-m-d');
            }
            else{
                return null;
            }
            
            if ($user->save()) {
                return Yii::$app->user->login($user, 3600*8);
            }
            
        }
        
        return null;
    }
	
    public function checkConfirmPhone() {
		
		$phone = preg_replace("/[^0-9]/", "", $this->phone);
		 
		$have_phone = (new \yii\db\Query)
			->select('*')
			->from('phones')
			->where(['number' => $phone, 'is_confirm' => 1])
			->one();
				
			if(!empty($have_phone)){
				return true;
			}
			else{ 
				Yii::$app->session->setFlash('Телефон не подтвержден');
				return false;
			}
	}
	
	
}
