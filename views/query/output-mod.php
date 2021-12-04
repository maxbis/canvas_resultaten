<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$studentNummer = isset($studentNummer) ? $studentNummer : '';
$moduleId = isset($moduleId) ? $moduleId : '';
?>

<div class="card">


    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h1><?= ($data['title']) ?></h1>
                    <?php
                        if (isset($descr)) {
                            echo "<small>".$descr."</small>";
                        }
                    ?>
                </div>
            <div class="col-md-auto">
                <?= Html::a('Export', [$action, 'export'=>1, 'studentNummer'=>$studentNummer, 'moduleId'=>$moduleId], ['class'=>'btn btn-primary', 'title'=> 'Export to CSV',]) ?>
            </div>
        </div>
    </div>

    <p></p>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <?php
                        if ( ! isset($nocount) ) echo "<td>#</td>";
                        if ( $data['row'] ) {
                            for($i=$from;$i<count($data['col']);$i++) {
                                echo "<th>".$data['col'][$i]."</th>";
                            }
                        } else {
                            echo "<td>Empty result set</td>";
                        }
                    ?>
            </thead>
            
            <?php
                // this view is used for query/details-module $action='details-module'
                // this view is used for query/student $action='student'
                 $totScore=0;
                 $totGraded=0;
                 $tot=0;
                 $totVoldaan=0;
                 $totOpdrachten=0;
                 $totPrPunten=0;
                 $totPrAantal=0;

                if ( $data['row'] ) {
                    foreach($data['row'] as $item) {
                        if ( array_key_exists('Opdrachtnaam', $item) && str_contains( strtolower($item['Opdrachtnaam']),'eind') ) {
                            echo "<tr style=\"background-color:#ffffde\">";
                        } else {
                            echo "<tr>";
                        }
                        $tot+=1;
                        if ( ! isset($nocount) ) {
                            $nr++;
                            echo "<td>".$nr."</td>";
                        }
                        for($i=$from;$i<count($data['col']);$i++) {
                            $colContent = $item[$data['col'][$i]];
                            $colName    = $data['col'][$i];

                            if($colName=='Laatste Actief' ){
                                $dagen=intval((time()-strtotime($item['Laatste Actief']))/86400);
                                if ($dagen<=7) {
                                    $color='#fdffe3';
                                } elseif ($dagen<=14) {
                                    $color='#fff8e3';
                                } else {
                                    $color='#ffffff';
                                }
                                echo "<td style=\"background-color:".$color."\">".$colContent."</td>";
                            } elseif (substr( $colContent,0,4)=='http' ) {
                                $link = substr( $colContent , 0, strpos( $colContent , "?") ) ;
                                echo "<td><a target=_blank onmouseover=\"this.style.background='yellow'\" onmouseout=\"this.style.background='none'\" href=\"".$link."\">&#10142;Canvas</a></td>";
                            } elseif( $colContent=='1970-01-01 00:00:00') {
                                echo "<td>-</td>";
                            } elseif ( $colName == 'Module' ) {
                                echo "<td>";
                                echo Html::a($colContent, ['/query/details-module', 'moduleId'=>$item[$data['col'][0]], 'studentNummer'=>$studentNummer ] );
                                // echo "<a href=\"/query/details-module?$params&moduleId=".$item[$data['col'][0]]."\">".$colContent."</a>";
                                echo "</td>";
                            } else {
                                echo "<td>".$colContent."</td>";
                            }
                            
                            if ( $colName == "Score" ) {
                                $totScore+=$colContent;
                            }
                            if ( $colContent == "graded" || $colContent == "submitted" ) {
                                $totGraded+=1;
                            }
                            if ( $colContent == "V" ) {
                                $totVoldaan+=1;
                            }                  
                            if ( $colName == "Opdrachten" ) {
                                $totOpdrachten+=$colContent;
                            }
                            if ( $colName == "Punten %" && $colContent>0 ) {
                                $totPrPunten+=$colContent;
                                $totPrAantal+=1;
                            }    
                        }
                        echo "</tr>";
                    }
                }
                if ($action=='details-module') {
                    echo "<tr style=\"background-color:#e8f0ff\">";
                    echo "<td><b>TOTAAL $tot opdrachten</b></td>";
                    echo "<td><b>$totGraded</b></td>";
                    echo "<td></td>";
                    echo "<td><b>$totScore</b></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "</tr>";
                }
                if ($action=='student') {
                    echo "<tr style=\"background-color:#e8f0ff\">";
                    echo "<td><b>TOTAAL / GEMIDDELD</b></td>";
                    echo "<td></td>";
                    echo "<td><b>$totVoldaan</b></td>";
                    echo "<td>$totOpdrachten</td>";
                    echo "<td>".round($totPrPunten/$totPrAantal,1)."</td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "</tr>";
                    // Html::a('&#x21BA', ['resultaat/update-assignment', 'student_nr'=>$data->student_nummer, 'module_id'=>$data->module_id], ['title'=> 'Update uit Canvas','onclick'=>'hide()']);
                }

            ?>

        </table>
    </div>
</div>