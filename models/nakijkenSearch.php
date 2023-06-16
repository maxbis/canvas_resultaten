<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Nakijken;

/**
 * nakijkenSearch represents the model behind the search form of `app\models\nakijken`.
 */
class nakijkenSearch extends Nakijken
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'assignment_id', 'module_id', 'points_possible'], 'integer'],
            [['module_name', 'assignment_name', 'file_type', 'words_in_order', 'instructie', 'cohort'], 'safe'],
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
        $query = nakijken::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'course_id' => $this->course_id,
            'assignment_id' => $this->assignment_id,
            'points_possible' => $this->points_possible,
        ]);

        $query->andFilterWhere(['like', 'module_name', $this->module_name])
            ->andFilterWhere(['like', 'assignment_name', $this->assignment_name])
            ->andFilterWhere(['like', 'file_type', $this->file_type])
            ->andFilterWhere(['like', 'words_in_order', $this->words_in_order])
            ->andFilterWhere(['like', 'instructie', $this->instructie])
            ->andFilterWhere(['like', 'cohort', $this->cohort]);

        return $dataProvider;
    }
}
