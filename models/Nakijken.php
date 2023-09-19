<?php

namespace app\models;

use Yii;

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
            [['course_id', 'assignment_id', 'module_id', 'attachments'], 'integer'],
            [['module_name', 'assignment_name'], 'string', 'max' => 60],
            [['file_type'], 'string', 'max' => 4],
            [['file_name'], 'string', 'max' => 20],
            [['cohort'], 'string', 'max' => 3],
            [['words_in_order', 'instructie'], 'string', 'max' => 400],
            [['assignment_id'], 'unique'],
            ['file_type', 'filter', 'filter' => function ($value) {
                return str_replace('.', '', $value);
            }],
            [['config'], 'safe'], 
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
            'file_name' => 'File Name',
            'attachments' => 'Attachments',
            'words_in_order' => 'Words In Order',
            'instructie' => 'Instructie',
            'label' => 'Label',
            'module_id' => 'Module ID',
            'cohort' => 'Cohort'
        ];
    }

    public function getJson_config()
    {
        return json_decode($this->getAttribute('config'), true);
    }

}
