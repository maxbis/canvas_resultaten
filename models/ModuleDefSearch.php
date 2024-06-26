<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ModuleDef;

/**
 * ModuleDefSearch represents the model behind the search form of `app\models\ModuleDef`.
 */
class ModuleDefSearch extends ModuleDef
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pos', 'generiek'], 'integer'],
            [['naam', 'korte_naam', 'voldaan_rule'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ModuleDef::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['pos' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'pos' => $this->pos,
            'generiek' => $this->generiek,
        ]);

        $query->andFilterWhere(['like', 'naam', $this->naam])
            ->andFilterWhere(['like', 'korte_naam', $this->korte_naam])
            ->andFilterWhere(['like', 'voldaan_rule', $this->voldaan_rule]);

        return $dataProvider;
    }
}
