<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
// dd($data);
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
</style>

<div class="card shadow">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h2>Voortgangsoverzicht van <?= $data[0]['Student']?></h2>
                <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
            </div>

            <div class="col-md-auto">
                <?php if ($rank<=12): ?>
                <div title="Stand in klassement" class="numberCircle"><?= $rank ?></div>
                <?php endif; ?>
            </div>
 
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table">
            <?php
            $totVoldaan=0; $totOpdrachten=0; $totPunten=0;
            echo "<tr>";
            echo "<th class=\"\">&nbsp;</th>";
            echo "<th colspan=2>Module</th>";
            echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal opdrachten en percentage opdrachten ingeleverd\"\>Opdrachten</th>";
            echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal punten en percentage van totaal te behalen\">Punten</th>";
            echo "<th title=\"Wanneer is er voor het laatst gewerkt aan deze module\" class=\"tcenter bleft\">Laatst Actief</th>";
            echo "</tr>";
            foreach ($data as $item) {
                if ( $item['Voldaan'] == 'V' ) {
                    $totVoldaan+=1;
                    $totPunten+=$item['Punten %'];
                    $totOpdrachten+=$item['Opdrachten %'];
                }
                
                $dagen=intval((time()-strtotime($item['Laatste Actief']))/86400);
                if ($dagen<=7) {
                    $color='#fdffe3';
                    $title='Afgelopen week actief geweest';
                } elseif ($dagen<=14) {
                    $color='#fff8e3';
                    $title='Afgelopen twee weken actief geweest';
                } else {
                    $color='#ffffff';
                    $title='Activiteit langer dan twee weken geleden';
                }

                echo "<tr>";

                if ( $item['Voldaan']=='V') {
                    echo "<td title=\"Voldaan\" width=60px class=\"\">&#10004;</td>";
      
                } else {
                    echo "<td title=\"Niet voldaan\" width=60px class=\"\">-</td>";
                }

                echo "<td width=60px>".$item['Blok']."</td>";
                echo "<td>".Html::a($item['Module'], ['/public/details-module', 'moduleId'=>$item['module_id'], 'code'=>$item['Code'] ] )."</td>";
                echo "<td class=\"tright bleft\">".$item['Opdrachten']."</td>";
                echo "<td class=\"tright bright\">".$item['Opdrachten %']."%</td>";
                echo "<td class=\"tright bleft\">".$item['Punten']."</td>";
                echo "<td class=\"tright bright\">".$item['Punten %']."%</td>";
                if ( substr( $item['Laatste Actief'], 0, 4) == "1970" ) {
                    echo "<td class=\"tcenter\"> - </td>";
                } else {
                    echo "<td title=\"".$title."\" class=\"tcenter\" style=\"background-color:".$color."\">".$item['Laatste Actief']."</td>";
                }
                echo "</tr>";
            }

            echo "<tr style=\"background-color:#e8f0ff\">";
            echo "<td></td>";
            echo "<td colspan=3><b>TOTAAL: ".$totVoldaan."</b> modules voldaan</td>";       
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "</tr>";
            ?>
        </table>
    </div>
</div>
<br>
<small>Aan dit overzicht kunnen geen rechten worden ontleend.
De gegevens in Canvas zijn leidend.</small>

