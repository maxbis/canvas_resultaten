<?php

use yii\helpers\Url;
use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
// dd($data);

function isMobileDevice() {

    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                        tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset($_GET['mobile']);
}

?>
<style>
    .numberCircle {
        border-radius: 50%;
        width: 32px;
        height: 32px;
        padding: 4px;

        background: #a3586d;
        color: #ffffff;
        text-align: center;

        font: 12 Arial, sans-serif;
    }

    .bleft {
        border-left: dashed 1px #c0c0c0;
    }

    .bright {
        border-right: dashed 1px #c0c0c0;
    }

    .tleft {
        text-align: left;
    }

    .tright {
        text-align: right;
    }

    .tcenter {
        text-align: center;
    }
    .hoverTable tr:hover {
        background-color: #f6f6ff;
    }
    .recent7 {
        background-color: #fdffe3;
    }
    .recent14 {
        background-color: #fff8e3;
    }

</style>

<!--
<div class="d-flex justify-content-center">
    <?= Html::img('@web/Bell-small.png', ['alt'=>'some', 'class'=>'']);?>
    <?= Html::img('@web/happy_2022-small.png', ['alt'=>'some', 'class'=>'']);?>
    <?= Html::img('@web/Bell-small.png', ['alt'=>'some', 'class'=>'']);?>
</div>
-->

<div class="card shadow table-responsive">

    <div class="container">

        <div class="row align-items-end justify-content-between">

            <div class="col">

                <?php if ($rank <= 16) : ?>
                    <br>
                    <div title="Stand in klassement" class="numberCircle"><?= $rank ?></div>
                <?php endif; ?>
                <br>
                <h3>Voortgangsoverzicht van<br>
                    <?= $data[0]['Student'] ?>
                </h3>
                <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
            </div>

            <div class="col">
                <?php
                use scotthuangzl\googlechart\GoogleChart;
                if( ! isMobileDevice() ){
                    if (gettype($chart) == 'array' && count($chart) > 1) {
                        echo "<br>";
                        echo GoogleChart::widget($chart);
                    }
                }
                ?>
            </div>

        </div>
    </div>

    <p>

    </p>

    <div class="card-body table-responsive hoverTable">
        <table class="table table-sm">
            <?php
            $totVoldaan = 0;
            $totOpdrachten = 0;
            $totPunten = 0;
            echo "<tr style=\"background:#ffffff;height=10px\">";
            echo "<th class=\"\">&nbsp;</th>";
            echo "<th colspan=2>Module</th>";
            if( ! isMobileDevice() ){
                echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal en percentage ingeleverd\"\>Opdrachten</th>";
                echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal en percentage van totaal te behalen\">Punten</th>";
                echo "<th title=\"Wanneer is er voor deze module het laatst iets ingeleverd\" class=\"tcenter bleft\">Laatst Actief</th>";
            }
            echo "</tr>";

            foreach ($data as $item) {
                if ($item['Voldaan'] == 'V') {
                    $totVoldaan += 1;
                }
                $totPunten += $item['Punten'];
                $totOpdrachten += $item['Opdrachten'];

                $dagen = intval((time() - strtotime($item['Laatste Actief'])) / 86400);
                if ($dagen <= 7) {
                    $color = '#fdffe3';
                    $title = 'Afgelopen week actief geweest';
                    $dateClass = 'recent7';
                } elseif ($dagen <= 14) {
                    $color = '#fff8e3';
                    $title = 'Afgelopen twee weken actief geweest';
                    $dateClass = 'recent14';
                } else {
                    $color = '#ffffff';
                    $title = 'Activiteit langer dan twee weken geleden';
                    $dateClass = '';
                }

                echo "<tr>";

                if ($item['Minpunten'] < 0) {
                    echo "<td title=\"Module kan niet worden afgetekend, vraag docent\" width=60px class=\"\" style=\"font-weight:bolder;color:#821600;\">???</td>";
                } elseif ($item['Voldaan'] == 'V') {
                    echo "<td title=\"Voldaan (" . $item['voldaanRule'] . ")\" width=60px class=\"\">&#10004;</td>";
                } else {
                    echo "<td title=\"Niet voldaan (" . $item['voldaanRule'] . ")\" width=60px class=\"\">-</td>";
                }

                echo "<td width=60px>" . $item['Blok'] . "</td>";
                
                if( ! isMobileDevice() ){
                    echo "<td>" . Html::a($item['Module'], ['/public/details-module', 'moduleId' => $item['module_id'], 'code' => $item['Code']]) . "</td>";
                    echo "<td class=\"tright bleft\">" . $item['Opdrachten'] . "</td>";
                    echo "<td class=\"tright bright\">" . $item['Opdrachten %'] . "%</td>";
                    echo "<td class=\"tright bleft\">" . $item['Punten'] . "</td>";
                    echo "<td class=\"tright bright\">" . $item['Punten %'] . "%</td>";
                    if (substr($item['Laatste Actief'], 0, 4) == "1970") {
                        echo "<td class=\"tcenter\"> - </td>";
                    } else {
                        // echo "<td title=\"" . $title . "\" class=\"tcenter\" style=\"background-color:" . $color . "\">" . $item['Laatste Actief'] . "</td>";
                        echo "<td title=\"" . $title . "\" class=\"tcenter ".$dateClass."\" >" . $item['Laatste Actief'] . "</td>";
                    }
                } else {
                    echo "<td>" . $item['Module'] . "</td>";
                }
                echo "</tr>";
            }

            echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
            
            echo "<td colspan=3><b>" .$totVoldaan . "</b></td>";
            if( ! isMobileDevice() ){
                echo "<td class=\"tright\">". $totOpdrachten ."</td>";
                echo "<td></td>";
                echo "<td class=\"tright\">". $totPunten ."</td>";
                echo "<td></td>";
                echo "<td title=\"Deze score bepaald jouw positie in het klassement\" class=\"tright\">";
                if ( Yii::$app->user->isGuest ) {
                    echo "(score: ".($totVoldaan*200+$totPunten).")";
                } else {
                    echo  "<a href=\"/report/activity?studentnr=".$data[0]['student_nummer']."\">(score: ".($totVoldaan*200+$totPunten).") </a>";
                }
                echo "</td>";
            }
            echo "</tr>";
            ?>
        </table>
    </div>
</div>
<br>
<small style="color:#b0b0b0;font-style: italic;">Aan dit overzicht kunnen geen rechten worden ontleend.
    De gegevens in Canvas zijn leidend.</small>