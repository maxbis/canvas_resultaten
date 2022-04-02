<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $login_id
 * @property int $student_nr
 * @property string|null $klas
 */
class Student extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'login_id', 'student_nr'], 'required'],
            [['id', 'student_nr', 'grade'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['login_id', 'comment'], 'string', 'max' => 80],
            [['klas'], 'string', 'max' => 2],
            [['code'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'login_id' => 'Login ID',
            'student_nr' => 'Student Nr',
            'klas' => 'Klas',
            'code' => 'Code',
            'comment' => 'Comment',
            'grade' => 'Grade',
        ];
    }
}
