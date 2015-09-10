<?php

namespace app\models;

use Yii;
use yii\base\Model;

class Socials extends Model
{
    const FB_CLIENT_ID = 390053821205331;
    const FB_CLIENT_SECRET = "953a4355fdc218337ec75be3fd01cb8a";
    const OK_CLIENT_ID = 1153894656;
    const OK_CLIENT_SECRET = "055FFB9988002F49019997EB";
    const OK_APP_KEY = "CBAKAIMFEBABABABA";
    const VK_CLIENT_ID = 5065438;
    const VK_CLIENT_SECRET = "6CBF2clrVl2EHP5sTnfO";
    
    //facebook
    public static function generateFbLink($url = false) {
        if($url){
			return "https://www.facebook.com/dialog/oauth?client_id=".self::FB_CLIENT_ID."&redirect_uri=".urlencode($url)."&response_type=code";
		}
		else{
			return "https://www.facebook.com/dialog/oauth?client_id=".self::FB_CLIENT_ID."&response_type=code";
		}
    }
	
    public static function getFbinfoByCode($code, $ret_url, $essay_id) {
		try{
			$get_acc = @file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=".self::FB_CLIENT_ID."&redirect_uri=".urlencode($ret_url)."&client_secret=".self::FB_CLIENT_SECRET."&code=".$code);
			$uinfo_json = @file_get_contents("https://graph.facebook.com/me?".$get_acc."&fields=id");
			$uinfo = json_decode($uinfo_json);
			return self::addSocialVote("fb", $uinfo->id, $essay_id);
		}
        catch (\Exception $e) {
			return ["status" => "error", "text" => "Ошибка авторизации"];
        }
    }
	
    //odnoklassniki
    public static function generateOkLink($url = false) {
        if($url){
			return "https://connect.ok.ru/oauth/authorize?client_id=".self::FB_CLIENT_ID."&scope=VALUABLE_ACCESS&redirect_uri=".urlencode($url)."&response_type=code";
		}
		else{
			return "https://connect.ok.ru/oauth/authorize?client_id=".self::OK_CLIENT_ID."&scope=VALUABLE_ACCESS&response_type=code";
		}
    }
	
    public static function getOkinfoByCode($code, $ret_url, $essay_id) {
		try{
            $context = stream_context_create(['http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                'content' => "code=".$code."&client_id=".self::OK_CLIENT_ID."&redirect_uri=".urlencode($ret_url)."&grant_type=authorization_code&client_secret=".self::OK_CLIENT_SECRET,
            ]]);

            $url = "https://api.odnoklassniki.ru/oauth/token.do";
            $get_acc_json = @file_get_contents($url, false, $context);
			$get_acc = json_decode($get_acc_json);
            $sig = MD5("application_key=".self::OK_APP_KEY."method=users.getCurrentUser" . MD5($get_acc->access_token . self::OK_CLIENT_SECRET));
            $uinfo_json = @file_get_contents("http://api.ok.ru/fb.do?application_key=".self::OK_APP_KEY."&method=users.getCurrentUser&access_token=".$get_acc->access_token."&sig=".$sig);
			$uinfo = json_decode($uinfo_json);
			return self::addSocialVote("ok", $uinfo->uid, $essay_id);
		}
        catch (\Exception $e) {
			return ["status" => "error", "text" => "Ошибка авторизации"];
        }
    }
    
    //vkontakte
    public static function generateVkLink($url = false) {
        if($url){
			return "https://oauth.vk.com/authorize?client_id=".self::VK_CLIENT_ID."&redirect_uri=".urlencode($url)."&response_type=code&display=popup";
		}
		else{
			return "https://oauth.vk.com/authorize?client_id=".self::VK_CLIENT_ID."&response_type=code&display=popup";
		}
    }
    
    public static function getVkinfoByCode($code, $ret_url, $essay_id) {
		try{
			$uinfo_json = @file_get_contents("https://oauth.vk.com/access_token?client_id=".self::VK_CLIENT_ID."&redirect_uri=".urlencode($ret_url)."&client_secret=".self::VK_CLIENT_SECRET."&code=".$code);
			$uinfo = json_decode($uinfo_json);
			return self::addSocialVote("vk", $uinfo->user_id, $essay_id);
		}
        catch (\Exception $e) {
			return ["status" => "error", "text" => "Ошибка авторизации"];
        }
    }
    
    //add social vote
    public static function addSocialVote($type, $id, $essay_id) {
		$essay = \app\models\Essays::findOne($essay_id);
		if(!empty($essay) && $essay->canVote()){
			$vote = \app\models\Votes::find()->where(["type" => $type, "soc_id" => $id, "essay_id" => $essay_id])->one();
			if(empty($vote)){
				$nvote = new \app\models\Votes;
				$nvote->type = $type;
				$nvote->soc_id = $id;
				$nvote->essay_id = $essay_id;
				$nvote->save();
				return ["status" => "success", "text" => ""];
			}
			else{
				return ["status" => "error", "text" => "За эту работу уже голосовали с вашего аккаунта"];
			}
		}
		else{
			return ["status" => "error", "text" => "Уже нельзя голосовать за эту работу"];
		}
    }
    
}
