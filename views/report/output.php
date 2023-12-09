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
    .small-button {
        display: inline-block;
        padding: 1px 6px;
        font-size: 0.7em;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: rgba(0, 123, 255, 0.25);;
        border: 1px solid #a0a0a0;
        border-radius: 4px;
        transition: background-color 0.1s ease;
        margin-top: 0px;
    }

    .small-button:hover {
        background-color: #ffdd00;
        color:#000000;
}
</style>

<script>
    function hide() {
        document.getElementById("main").style.display = "none";
        document.getElementById("wait").style.display = "block";
   }

   function working(url) {
      // Hide the 'main' div
        document.getElementById('main').style.display = 'none';
        document.getElementById('busy').style.display = 'block';

        setInterval(function() {
        var myDiv = document.getElementById('busy');
        myDiv.textContent = myDiv.textContent + '.';
        }, 400);

        // Redirect to the new URL
        window.location.href = url;

    }
    
</script>

<div class="card" id="wait" style="display:none;">
    <div class="container">
        <br><h1>Processing...</h1></br>
    </div>
</div>

<h1><div class="container" id="busy" style="display:none;width:500px;padding-top:500px;">Working....</div></h1>

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
                    if ( isset($action[0]) ) {
                        foreach($action as $thisAction) {
                            echo "&nbsp;&nbsp;&nbsp;";
                            echo Html::a($thisAction['name']??=strtok($thisAction['title']??='button name'," "), [$thisAction['link'] .'?'. $thisAction['param']??=''], ['class' => $thisAction['class']??='', 'title' => $thisAction['title']??='Title',]);
                        }
                    } elseif ( isset($action)) {
                        // echo Html::a('Export', [$action . 'export=1'], ['class' => 'btn btn-primary', 'title' => 'Export to CSV',]);
                        echo Html::a($action['name']??=strtok($action['title']??='button name'," "), [$action['link'] .'?'. $action['param']??=''], ['class' => $action['class']??='', 'title' => $action['title']??='Title',]);
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
                            if ( substr($columnName, 0, 1) == '+' || substr($columnName, 0, 1) == '~' ) {
                                $tot[$columnName] = 0;
                                $columnName = substr($columnName, 1);
                            }
                            $columnName = str_replace(array("#", "!"), '', $columnName);
                            if (substr($columnName, 0, 1) != '-') {
                                if ( isset($width[$i]) && $width[$i]!=0 ){
                                    if ( isset($ccolor[$i]) && $ccolor[$i]!='' ) {
                                        echo "<th style=\"width:".$width[$i]."px;background-color:".$ccolor[$i]."\">";
                                        $td[$i]= "<td style=\"width:".$width[$i]."px;background-color:".$ccolor[$i]."\">";
                                    } else {
                                        echo "<th style=\"width:".$width[$i]."px;\">";
                                        $td[$i]= "<td style=\"width:".$width[$i]."px;\">";
                                    }
                                }else {
                                    echo "<th>";
                                    $td[$i]= "<td>";
                                }
                                echo substr($columnName, 0, 1) != '_' ? $columnName : '&nbsp;';
                                echo "</th>";
                            }
                        }
                    } else {
                        echo "<tr><td><i>Empty result set</i></td><tr>";
                    }
                    ?>
            </thead>

            <?php
            // d($data['col']);
            // dd($td);

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
                        $i = $count - 1;
                        if ( substr($columnName, 0, 1) == '+' || substr($columnName, 0, 1) == '~' ) {
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
                                    echo "$td[$count]</td>";
                                    continue;
                                }
                            }
                            if (count($part) == 2){
                                if (substr($part[0],0,5)=='Grade') { # Only for Grade Link
                                    echo $td[$i]."<a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" title=\"Naar opdracht\" href=\"".$part[1]."\">".$part[0]."</td>";
                                }elseif (substr($part[0],0,4)=='(ac)') {
                                    echo $td[$i]."<a onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" onclick=\"working('".$part[1]."');\" title=\"Auto Correct\">".$part[0]."</td>";
                                } else { # Generic
                                    //echo "<td>" . Html::a($part[0], [$part[1]]) . "</td>";
                                    echo $td[$i]."<a href=\"".$part[1]."\">$part[0]</a></td>";
                                }
                            } elseif (count($part) == 4) { # Generic
                                preg_match('/^\((.*?)\)$/', $part[0], $matches);
                                if ( !empty($matches[1]) ) {
                                    echo $td[$i] . Html::a(substr($matches[1],0,3), [$part[1], $part[2] => $part[3]], ['title'=>$matches[1], 'class'=>'small-button']) . "</td>";
                                } else {
                                    echo $td[$i] . Html::a($link, [$part[1], $part[2] => $part[3]], ['title'=>$help]) . "</td>";
                                }
                                
                            } elseif ( count($part) == 6 ) { # Generic
                                echo$td[$initials] . Html::a($link, [$part[1], $part[2] => $part[3], $part[4] => $part[5]], ['title'=>$help] ) . "</td>";
                            } elseif ( count($part) == 7 ) { # show processing (hack)
                                echo $td[$i] . Html::a($link, [$part[1], $part[2] => $part[3], $part[4] => $part[5]], ['title'=>$help, 'onclick'=>"hide();"] ) . "</td>";
                            } else {
                                echo $td[$i]."Err: Inlvalid link data</td>";
                                echo "<pre><hr>";
                                dd( ["Err: Inlvalid link data", $item, $part] );
                            }
                        } elseif (substr($columnName, 0, 1) == '#' ) {
                            if ( $prevItem=='' || $item[$columnName] != $prevItem[$columnName] ) {
                                echo $td[$i] . $item[$columnName] . "</td>";
                            } else { 
                                echo $td[$i] . "</td>";
                            }
                            
                        } elseif ( substr($columnName, 0, 1) <> '-' ) {
                            if ( substr($columnName, 1, 1) == '+' ) {
                                echo $td[$i] .  $tot[$columnName] . "</td>";
                            } elseif( substr($columnName, 1, 1) == '~' ) {
                                echo $td[$i] .  $tot[$columnName] . "</td>";
                            } else {
                                echo $td[$i] . $item[$columnName] . "</td>";
                            }
                        }
                        
                    }
                    $prevItem=$item;
                    echo "</tr>";
                }

                if (count($tot)) {
                    echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
                    if (!isset($nocount)) {
                        echo $td[$i]."</td>";
                    }
                    $count=0;
                    foreach ($data['col'] as $columnName) {
                        if (++$count<=$from) continue;
                        if (substr($columnName, 0, 1) == '+') {
                            echo $td[$i];
                            echo number_format($tot[$columnName], 0, ',', ' ');
                            echo "</td>";
                        } elseif ( substr($columnName, 0, 1) == '~' ) {
                            echo "<td>";
                            echo number_format(($tot[$columnName]/$nr), 1, '.', ' ');
                            echo "</td>";
                        } elseif ( substr($columnName, 0, 1) <> '-' ) {
                            echo $td[$i]."</td>";
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