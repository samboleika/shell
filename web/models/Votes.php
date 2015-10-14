<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "votes".
 *
 * @property integer $votes_id
 * @property integer $essay_id
 * @property string $type
 * @property integer $soc_id
 *
 * @property Essays $essay
 */
class Votes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'votes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['essay_id', 'soc_id'], 'integer'],
            [['type'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'votes_id' => 'Votes ID',
            'essay_id' => 'Essay ID',
            'type' => 'Type',
            'soc_id' => 'Soc ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEssay()
    {
        return $this->hasOne(Essays::className(), ['id' => 'essay_id']);
    }
}