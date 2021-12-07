<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
//dd($data);
$totScore=0;
?>

<div class="card shadow">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h2>Module <i><?= $data[0]['module'] ?></i> van <?= $data[0]['naam']?></h2>
            </div>

            <div class="col-md-auto">

            </div>
 
        </div>
    </div>

    <div class="card-body">
        <table class="table">
            <?php
            $totScore=0;
            $totSubmitted=0;
            echo "<tr>";
            echo "<th>Opdrachtnaam</th>";
            echo "<th>Status</th>";
            echo "<th>Ingeleverd</th>";
            echo "<th>Score</th>";
            echo "<th>Beoordeeld</th>";
            echo "<th>Door</th>";
            echo "<th>Link</th>";
            echo "</tr>";
            foreach ($data as $item) {
                $totScore+=$item['Score'];
                if ( $item['Status']=='submitted' || $item['Status']=='graded' ) {
                    $totSubmitted+=1;
                }
                $link = substr( $item['Link'] , 0, strpos( $item['Link'] , "?") ) ;
                echo "<tr>";
                echo "<td>".$item['Opdrachtnaam']."</td>";
                echo "<td>".$item['Status']."</td>";
                echo "<td>".$item['Ingeleverd']."</td>";
                echo "<td>".$item['Score']."</td>";
                echo "<td>".$item['Beoordeeld']."</td>";
                echo "<td>".$item['Door']."</td>";
                echo "<td>";
                echo "<a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" href=\"";
                echo $link;
                echo "\">&#10142;Canvas</a></td>";
                echo "</td>";
                echo "</tr>";
            }
            echo "<tr style=\"background-color:#e8f0ff\">";
            echo "<td></td>";
            echo "<td><b>".$totSubmitted."</b></td>";
            echo "<td></td>";
            echo "<td><b>".$totScore."</b></td>";
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

