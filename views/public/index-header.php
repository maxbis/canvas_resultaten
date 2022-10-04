<?php

use yii\helpers\Html;


$quality='';
$size='';

if ($pogingen) {
    if ($pogingen < 100) $quality.='&#9733;';
    if ($pogingen < 50)  $quality.='&#9733;';
    if ($pogingen < 20)  $quality.='&#9733;';
}
if ($minSubmitted) {
    if ($pogingen > 4) $size.='&#9733';
    if ($pogingen > 9) $size.='&#9733';
    if ($pogingen > 17) $size.='&#9733';
}

?>

<div class="container">

    <div class="row align-items-end justify-content-between">

        <div class="col">
  
            <br>
            <div class="top">
                <?php if ($rank <= 16) : ?>
                    <div title="Stand in klassement" class="numberCircle"><?= $rank ?></div>
                <?php endif; ?>
                <!-- <div title="" class="star-red">&nbsp;<?=$size?>&nbsp;</div> -->
                <div title="1-3 sterren geeft kwalitiet van werk weer. Over laatste zes weken <?=$pogingen?>% herkansingen." class="star-yellow">&nbsp;&nbsp;<?= $quality ?></div>
                
            </div>
            
            <br>
            Voortgangsoverzicht <span style="color:white"><?= $data[0]['student_nummer'] ?></span>
            <h3><?= $data[0]['Student']; ?>
                <span style="font-size:12px;color:#999;">
                    <?= $data[0]['Klas']; ?>
                </span>
            </h3>
            
            <i><?= $data[0]['Message'];?></i><br/>
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