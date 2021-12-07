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
 * @property string|null $klas
 * @property string $student_naam
 * @property int $ingeleverd
 * @property int $ingeleverd_eo
 * @property int $punten
 * @property int $punten_max
 * @property int $punten_eo
 * @property string $voldaan
 * @property string|null $laatste_activiteit
 * @property string|null $laatste_beoordeling
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
            [['course_id', 'module_id', 'module', 'student_nummer', 'laatste_activiteit', 'ingeleverd', 'ingeleverd_eo', 'punten', 'punten_max', 'punten_eo'], 'required'],
            [['course_id', 'module_id', 'ingeleverd', 'ingeleverd_eo', 'punten', 'punten_max', 'punten_eo'], 'integer'],
            [['laatste_activiteit', 'laatste_beoordeling', 'module_id'], 'safe'],
            [['module'], 'string', 'max' => 100],
            [['student_nummer'], 'string', 'max' => 8],
            [['klas'], 'string', 'max' => 2],
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
            'module_id' => 'Module ID',
            'module' => 'Module',
            'student_nummer' => 'Student Nummer',
            'klas' => 'Klas',
            'student_naam' => 'Student Naam',
            'ingeleverd' => 'Ingeleverd',
            'ingeleverd_eo' => 'Ingeleverd Eo',
            'punten' => 'Punten',
            'punten_max' => 'Punten Max',
            'punten_eo' => 'Punten Eo',
            'voldaan' => 'Voldaan',
            'laatste_activiteit' => 'Laatste Activiteit',
            'laatste_beoordeling' => 'Laatste Beoordeling',
        ];
    }

    //public function getStudentr()
    //{
    //    return $this->hasOne(Student::className(), ['student_nr' => 'student_nummer']);
    //}

    public function getModuleDef() {
        return $this->hasOne(ModuleDef::className(), ['id' => 'module_id']);
    }

    public function getCourse() {
        return $this->hasOne(Course::className(), ['id' => 'module_id']);
    }

    public function getStudent() {
        return $this->hasOne(Student::className(), ['student_nr'=>'student_nummer']);
    }
}
