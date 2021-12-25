<?php

use yii\helpers\Url;
use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
//dd($data);
$totScore = 0;

function getInitials($name)
{
    $words = explode(" ", $name);

    $initials = "";

    foreach ($words as $w) {
        if (isset($w[0])) {
            $initials .= $w[0];
        }
    }
    return $initials;
}

function getStatus($status)
{
    if ($status == "submitted") return 'ingeleverd';
    if ($status == "graded") return 'beoordeeld';
    if ($status == "unsubmitted") return '-';
    return '?';
}


?>

<style>
    .right {
        text-align: right;
        border-right: dashed 1px #c0c0c0;
        background: #fdffff;
    }

    .left {
        text-align: right;
        border-left: dashed 1px #c0c0c0;
        background: #fdffff;
    }
    .nakijken {
        background: #eeffa8;
    }
    .hoverTable tr:hover td {
            background-color: #f6f6ff;
    }
</style>

<div class="card shadow">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h2>Module <i><?= $data[0]['module'] ?></i> van <?= $data[0]['naam'] ?></h2>
            </div>

            <div class="col-md-auto">

            </div>

        </div>
    </div>

    <div class="card-body">
        <table class="table hoverTable">
            <?php
            $totScore = 0;
            $totMaxScore = 0;
            $totSubmitted = 0;
            echo "<tr>";
            echo "<th>Opdrachtnaam</th>";
            echo "<th>Status</th>";
            echo "<th>Ingeleverd</th>";
            echo "<th class=\"left\" title=\"Behaalde score\">Score</th>";
            echo "<th class=\"right\" title=\"Maximum te behalen\">Max</th>";
            echo "<th>Beoordeeld</th>";
            echo "<th>Door</th>";
            echo "<th></th>";
            echo "</tr>";
            foreach ($data as $item) {
                $totScore += $item['Score'];
                $totMaxScore += $item['MaxScore'];
                if ($item['Status'] == 'submitted' || $item['Status'] == 'graded') {
                    $totSubmitted += 1;
                }
                $link1 = substr($item['Link'], 0, strpos($item['Link'], "?"));
                $link2 = "https://talnet.instructure.com/courses/" . $item['course_id'] . "/gradebook/speed_grader?assignment_id=" . $item['a_id'] . "&student_id=" . $item['u_id'];
                echo "<tr>";

                echo "<td>";
                echo "<a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" title=\"Naar opdracht\" href=\"";
                echo $link1;
                echo "\">" . $item['Opdrachtnaam'] . "</a>";
                echo "</td>";

                echo "<td>" . getStatus($item['Status']) . "</td>";

                if ($item['Ingeleverd'] > $item['Beoordeeld'] &&  $item['Beoordeeld'] != "") {
                    echo "<td style=\"background:#f5ffed;\">" . strtok($item['Ingeleverd'], " ") . "</td>";
                } else {
                    echo "<td>" . strtok($item['Ingeleverd'], " ") . "</td>";
                }


                echo "<td class=\"left\">" . $item['Score'] . "</td>";

                echo "<td class=\"right\">" . $item['MaxScore'] . "</td>";

                echo "<td>" . strtok($item['Beoordeeld'], " ") . "</td>";

                echo "<td>" . getInitials($item['Door']) . "</td>";

                echo "<td>";
                if ($item['Ingeleverd'] > $item['Beoordeeld'] &&  $item['Beoordeeld'] != "") {
                    $style = "#a6ff66";
                } else {
                    $style = "none";
                }
                if ((isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin')) {
                    echo "<a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='" . $style . "'\" style=\"background:" . $style . ";\" title=\"Speedgrader\" href=\"";
                    echo $link2;
                    echo "\">Grade&#10142;</a>";
                }
                echo "</td>";

                echo "</tr>";
            }
            echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
            echo "<td></td>";
            echo "<td><b>" . $totSubmitted . "</b></td>";
            echo "<td></td>";
            echo "<td style=\"text-align: right;\"><b>" . $totScore . "</b></td>";
            echo "<td style=\"text-align: right;\"><b>" . $totMaxScore . "</b></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "</tr>";
            ?>
        </table>
        <?= Html::a('<< Terug', Yii::$app->request->referrer, ['class' => 'btn btn-light']); ?>
    </div>
</div>
<br>
<small>Aan dit overzicht kunnen geen rechten worden ontleend.
    De gegevens in Canvas zijn leidend.</small>