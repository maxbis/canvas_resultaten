<?php

use yii\helpers\Html;

$nr = 0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$tot = [];

?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
    .bottom-button {
        padding: 0.375rem 0.75rem;
        font-size: 0.8em;
        text-align: center;
        cursor: pointer;
        color: #404040;
        font-weight: 400px;
        background-color: #f8f9fa;
        border: solid 1px;
        border-color: #d0d0d0;
        border-radius: 0.25rem;
        float: right;
        margin-left: 20px;
    }
    .bottom-button:hover {background-color: #e7e7e7;text-decoration:none;color:#3333ff}

    .bottom-button:active {
        background-color: #d0d0d0;
    }
    .left {
        float: left;
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
                    if (isset($data['row'])) {
                        if (!isset($nocount)) echo "<th style=\"width:30px;color:#A0A0A0;\">#</th>";
                        for ($i = $from; $i < count($data['col']); $i++) {
                            $columnName = $data['col'][$i];
                            if (substr($columnName, 0, 1) == '+') {
                                $tot[$columnName] = 0;
                                $columnName = substr($columnName, 1);
                            }
                            $columnName = str_replace(array("#", "!"), '', $columnName);
                            if (substr($columnName, 0, 1) != '-') {
                                if ( isset($width[$i]) && $width[$i]!=0 ){
                                    echo "<th style=\"width:".$width[$i]."px;\">";
                                }else {
                                    echo "<th>";
                                }
                                echo substr($columnName, 0, 1) != '_' ? $columnName : '&nbsp;';
                                echo "</th>";
                            }
                        }
                    } else {
                        echo "<td><i>Empty result set</i></td>";
                    }
                    ?>
            </thead>

            <?php
            if (isset($data['row'])) {
                $prevItem='';
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
                        if (substr($columnName, 0, 1) == '!' && $item[$columnName]) { # column namen starts with ! link in format naam|link|param1|value1|param2|valule2 (0,1,or 2 params)
                            $part = explode('|', $item[$columnName]);
                            if (strlen($part[0])>22) { # if name for link is larger than 20, concat it and put complete link in help (title)
                                $help=$part[0];
                                $link=substr($part[0],0,22);
                            } else {
                                $help='';
                                $link=$part[0];
                            }
                            if ( substr($columnName, 1, 1) == '#' ) {
                                if ( $prevItem!='' && $item[$columnName] == $prevItem[$columnName] ) {
                                    echo "<td></td>";
                                    continue;
                                }
                            }
                            if (count($part) == 2){
                                if (substr($part[0],0,5)=='Grade') { # Only for Grade Link
                                    echo "<td><a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" title=\"Naar opdracht\" href=\"".$part[1]."\">".$part[0]."</td>";
                                } else { # Generic
                                    //echo "<td>" . Html::a($part[0], [$part[1]]) . "</td>";
                                    echo "<td><a href=\"".$part[1]."\">$part[0]</a></td>";
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
                        } elseif (substr($columnName, 0, 1) == '#' ) {
                            if ( $prevItem=='' || $item[$columnName] != $prevItem[$columnName] ) {
                                echo "<td>" . $item[$columnName] . "</td>";
                            } else { 
                                echo "<td></td>";
                            }
                            
                        } elseif (substr($columnName, 0, 1) <> '-') {
                            echo "<td>" . $item[$columnName] . "</td>";
                        }
                        
                    }
                    $prevItem=$item;
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