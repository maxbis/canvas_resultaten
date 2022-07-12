<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "check_in".
 *
 * @property int $id
 * @property int $studentId
 * @property string $action
 * @property string $timestamp
 */
class CheckIn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'check_in';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studentId', 'action'], 'required'],
            [['studentId'], 'integer'],
            [['timestamp','browser_hash'], 'safe'],
            [['action'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studentId' => 'Student ID',
            'action' => 'Action',
            'timestamp' => 'Timestamp',
        ];
    }

    public function getStudent() {
        return $this->hasOne(Student::className(), ['id' => 'studentId']);
    }
}
