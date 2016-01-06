<?php

namespace app\models;

use Yii;

class Weeks extends \yii\base\Model
{
    const END_DATE = '2015-12-27';

    public static function getWeeks() {
        $weeks = (new \yii\db\Query)
            ->select(["weeks.*", "concat(date_start, ' - ', date_end) as period"])
            ->from("weeks")
            ->orderBy("id")
            ->all();
        
        return $weeks;
    }
    
    public static function getWeeksDropDown() {
        $weeks = self::getWeeks();
        $weeksMap = \yii\helpers\ArrayHelper::map($weeks, 'id', 'period');
        return $weeksMap;
    }
    
    public static function getCurrentWeek($date) {
        $week = (new \yii\db\Query)
            ->from("weeks")
            ->where(":date between date_start and date_end", [":date" => $date])
            ->one();
        
        return $week;
    } 
	
    public static function isCurrentVote($week_id) {
        $week = (new \yii\db\Query)
            ->from("weeks")
            ->where(":date between date_vote_start and date_vote_end and :date <> date_vote_end", [":date" => date('Y-m-d')])
			->andWhere(["id" => $week_id])
            ->one();
        
        return (empty($week))?false:true;
    } 
    
}