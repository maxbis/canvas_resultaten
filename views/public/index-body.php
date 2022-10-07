<?php
use yii\helpers\Html;
?>

<div class="card-body table-responsive hoverTable">
    <table class="table table-sm">
        <?php
            $totVoldaan = 0;
            $totOpdrachten = 0;
            $totPunten = 0;

            // header of table
            echo "<tr>";
            echo "<th class=\"\">&nbsp;</th>";
            echo "<th colspan=2>Module</th>";
            if( ! isMobileDevice() ){
                echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal en percentage ingeleverd\"\>Opdrachten</th>";
                echo "<th colspan=2 class=\"bleft\" style=\"text-align:center;\" title=\"Aantal en percentage van totaal te behalen\">Punten</th>";
                echo "<th title=\"Wanneer is er voor deze module het laatst iets ingeleverd\" class=\"tcenter bleft\">Laatst Actief</th>";
            } else {
                echo "<th colspan=3>";
            }
            echo "</tr>";

            $prevBlok = '';
            foreach ($data as $item) {
                if ($item['Voldaan'] == 'V') {
                    $totVoldaan += 1;
                }
                $totPunten += $item['Punten'];
                $totOpdrachten += $item['Opdrachten'];

                $dagen = intval((time() - strtotime($item['Laatste Actief'])) / 86400);
                if ($dagen <= 7) {
                    $title = 'Afgelopen week actief geweest';
                } elseif ($dagen <= 14) {
                    $title = 'Afgelopen twee weken actief geweest';
                } else {
                    $title = 'Activiteit langer dan twee weken geleden';
                }
                $dagen=max(0,$dagen-2);
                $daysAgoColor="rgb(253,255,".min(255,(199+$dagen*4)).")"; // color fades away from yellow as the age is older

                // Module wrap-up line, print on all reports except in the standard report
                if ( $item['Blok'] != $prevBlok && $style!='standard') {
                    if ($aggregatedData[$item['Blok']]['voldaan'] ) {
                        $done=$aggregatedData[$item['Blok']]['countVoldaan'];
                        echo "\n<tr class=\"clickable\" id=\"blok-".$item['Blok']."\"><td>&#10004;</td><td class=\"voldaan\">".$item['Blok']."</td><td class=\"voldaan\">Alle $done modules voldaan <i class=\"bi bi-emoji-smile\"></i></td><td></td><td></td><td></td><td></td><td></td></tr>";
                    } else { // niet voldaan
                        $nog = $aggregatedData[$item['Blok']]['count']-$aggregatedData[$item['Blok']]['countVoldaan'];
                        echo "\n<tr class=\"clickable\" id=\"blok-".$item['Blok']."\"><td style=\"color:#ff0000\">&#11096;</td><td class=\"niet-voldaan\">".$item['Blok']."</td><td class=\"niet-voldaan\">Nog $nog modules afronden</td><td></td><td></td><td></td><td></td><td></td></tr>";
                    }
                    $prevBlok= $item['Blok'];
                }
                
                if ( ($aggregatedData[$item['Blok']]['voldaan'] && $style=='compact') || $style=='mini') {
                    // only voldaan blok in compact tab can be clicked open: init-hide hides on load and line-blok-<block name> is used to identify line in order to show/hide
                    echo "\n<tr class=\"init-hide line-blok-".$item['Blok']."\">";
                } else {
                    // not voldaan blok stays open all the time
                    echo "\n<tr>";
                }


                if ($style!='standard') {
                    echo "<td width=60px>&nbsp;</td>";
                }

                if ($item['Minpunten'] < 0) {
                    echo "<td title=\"Module kan niet worden afgetekend, vraag docent\" width=60px class=\"\" style=\"font-weight:bolder;color:#821600;\">???</td>";
                } elseif ($item['Voldaan'] == 'V') {
                    echo "<td title=\"Voldaan (" . $item['voldaanRule'] . ")\" style=\"width:60px;color:green\" class=\"\">&#10004;</td>";
                } else {
                    echo "<td title=\"Niet voldaan (" . $item['voldaanRule'] . ")\" width=60px class=\"\">&#11096;</td>";
                }

                if ($style=='standard') {
                    echo "<td width=60px>" . $item['Blok'] . "</td>";
                }

                if( ! isMobileDevice() ){
                    echo "<td>".Html::a($item['Module'], ['/public/details-module', 'moduleId' => $item['module_id'], 'code' => $item['Code']])."</td>";
                    echo "<td class=\"tright bleft\">" . $item['Opdrachten'] . "</td>";
                    echo "<td class=\"tright bright\">" . $item['Opdrachten %'] . "%</td>";
                    echo "<td class=\"tright bleft\">" . $item['Punten'] . "</td>";
                    echo "<td class=\"tright bright\">" . $item['Punten %'] . "%</td>";
                    if (substr($item['Laatste Actief'], 0, 4) == "1970") {
                        echo "<td class=\"tcenter\"> - </td>";
                    } else {
                        echo "<td title=\"" . $title . "\" style=\"background-color:".$daysAgoColor.";\" class=\"tcenter\">" . $item['Laatste Actief'] . "</td>";
                    }
                } else {
                    echo "<td>" . $item['Module'] . "</td>";
                }
                echo "\n</tr>";
            }

            echo "<tr style=\"background-color:#e8f0ff;box-shadow: 5px 5px 5px #d0d0d0;\">";
            
            echo ($style!='standard') ?  "<td></td>" : "";
            echo "<td colspan=2><b>" .$totVoldaan . "</b></td>";
            echo ($style=='standard') ?  "<td></td>" : "";
            if( ! isMobileDevice() ){
                echo "<td class=\"tright\">". $totOpdrachten ."</td>";
                echo "<td></td>";
                echo "<td class=\"tright\">". $totPunten ."</td>";
                echo "<td></td>";
                echo "<td title=\"Deze score bepaald jouw positie in het klassement\" class=\"tright\">";
                echo "(score: ".($totVoldaan*200+$totPunten).")";

                echo "</td>";
            } else {
                echo "<td colpsan=5></td>";
            }
            echo "</tr>";
        ?>
    </table>
</div>