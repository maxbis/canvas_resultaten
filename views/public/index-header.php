<?php

use yii\helpers\Html;


$quality='';
if ($pogingen) { // detemine the number of (quality) stars
    if ($pogingen < 100) $quality.='&#9733;';
    if ($pogingen < 50)  $quality.='&#9733;';
    if ($pogingen < 20)  $quality.='&#9733;';
}
$size='';
if ($minSubmitted) { // min. number of submissions over last threee weeks (not shown yet)
    if ($pogingen > 4) $size.='&#9733';
    if ($pogingen > 9) $size.='&#9733';
    if ($pogingen > 17) $size.='&#9733';
}

?>


<div class="container">

    <div class="row align-items-end justify-content-between">

        <div class="col">
  
            <div id="ranking" class="top">
                <?php if ($rank <= 16) : ?>
                    <div id="ranking-p1" title="Stand in klassement" class="numberCircle ranking"><?= $rank ?></div>
                <?php endif; ?>
                <div id="ranking-p2" title="1-3 sterren geeft kwaliteit van werk weer. Over laatste zes weken <?=$pogingen?>% herkansingen." class="star-yellow ranking">&nbsp;&nbsp;<?= $quality ?></div>
            </div>
            
            <br>
            Voortgangsoverzicht <span style="color:white"><?= $data[0]['student_nummer'] ?></span>
            <h3><?= $data[0]['Student']; ?>
                
                <?php // small grey klass - when 'beheer' this is a link to edit student (for opening courses)
                    if (isset(Yii::$app->user->identity->username) && Yii::$app->user->identity->username == 'beheer') { 
                        echo Html::a($data[0]['Klas'], [ '/student/update','id' => $data[0]['student_id'] ], ['title'=> 'Edit student', 'class' => 'klas']);
                    } else {
                        echo "<span class=\"klas\">".$data[0]['Klas']."</span>";
                    }
                ?>

            </h3>
            
            <!-- make message editable if user is logged in, jQuery in index.php will perform Ajax call -->
            <?php if ( Yii::$app->user->isGuest && False ) $addEditable="false"; else $addEditable="true"; ?>
            <span style="font-style: italic;" class="editable" contentEditable="<?=$addEditable?>" id="<?=$data[0]['student_id']?>"><?= $data[0]['Message'];?></span><br/>

            <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
        </div>

        <div class="col">
            <?php
            use scotthuangzl\googlechart\GoogleChart;
            if( ! isMobileDevice() ){
                if (gettype($chart) == 'array' && count($chart) > 1) {
                    echo "<br>";
                    if ( Yii::$app->user->isGuest ) {
                        echo GoogleChart::widget($chart);
                    } else {
                        echo Html::a(GoogleChart::widget($chart), ['/report/activity', 'studentnr'=>$data[0]['student_nummer'] ], ['title'=> 'Details',]);
                    }
                }
            }
            ?>
        </div>
    </div>
</div>