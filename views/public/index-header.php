<?php

use yii\helpers\Html;

?>

<div class="container">

    <div class="row align-items-end justify-content-between">

        <div class="col">

            <?php if ($rank <= 16) : ?>
                <br>
                <div title="Stand in klassement" class="numberCircle"><?= $rank ?></div>
            <?php endif; ?>
            <br>
            Voortgangsoverzicht <span style="color:white"><?= $data[0]['student_nummer'] ?></span>
            <h3><?= $data[0]['Student'] ?></h3>  
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