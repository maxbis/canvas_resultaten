<?php

use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$tot = [];

if ($data['row']) {
    for ($i = $from; $i < count($data['col']); $i++) {
        $columnName = $data['col'][$i];
        if (substr($columnName, 0, 1) == '+') {
            $tot[$columnName] = 0;
            $columnName = substr($columnName, 1);
        }
        if (substr($columnName, 0, 1) == '!') {
            $columnName = substr($columnName, 1);
        }

    }
}

?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }

    .graph {
        position: relative;
        width: 124px;
        height: 24px;
        border: 1px solid #ffebb0;
        background-color: #fffbf0;
    }

    .bar {
        position: absolute;
        border: 0px solid blue;
        background-color: #c0d6eb;
    }
</style>

<div class="card">

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
                         // echo Html::a('Export', [$action . 'export=1'], ['class' => 'btn btn-primary', 'title' => 'Export to CSV',]);
                         echo Html::a('Export', [$action['link'] .'?'. $action['param']??=''], ['class' => $action['class']??='', 'title' => $action['title']??='Title',]);
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
                    <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th colspan=1></th>
                    <th colspan=12>Weeknummers</th>
                </tr>
                <tr>
                <th>#</th>
                <?php
                    for ($i = $from; $i < count($data['col']); $i++) {
                        $columnName = $data['col'][$i];
                        $columnName = str_replace(array("#", "!","+"), '', $columnName);
                        if (intval($columnName) > 0) $columnName = sprintf('%02d', $columnName);
                        if (substr($columnName, 0, 1) != '-') { // ToDO sprintf('%02d', $columnName)
                            echo "<th>";
                            echo substr($columnName, 0, 1) != '_' ? $columnName : '&nbsp;';
                            echo "</th>";
                        }
                    }
                ?>

                </tr>

            </thead>

            <?php

            if ($data['row']) {
                foreach ($data['row'] as $item) {
                    echo "<tr>";
                    if (!isset($nocount)) {
                        $nr++;
                        echo "<td style=\"color:#A0A0A0;\">" . $nr . "</td>";
                    }

                    $count=0;
                    foreach ($data['col'] as $columnName) {
                        if (++$count<=$from) continue;
                        if (substr($columnName, 0, 1) == '+') {
                            $tot[$columnName] += $item[$columnName];
                        }

                        if ( $columnName == 'Graph') {
                            echo "<td><a href=\"activity?studentnr=".$item['-student_nr']."\">";
                                echo "<div class=\"graph\">";
                                    echo "<div style=\"position:absolute; left: 1px; top: 1px; right: 1px; bottom: 1px\">";
                                        for($i=0; $i<12; $i++) {
                                            $value=$item[$data['col'][$i+5]];
                                            //echo "----".$value;
                                            $barColor = '#ffb3b9';
                                            if ($value == 0 ) {
                                                $barColor = '#ff4242';
                                            }
                                            if ( $value > 4 ) {
                                                $barColor = '#c4ebc0';
                                            }                                         
                                            echo "<div class=\"bar\" style=\"background-color: ".$barColor.";bottom: 0; left: ".($i*10)."px; width: 8px; height: ".min( intval(1.4*($value)+1) ,25)."px\"></div>";
                                        }
                                    echo "</div>";
                                echo "</div>";
                            echo "</a></td>";
                        }

                        elseif (substr($columnName, 0, 1) == '!') { #hack, column namen starts with ! link in format naam|link
                            $part = explode('|', $item[$columnName]);
                            if (count($part) == 2) {
                                echo "<td><a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" title=\"Naar opdracht\" href=\"".$part[1]."\">".$part[0]."</td>";
                            } elseif (count($part) == 4) {
                                echo "<td>" . Html::a($part[0], [$part[1], $part[2] => $part[3]]) . "</td>";
                            } elseif ( count($part) == 6 ) {
                                echo "<td>" . Html::a($part[0], [$part[1], $part[2] => $part[3], $part[4] => $part[5]]) . "</td>";
                            } else {
                                echo "<td>Err: Inlvalid link data</td>";
                            }
                        } elseif  (substr($columnName, 0, 1) != '-')  {
                            echo "<td>" . $item[$columnName] . "</td>";
                        }
                    }
                }

                if (count($tot)) {
                    echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
                    if (!isset($nocount)) {
                        echo "<td></td>";
                    }
                    $count=0;
                    foreach ($data['col'] as $columnName) {
                        if (++$count<=$from+1) continue;
                        if (substr($columnName, 0, 1) == '+') {
                            echo "<td>";
                            echo $tot[$columnName];
                            echo "</td>";
                        } else {
                            echo "<td></td>";
                        }
                    }
                    echo "</tr>";
                }
            }

            ?>

        </table>
    </div>
</div>