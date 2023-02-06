<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "module_def".
 *
 * @property int $id
 * @property string $naam
 * @property int $pos
 * @property string $voldaan_rule
 */
class ModuleDef extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'module_def';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'naam', 'pos', 'voldaan_rule'], 'required'],
            [['id', 'pos', 'generiek', 'norm_uren'], 'integer'],
            [['naam'], 'string', 'max' => 80],
            [['voldaan_rule'], 'string', 'max' => 200],
            [['id', 'pos'], 'unique'],
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
            'pos' => 'Pos',
            'voldaan_rule' => 'Voldaan Rule',
            'norm_uren' => 'Norm Uren',
            'generiek' => 'Generiek',
        ];
    }
}
