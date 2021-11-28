<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course".
 *
 * @property int $id
 * @property string $naam
 * @property string $korte_naam
 * @property int $pos
 * @property int $update_prio
 */
class Course extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'naam', 'korte_naam', 'pos', 'update_prio'], 'required'],
            [['id', 'pos', 'update_prio'], 'integer'],
            [['naam'], 'string', 'max' => 18],
            [['korte_naam'], 'string', 'max' => 6],
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
            'naam' => 'Naam',
            'korte_naam' => 'Korte Naam',
            'pos' => 'Pos',
            'update_prio' => 'Update Prio',
        ];
    }
}
