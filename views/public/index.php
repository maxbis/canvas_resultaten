<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;

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
</style>

<div class="card">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h2>Voortgangsoverzicht van <?= $data[0]['Student']?></h2>
                <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
            </div>

            <div class="col-md-auto">
                <?php if ($rank<=12): ?>
                <div class="numberCircle"><?= $rank ?></div>
                <?php endif; ?>
            </div>
 
        </div>
    </div>

    <div class="card-body">
        <table class="table">
            <?php
            $totVoldaan=0; $totOpdrachten=0;
            echo "<tr>";
            echo "<th>Blok</th>";
            echo "<th>Module</th>";
            echo "<th>Voldaan</th>";
            echo "<th>Opdrachten</th>";
            echo "<th>Punten %</th>";
            echo "<th>Laatst Actief</th>";
            echo "</tr>";
            foreach ($data as $item) {
                if ( $item['Voldaan'] == 'V' ) {
                    $totVoldaan+=1;
                }
                $totOpdrachten+=$item['Opdrachten'];

                $dagen=intval((time()-strtotime($item['Laatste Actief']))/86400);
                if ($dagen<=7) {
                    $color='#fdffe3';
                } elseif ($dagen<=14) {
                    $color='#fff8e3';
                } else {
                    $color='#ffffff';
                }

                echo "<tr>";
                echo "<td>".$item['Blok']."</td>";
                echo "<td>".$item['Module']."</td>";
                echo "<td>".$item['Voldaan']."</td>";
                echo "<td>".$item['Opdrachten']."</td>";
                echo "<td>".$item['Punten %']."</td>";
                echo "<td style=\"background-color:".$color."\">".$item['Laatste Actief']."</td>";
                echo "</tr>";
            }
            echo "<tr style=\"background-color:#e8f0ff\">";
            echo "<td><b>TOTAAL / GEMIDDELD</b></td>";
            echo "<td></td>";
            echo "<td><b>$totVoldaan</b></td>";
            echo "<td>$totOpdrachten</td>";
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

