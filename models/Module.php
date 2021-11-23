<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "module".
 *
 * @property int $id
 * @property string $name
 * @property int $position
 * @property int $items_count
 * @property int $published
 * @property int $course_id
 */
class Module extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'module';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'position', 'items_count', 'published', 'course_id'], 'required'],
            [['id', 'position', 'items_count', 'published', 'course_id'], 'integer'],
            [['name'], 'string', 'max' => 200],
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
            'position' => 'Position',
            'items_count' => 'Items Count',
            'published' => 'Published',
            'course_id' => 'Course ID',
        ];
    }
}
