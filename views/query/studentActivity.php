<?php

use yii\helpers\Url;
use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$tot = [];
//dd($data);
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
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan=2>Laatste 2 dagen</td>
                    <td colspan=1>Laatste 12 weken</td>
                    <td colspan=4><-- oud</td>
                    <td colspan=4 style="text-align: center;">weken</td>
                    <td colspan=4 style="text-align: right;">recent --></td>
                    <td></td>
     
                </tr>
                <tr>
                    <?php
                    if (!isset($nocount)) echo "<th>#</th>";
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
                            echo "<th>" . $columnName . "</th>";
                        }
                    } else {
                        echo "<td>Empty result set</td>";
                    }
                    ?>
                </tr>

            </thead>

            <?php
            //dd($data);
            if ($data['row']) {
                foreach ($data['row'] as $item) {
                    echo "<tr>";
                    if (!isset($nocount)) {
                        $nr++;
                        echo "<td background: linear-gradient(to bottom, green 50%, white 0%);>" . $nr . "</td>";
                    }

                    $count=0;
                    foreach ($data['col'] as $columnName) {
                        if (++$count<=$from) continue;
                        if (substr($columnName, 0, 1) == '+') {
                            $tot[$columnName] += $item[$columnName];
                        }

                        if ( $columnName == 'Graph') {
                            echo "<td>";
                                echo "<div class=\"graph\">";
                                    echo "<div style=\"position:absolute; left: 1px; top: 1px; right: 1px; bottom: 1px\">";
                                        for($i=0; $i<12; $i++) {
                                            $value=$item[$data['col'][$i+6]];
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
                            echo "</td>";
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
                        } else {
                            echo "<td>" . $item[$columnName] . "</td>";
                        }
                    }

                    echo "</tr>";
                }

                if (count($tot)) {
                    echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
                    if (!isset($nocount)) {
                        echo "<td></td>";
                    }
                    $count=0;
                    foreach ($data['col'] as $columnName) {
                        if (++$count<=$from) continue;
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