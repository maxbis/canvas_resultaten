<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "nakijken".
 *
 * *
 * @property int $course_id
 * @property int $assignment_id
 * @property string $module_name
 * @property string $assignment_name
 * @property string $file_type
 * @property string|null $words_in_order
 * @property int|null $points_possible
 * @property string|null $instructie
 * @property string|null $label
 */
class Nakijken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'canvas.nakijken';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'assignment_id', 'module_name', 'assignment_name', 'file_type'], 'required'],
            [['course_id', 'assignment_id', 'module_id','points_possible'], 'integer'],
            [['module_name', 'assignment_name'], 'string', 'max' => 40],
            [['file_type'], 'string', 'max' => 4],
            [['cohort'], 'string', 'max' => 3],
            [['words_in_order', 'instructie'], 'string', 'max' => 400],
            [['assignment_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'course_id' => 'Course ID',
            'assignment_id' => 'Assignment ID',
            'module_name' => 'Module Name',
            'assignment_name' => 'Assignment Name',
            'file_type' => 'File Type',
            'words_in_order' => 'Words In Order',
            'points_possible' => 'Points Possible',
            'instructie' => 'Instructie',
            'label' => 'Label',
            'module_id' => 'Module ID',
            'cohort' => 'Cohort'
        ];
    }
}
