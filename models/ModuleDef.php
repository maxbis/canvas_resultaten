<?php

namespace app\models;

use Yii;

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
            [['korte_naam'], 'string', 'max' => 6],
            ['korte_naam', 'match', 'pattern' => '/^[^\/]*$/', 'message' => 'Bloknaam cannot contain the "/" character.'],
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
            'korte_naam' => 'Korte Naam',
            'pos' => 'Pos',
            'voldaan_rule' => 'Voldaan Rule',
            'norm_uren' => 'Norm Uren',
            'generiek' => 'Generiek',
        ];
    }
}
