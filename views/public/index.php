<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;

?>

<div class="card">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col"><h2>Voortgangsoverzicht van <?= $data[0]['Student']?></h2>
            <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
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

                echo "<tr>";
                echo "<td>".$item['Blok']."</td>";
                echo "<td>".$item['Module']."</td>";
                echo "<td>".$item['Voldaan']."</td>";
                echo "<td>".$item['Opdrachten']."</td>";
                echo "<td>".$item['Punten %']."</td>";
                echo "<td>".$item['Laatste Actief']."</td>";
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

