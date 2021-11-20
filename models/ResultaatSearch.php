<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Resultaat;

/**
 * ResultaatSearch represents the model behind the search form of `app\models\Resultaat`.
 */
class ResultaatSearch extends Resultaat
{
    /**
     * {@inheritdoc}
     */

    public function rules()
    {
        return [
            [['id', 'course_id', 'ingeleverd', 'ingeleverd_eo', 'punten', 'punten_max', 'punten_eo'], 'integer'],
            [['module', 'student_nummer', 'klas', 'student_naam', 'voldaan', 'laatste_activiteit', 'laatste_beoordeling'], 'safe'],
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
        $query = Resultaat::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
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
            'course_id' => $this->course_id,
            'ingeleverd' => $this->ingeleverd,
            'ingeleverd_eo' => $this->ingeleverd_eo,
            'punten' => $this->punten,
            'punten_max' => $this->punten_max,
            'punten_eo' => $this->punten_eo,
        ]);

        $query->andFilterWhere(['like', 'module', $this->module])
            ->andFilterWhere(['like', 'student_nummer', $this->student_nummer])
            ->andFilterWhere(['like', 'student_naam', $this->student_naam])
            ->andFilterWhere(['like', 'voldaan', $this->voldaan])
            ->andFilterWhere(['like', 'klas', $this->klas])
            ->andFilterWhere(['<', 'datediff(now(), laatste_activiteit)',  $this->laatste_activiteit])
            ->andFilterWhere(['<', 'datediff(now(), laatste_beoordeling)',  $this->laatste_beoordeling]);

        return $dataProvider;
    }
}
