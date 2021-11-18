<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "resultaat".
 *
 * @property int $id
 * @property int $course_id
 * @property string $module
 * @property string $student_nummer
 * @property string $student_naam
 * @property int $ingeleverd
 * @property int $ingeleverd_eo
 * @property int $punten
 * @property int $punten_max
 * @property int $punten_eo
 * @property string $voldaan
 */
class Resultaat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resultaat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'module', 'student_nummer', 'student_naam', 'ingeleverd', 'ingeleverd_eo', 'punten', 'punten_max', 'punten_eo', 'voldaan'], 'required'],
            [['course_id', 'ingeleverd', 'ingeleverd_eo', 'punten', 'punten_max', 'punten_eo'], 'integer'],
            [['module'], 'string', 'max' => 100],
            [['student_nummer'], 'string', 'max' => 8],
            [['student_naam'], 'string', 'max' => 200],
            [['voldaan'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course ID',
            'module' => 'Module',
            'student_nummer' => 'Student Nummer',
            'student_naam' => 'Student Naam',
            'ingeleverd' => 'Ingeleverd',
            'ingeleverd_eo' => 'Ingeleverd Eo',
            'punten' => 'Punten',
            'punten_max' => 'Punten Max',
            'punten_eo' => 'Punten Eo',
            'voldaan' => 'Voldaan',
        ];
    }
}
