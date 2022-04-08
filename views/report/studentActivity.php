<?php

use yii\helpers\Url;
use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$tot = [];

?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
</style>

<script>
    function hide() {
        document.getElementById("main").style.display = "none";
        document.getElementById("wait").style.display = "block";
   }
</script>

<div class="card" id="wait" style="display:none;">
    <div class="container">
        <br><h1>Processing...</h1></br>
    </div>
</div>

<div class="card" id="main">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h1><?= ($data['title']) ?></h1>
                <?php
                if (isset($descr)) {
                    echo "<small>" . $descr . "</small>";
                }
                ?>
            </div>
            <div class="col-md-auto">
                <?php
                    if ( isset($action)) {
                        echo Html::a('Export', [$action . 'export=1'], ['class' => 'btn btn-primary', 'title' => 'Export to CSV',]);
                    }
                ?>
            </div>
        </div>
    </div>

    <p></p>

    <div class="card-body">
        <table class="table table-sm hoverTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Week</th>
                    <th  width="250px">Module</th>
                    <th>opdracht</th>
                    <th width="250px">Ingeleverd</th>
                    <th>Poging</th>
                    <th colspan=2 style="color:#7d2e2e;">Punten</th>
                </tr>
            </thead>

            <?php
            $prev_week=0;
            $firstLine=1;

            if ($data['row']) {
                foreach ($data['row'] as $item) {
                    $date_ingeleverd = new DateTime($item['ingeleverd']);
                    $date_ingeleverd_week = $date_ingeleverd->format("W");

                    if ( $date_ingeleverd_week <> $prev_week ) {
                        if ( $prev_week ) {
                            if ( abs($date_ingeleverd_week - $prev_week) > 1 ) {
                                echo "<tr style=\"background-color:#edf9ff;\"><td colspan=8></td></tr>";
                                echo "<tr><td colspan=6></td></tr>";
                            }
                            echo "<tr style=\"background-color:#edf9ff;\"><td colspan=8></td></tr>";
                        }
                        $prev_week = $date_ingeleverd_week;
                        $firstLine=1;
                    }

                    echo "<tr>";

                    $nr++;

                    echo "<td style=\"color:#d0d0d0;\">" . $nr . "</td>";
                    if ( $firstLine ) {
                        echo "<td>". $date_ingeleverd_week ."</td>";
                        $firstLine=0;
                    } else {
                        echo "<td>&nbsp;</td>";
                    }
                    
                    echo "<td>". substr($item['module'],0,40) ."</td>";

                    if ( ! $item['graded']) {
                        $accent='yellow';
                    } else {
                        $accent='';
                    }

                    $link = "https://talnet.instructure.com/courses/" . $item['course_id'] . "/gradebook/speed_grader?assignment_id=" . $item['assignment_id'] . "&student_id=" . $item['student_id'];
                    echo "<td>";
                    echo "<a href=\"".$link."\" style=\"background-color:$accent;\" target=\"_blank\">";
                    echo substr($item['opdracht'],0,40)."</a>";
                    echo "</td>";

                    echo "<td>". $item['ingeleverd'] ."</td>";
                    
                    echo "<td>". $item['poging'] ."</td>";

                    echo "<td style=\"color:#7d2e2e;\">". $item['points'] ."</td>";
                    echo "<td style=\"color:#C09090;\">". $item['max_points'] ."</td>";

                    echo "</tr>";
                }

            }

            ?>

        </table>

    </div>
</div>