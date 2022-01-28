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
                            if (substr($columnName, 0, 1) <>'-') {
                                echo "<th>" . $columnName . "</th>";
                            }
                        }
                    } else {
                        echo "<td>Empty result set</td>";
                    }
                    ?>
            </thead>

            <?php
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
                        if (substr($columnName, 0, 1) == '!') { # column namen starts with ! link in format naam|link|param1|value1|param2|valule2 (0,1,or 2 params)
                            $part = explode('|', $item[$columnName]);
                            if (strlen($part[0])>20) { # if name for link is larger than 20, concat it and put complete link in help (title)
                                $help=$part[0];
                                $link=substr($part[0],0,20);
                            } else {
                                $help='';
                                $link=$part[0];
                            }
                            if (count($part) == 2){
                                if (substr($part[0],0,5)=='Grade') { # Only for Grade Link
                                    echo "<td><a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" title=\"Naar opdracht\" href=\"".$part[1]."\">".$part[0]."</td>";
                                } else { # Generic
                                    echo "<td>" . Html::a($part[0], [$part[1]]) . "</td>";
                                }
                            } elseif (count($part) == 4) { # Generic
                                echo "<td>" . Html::a($link, [$part[1], $part[2] => $part[3]], ['title'=>$help]) . "</td>";
                            } elseif ( count($part) == 6 ) { # Generic
                                echo "<td>" . Html::a($link, [$part[1], $part[2] => $part[3], $part[4] => $part[5]], ['title'=>$help] ) . "</td>";
                            } elseif ( count($part) == 7 ) { # show processing (hack)
                                echo "<td>" . Html::a($link, [$part[1], $part[2] => $part[3], $part[4] => $part[5]], ['title'=>$help, 'onclick'=>"hide();"] ) . "</td>";
                            } else {
                                echo "<td>Err: Inlvalid link data</td>";
                                echo "<pre><hr>";
                                dd( ["Err: Inlvalid link data", $item, $part] );
                            }
                        } elseif (substr($columnName, 0, 1) <> '-') {
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
                            echo number_format($tot[$columnName], 0, ',', ' ');
                            echo "</td>";
                        } elseif (substr($columnName, 0, 1) <> '-') {
                            echo "<td></td>";
                        }
                    }
                    echo "</tr>";
                }
            }

            ?>

        </table>

    <?php if ( isset($lastLine) ) echo $lastLine; ?>

    </div>
</div>