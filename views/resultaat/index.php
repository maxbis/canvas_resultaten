<?php

use yii\helpers\Html;
use yii\grid\GridView;


/* @var $this yii\web\View */
/* @var $searchModel app\models\ResultaatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Resultaten';
$this->params['breadcrumbs'][] = $this->title;
?>

<script>
    function hide() {
        // document.getElementById('main').style.visibility = 'hidden';
        document.getElementById("main").innerHTML = "<br><br>Updating...";
        t=setInterval(waiting,250);
    }
    function waiting() {
        document.getElementById("main").innerHTML = document.getElementById("main").innerHTML + "..";
    }
    function toggleHelp() {
        $("#buttonHelp").toggleClass('d-none');
        $("#helpText").toggleClass('d-none');
    }
</script>

<style type="text/css">
        main { font-size:0.85rem;  }
    </style>

<div class="resultaat-index">

    <div class="container">
        <div class="row align-items-start">
            <div class="col">
                <h1><?= Html::encode($this->title) ?></h1>
                <div id="helpText" class="d-none" onclick="toggleHelp()">
                    <p>Ga met je muis over een veld voor meer info.</P>
                    <p>De student die het meest recent een opdracht heeft ingeleverd staat bovenaan.</p>
                    <p>Zoek je een ander student? Tik dan de naam in de tekstbox (onder het kopje 'Student').</p>
                    <p>Als je de juiste student hebt gevonden, klik dan op de naam van de student. Je ziet nu alles van deze student met de module waarin het meest recent wat veranderd is bovenaan.</p?>
                    <p>Klik je nu <b>nog een keer</b> op de naam dan zie je een overzicht van alle modules en voortgang van deze student.</p>
                    <p>Klik vanuit dit overzicht naar een module voor informatie over de voortgang van deze module.</p>
                    <p>Klik hier om deze tekst te verbergen.</p>
                </div>
                
            </div>
            <div class="col-lg-1">
                <button id="buttonHelp" class="btn btn-secondary" onclick="toggleHelp()">Help</button>
            </div> 
            <div class="col-lg-1">
                <?= Html::a('Export', ['query/get-all-resultaat'], ['class'=>'btn btn-primary', 'title'=> 'Export to CSV',]) ?>
            </div>
        </div>
    </div>

    <br>

    <div id="main">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => 'Blok',
                'attribute'=>'course_id',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;', 'title'=>'Blok'],
                'filter' => $courses,
                'format' => 'raw',
                'value' => function ($data) use ($courses) {
                    if (array_key_exists($data->course_id, $courses)) {
                        return $courses[$data->course_id];
                    } else {
                        return '?';
                    }
                    
                }
            ],
            [   'attribute' => 'module_id',
                'label' => 'Module',
                'contentOptions' => ['style' => 'width:140px; white-space: normal;', 'title'=>'Modulenaam' ],
                'filter' => $modules,
                'format' => 'raw',
                'value' => function ($data) {
                    if ( isset($data->moduleDef['naam']) ) {
                        // actionDetailsModule($userId, $moduleId){
                        return Html::a($data->moduleDef['naam'],['/public/details-module','code'=>$data->student->code,'moduleId'=>$data->module_id],['title'=>'Laat opdrachten zien ('.$data->module_id.')']);
                        return str_replace( "Opdrachten", "", $data->moduleDef['naam']);
                    } else {
                        return "<p title=\"Voldaan-criteria nog niet ingevoerd (".$data->module_id." )\" style=\"color:#808080;font-style: italic;\">".$data->module."</p>";
                    }
                    
                }
            ],
            [   'attribute' => 'klas',
                'label' => 'Klas',
                'filter' => $klas,
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Klas'],
            ],
            [   'attribute' => 'student_naam',
                'label' => 'Student',
                'contentOptions' => ['style' => 'width:160px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) use($searchModel){
                    // first click when searchis partial show all data of this student in this view, then when clicked the search parameter has a the fulle name, show student overzicht
                    if ( isset($searchModel['student_naam']) and $searchModel['student_naam']==$data->student_naam) {
                        return Html::a($data->student_naam, ['/public/index', 'code'=>$data->student->code], ['title'=> 'Overzicht van '.$data->student_naam.' zien',]);
                    } else {
                        return Html::a($data->student_naam, ['/resultaat', 'ResultaatSearch[student_naam]'=>$data->student_naam], ['title'=> 'Laat alleen '.$data->student_naam.' zien',]);
                    }  
                }
            ],
            [
                'attribute'=>'voldaan',
                'label' => 'V',
                'headerOptions' => [ 'style' => 'color:#F0F0F0;' ],
                'contentOptions' => ['style' => 'width:40px; white-space: normal;'],
                'filter' => ['-'=>'Niet Voldaan','V'=>'Voldaan'],
                'format' => 'raw',
                'value' => function ($data) {
                    if ( $data->voldaan=='V'){
                        return 'V';    
                    } else {
                        return '-';
                    }
                    return Html::a($value, ['/query/student', 'studentNummer'=>$data->student_nummer], ['title'=> 'Klik voor details']);
                     
                }
            ],
            [   'attribute' => 'ingeleverd',
                'label' => 'Klaar',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;', 'title'=>'Ingeleverde opdrachten - Ingeleverde eindopdrachten' ],
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->aantal_opdrachten){
                        return round($data->ingeleverd*100/$data->aantal_opdrachten).' %';
                    } else {
                        return '0 %';
                    }
                    return $data->ingeleverd;
                    // return Html::a($value,['/query/details-module','studentNummer'=>$data->student_nummer,'moduleId'=>$data->module_id],['title'=>'Aantal ingeleverde opdrachten']);
                    // return sprintf("<pre>%2d %2d</pre>", $data->ingeleverd, $data->ingeleverd_eo);
                    // return $data->ingeleverd."/".$data->ingeleverd_eo;
                }
            ],
            [   'attribute' => 'punten',
                'label' => 'Score',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;', 'title'=>'Score als percentage'],
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->punten_max){
                        return round($data->punten*100/$data->punten_max).' %';
                    } else {
                        return '0 %';
                    }
                    
                    return Html::a('<b>'.$value.'</b>',['/query/details-module','studentNummer'=>$data->student_nummer,'moduleId'=>$data->module_id],['title'=>'Score aan de hand van behaalde punten']);
                    // return sprintf("<pre>%3.1f %2d %3d %3d%%</pre>", $data->punten, $data->punten_eo, $data->punten_max,  $data->punten_max==0 ? 0 : $data->punten*100/$data->punten_max);
                }
            ],
            [   'attribute' => 'ingeleverd',
                'label' => 'I',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; ', 'title'=>'Aantal ingeleverde opdrachten'],
            ],
            [   'attribute' => 'ingeleverd_eo',
                'label' => 'Ie',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; ', 'title'=>'Aantal ingeleverde opdrachten'],
            ],
            [   'attribute' => 'punten',
                'label' => 'P',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; ', 'title'=>'Totaal aantal punten'],
            ],
            [   'attribute' => 'punten_eo',
                'label' => 'Pe',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; ', 'title'=>'Totaal aantal punten voor eindopdracht'],
            ],
            [   'attribute' => 'punten_max',
                'label' => 'M',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; ', 'title'=>'Maximum te behalen punten'],
            ],
            [   'attribute' => 'laatste_activiteit',
                'label' => 'A',
                'contentOptions' => ['style' => 'width:20px; white-space: normal; '],
                'format' => 'raw',
                'value' => function ($data) {
                    $days = intval((time()-strtotime($data->laatste_activiteit))/86400) ;
                    if ( $days<999) {
                        return "<p title=\"Student $days dagen geleden voor het laatst actief in deze module\">".$days."</p>";
                    } else {
                        return "-";
                    }
                    // ." ". Yii::$app->formatter->asDate($data->laatste_activiteit, 'php:Y-m-d');
                }
            ],
            [   'attribute' => 'laatste_beoordeling',
                'label' => 'B',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    $days = intval((time()-strtotime($data->laatste_beoordeling))/86400);
                    if ( $days<999) {
                        return "<p title=\"Module $days dagen geleden voor het laatst nagekeken\">".$days."</p>";
                    } else {
                        return "-";
                    }
                }
            ],
            [
                'contentOptions' => ['style' => 'width:20px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) use ($updates_available_for) {
                    //dd($updates_available_for);
                    if ( in_array($data->module_id, $updates_available_for) ) {
                        return Html::a('&#x21BA', ['resultaat/update-assignment', 'student_nr'=>$data->student_nummer, 'module_id'=>$data->module_id], ['title'=> 'Update uit Canvas','onclick'=>'hide()']);
                    } else {
                        return "<p title=\"Update uit Canvas niet mogelijk\">".'&#x21BA'."</p>";
                    }
                }

            ],
            // [
            //     'attribute' => 'aantal_opdrachten',
            //     'label' => 'Aantal',
            //     'contentOptions' => ['style' => 'width:40px; white-space: normal;'],

            // ],
            // [   
                // 'attribute' => 'moduleDef.pos',
                // 'label' => 'pos',
                // 'contentOptions' => ['style' => 'width:80px; white-space: normal;'],
                // 'format' => 'raw',
                // 'value' => function ($data) {
                //     return $data->moduleDef['pos'];
                // }
            // ],
            // [
            //     'attribute' => 'module.position',
            // ],

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?></div>

</div>