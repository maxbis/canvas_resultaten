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
            [['module', 'module_id','student_nummer', 'klas', 'student_naam', 'voldaan', 'laatste_activiteit', 'laatste_beoordeling'], 'safe'],
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
        // $query = Resultaat::find()->joinWith(['moduleDef'])->where('pos is not null')->orderBy(['pos'=>'DESC']);
        // $query = Resultaat::find()->joinWith(['moduleDef'])->orderBy(['pos'=>'DESC']);
        // Model::find()->orderBy([new \yii\db\Expression('-column_1 DESC')])->all();
        //$query = Resultaat::find()->orderBy(['module_pos'=>'ASC'])->all();
        $query = Resultaat::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
            'sort'=> ['defaultOrder' => ['laatste_activiteit' => SORT_DESC]],
        ]);

        // $dataProvider->sort->attributes['module'] = [ // ADD this block to suppoer sorting
        //     'asc' => ['module_id' => SORT_ASC],
        //     'desc' => ['module_id' => SORT_DESC],
        // ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'module_id' => $this->module_id,
            'course_id' => $this->course_id,
            'student_nummer'  => $this->student_nummer,
            'voldaan' => $this->voldaan,
            'klas' => $this->klas,
        ]);

        $query->andFilterWhere(['like', 'student_naam', $this->student_naam])
            ->andFilterWhere(['like', 'module',  $this->module])
            ->andFilterWhere(['<', 'datediff(now(), laatste_activiteit)',  $this->laatste_activiteit])
            ->andFilterWhere(['<', 'datediff(now(), laatste_beoordeling)',  $this->laatste_beoordeling])
            ->andFilterWhere(['>', 'punten',  $this->punten])
            ->andFilterWhere(['>', 'ingeleverd',  $this->ingeleverd]);


        return $dataProvider;
    }
}
