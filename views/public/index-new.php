<?php

//first sort the data to make sure you can show it in the right order and with the right colors
$countV = 0;
$counter = 0;
$blockName = "first";
$blockArray = [];
$rowClass = "";
$setView = "";

foreach ($data as $value) {

    if ($blockName == "first" || $blockName != $value["Blok"]) {
        $blockName = trim($value["Blok"], " ");
        array_push($blockArray, $blockName);
        ${"status_" . $blockName} = false;
        ${"voldaan_" . $blockName} = "";
        ${"content_" . $blockName} = "";
        ${"header_" . $blockName} = "";

        //reset values
        $countV = 0;
        $counter = 0;
    }

    if ($value["Voldaan"] == "V") {
        $countV++;
        $rowClass = "success";
    } elseif ($value["Punten %"] > 50) {
        $rowClass = "warning";
    } elseif ($value["Punten %"] < 50) {
        $rowClass = "danger";
    }

    $date = date("d/m/y H:i", strtotime($value["Laatste Actief"]));

    ${"content_" . $blockName} .= "<tr class='{$rowClass}' data-setcolor='false'>
                                                <th width='3%' class='info'>" . ($value["Voldaan"] == "V" ? "<i class='bi bi-check-lg text-success'></i>" : "<i class='bi bi-x-lg text-danger'></i>") . "<span class='tooltiptext bg-{$rowClass}'>{$value['voldaanRule']}</span></th>
                                                <td width='6%'>{$value["Blok"]}</td>
                                                <th width='26%'>{$value["Module"]}</th>
                                                <td width='22%'>{$value["Opdrachten"]} / {$value["aantal_opdrachten"]} ({$value["Opdrachten %"]}%)</td>
                                                <td width='22%'>{$value["Punten"]} ({$value["Punten %"]}%)</td>
                                                <td width='18%'>{$date}</td>"
        . ($counter == 0 ? "<td width='3%' rowspan='{$counter}' class='align-middle text-right bg-light'><i class='bi bi-chevron-double-up' id='btn-show_{$blockName}'></i></td></tr>" : "</tr>");

    //<i class="bi bi-info-circle"></i>

    $counter++;
    ${"voldaan_" . $blockName} = "{$countV} van de {$counter} modules voldaan";
    $lastRow = "</tr>";

    //before changing blockName check the counter to see if Voldaan = true
    if ($blockName != "first" && $countV == $counter) {
        ${"header_" . $blockName} = "bg-success";
        ${"status_" . $blockName} = true;
    } elseif ($blockName != "first" && $countV == 0 || $blockName != "first" && ($countV * 100) / $counter < 50) {
        ${"header_" . $blockName} = "bg-danger";
        ${"status_" . $blockName} = false;
    } elseif ($blockName != "first" && ($countV * 100) / $counter >= 50) {
        ${"header_" . $blockName} = "bg-warning";
        ${"status_" . $blockName} = false;
    }
}

function isMobileDevice()
{

    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                        tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset($_GET['mobile']);
}

?>

<section>
    <div class="row mb-2">
        <div class="col">
            <h3>Voortgangsoverzicht van <br><?= $data[0]['Student'] ?></h3>
            <small style="color:#999;">Bijgewerkt tot: <?= $timeStamp ?></small>
        </div>
        <div class="col">
            <?php

            use scotthuangzl\googlechart\GoogleChart;

            if (!isMobileDevice()) {
                if (gettype($chart) == 'array' && count($chart) > 1) {
                    echo "<br>";
                    echo GoogleChart::widget($chart);
                }
            }
            ?>
        </div>
    </div>
</section>

<section id="results_student" data-setcolor="false">
    <div class="row">
        <div class="col">
            <div class=" card card-body px-1 pt-1 pb-0 mb-0">
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td scope="col" width="9%" colspan="2" class="text-center"><small><a href="#" id="btn-color" class="text-info">show color</a></small></td>
                                <th scope="col" width="26%">Module</th>
                                <th scope="col" width="22%">Opdrachten</th>
                                <th scope="col" width="22%">Punten</th>
                                <th scope="col" width="18%">Laatste actie</th>
                                <th scope="col" width="3%"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php
                foreach ($blockArray as $item) { ?>

                    <div class="table-responsive table-header mb-0" id="<?php echo "tb-heading_{$item}"; ?>" data-show="<?php echo (${'status_' . $item} == false ? 'false' : 'true'); ?>">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr class="table-header <?php echo ${'header_' . $item}; ?>" data-setcolor="false">
                                    <th scope="col" width="3%"><?php echo (${'status_' . $item} == true ? "<i class='bi bi-check-lg text-success'></i>" : "<i class='bi bi-x-lg text-danger'></i>"); ?></th>
                                    <th scope="col" width="6%"><?php echo $item; ?></th>
                                    <th scope="col" width="88%"><?php echo ${'voldaan_' . $item}; ?></th>
                                    <th scope="col" width="3%" class='align-middle text-right'><i class="bi bi-chevron-double-down" id=<?php echo "btn-hide_{$item}"; ?>></i></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive table-results" id="<?php echo "tb-body_{$item}"; ?>" data-show="<?php echo (${'status_' . $item} == false ? 'true' : 'false'); ?>">
                        <table class="table table-sm mb-0 table-bordered">
                            <tbody>
                                <?php echo ${"content_" . $item}; ?>
                            </tbody>
                        </table>
                    </div>
                <?php

                    $setView .= '
                $("#btn-hide_' . $item . '").click(function() { 
                    $("#tb-body_' . $item . '").attr("data-show","true"); 
                    $("#tb-heading_' . $item . '").attr("data-show","false");
                });
                $("#btn-show_' . $item . '").click(function() { 
                    $("#tb-body_' . $item . '").attr("data-show","false"); 
                    $("#tb-heading_' . $item . '").attr("data-show","true");
                });';
                } // end foreach 
                ?>
            </div> <!-- end card body -->
        </div> <!-- end col -->
    </div> <!-- end row -->
</section> <!-- end section -->

<?php
$setView .= '$("#btn-color").click(function() {
    if($("#results_student").attr("data-setcolor") == "false"){
        $("tr").attr("data-setcolor", "true");
        $("#results_student").attr("data-setcolor", "true");
        $("#btn-color").html("verberg kleur");
    } else {
        $("tr").attr("data-setcolor", "false");
        $("#results_student").attr("data-setcolor", "false");
        $("#btn-color").html("toon kleur");
    }
    });';

if ($setView != "") {
    $this->registerJs($setView);
}
?>