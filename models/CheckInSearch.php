<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CheckIn;

/**
 * CheckInSearch represents the model behind the search form of `app\models\CheckIn`.
 */
class CheckInSearch extends CheckIn
{
    /**
     * {@inheritdoc}
     */

    public $name;
    public $klas;

    public function rules()
    {
        return [
            [['id', 'studentId'], 'integer'],
            [['action', 'timestamp', 'name', 'klas','browser_hash'], 'safe'],
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
        $query = CheckIn::find()->joinwith(['student']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['timestamp' => SORT_DESC]],
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
            'studentId' => $this->studentId,
            'klas' => $this->klas,
        ]);

        $query->andFilterWhere(['like', 'action', $this->action]);
        $query->andFilterWhere(['like', 'user.name', $this->name]);
        $query->andFilterWhere(['like', 'timestamp', $this->timestamp]);
        $query->andFilterWhere(['like', 'browser_hash', $this->browser_hash]);

        $dataProvider->sort->attributes['name'] = [
            'asc' => ['user.name' => SORT_ASC],
            'desc' => ['user.name' => SORT_DESC],
       ];

       $dataProvider->sort->attributes['klas'] = [
        'asc' => ['user.klas' => SORT_ASC],
        'desc' => ['user.klas' => SORT_DESC],
   ];

        return $dataProvider;
    }
}
