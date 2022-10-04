<?php

    function isMobileDevice() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                            tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset($_GET['mobile']);
    }

    $prevBlok="";
    $aggregatedData=[];
    foreach ($data as $item) {
        if ( $item['Blok']!=$prevBlok) {
            if ( $prevBlok!="" ) {
                $aggregatedData[$prevBlok]['count']=$aantalModulesInBlok;
                $aggregatedData[$prevBlok]['countVoldaan']=$aantalVoldaanInBlok;
                $aggregatedData[$prevBlok]['voldaan']= ($aantalModulesInBlok==$aantalVoldaanInBlok) ? 1 : 0;
            }
            $prevBlok=$item['Blok'];
            $aantalModulesInBlok=0;
            $aantalVoldaanInBlok=0;
        } 
        $aantalModulesInBlok++;
        if ( $item['Voldaan']=='V') {
            $aantalVoldaanInBlok++;
        }   
    }
    $aggregatedData[$prevBlok]['count']=$aantalModulesInBlok;
    $aggregatedData[$prevBlok]['countVoldaan']=$aantalVoldaanInBlok;
    $aggregatedData[$prevBlok]['voldaan']= ($aantalModulesInBlok==$aantalVoldaanInBlok) ? 1 : 0;

    // render page elements
    $header      = $this->render( 'index-header', ['data' => $data,'timeStamp' => $timeStamp, 'rank' => $rank, 'pogingen' => $pogingen, 'minSubmitted'=> $minSubmitted, 'chart' => $chart,]);
    $bodyCompact = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'compact']);
    $bodyFull    = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'mini']);
    $bodyStandard= $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'standard']);
    $bodyPrefs   = "";
    $achievements= "";

?>

<style>
    .numberCircle {
        border-radius:50%;width:32px;height:32px;padding:4px;
        background:#a3586d;color:#ffffff;text-align:center;
        font:12 Arial,sans-serif;
    }
    .star-yellow {
        color:#ebb134;
        font-size: 120%;
    }
    .star-red {
        color:#a3586d;
        font-size: 120%;
    }

    .bleft  { border-left:dashed 1px #c0c0c0; }
    .bright { border-right:dashed 1px #c0c0c0; }
    .tleft  { text-align:left; }
    .tright { text-align:right; }
    .tcenter { text-align:center; }
    .hoverTable tr:hover > td { background-color: #f6f6ff !important; }
    .voldaan  { background-color:#f8fff8; }
    .niet-voldaan { background-color:#fff4f4; }

    .top{
        display:flex;
        flex-direction:row;
        align-items:top;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        // remember last tab selected when clicked on tab
        $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTab', $(e.target).attr('href'));
        });

        // make some tr lines clickable (show/hide)
        $(document).on("click", ".clickable", function() {
            thisBlok = $(this).closest('tr').attr('id');
            $(".line-"+thisBlok).toggle();
        });

        // get stored last viewed tab
        var activeTab = localStorage.getItem('activeTab');
        if(activeTab){
            $('#myTab a[href="' + activeTab + '"]').tab('show');
        } else {
            $('#myTab a[href="#standard"]').tab('show');
        }

        // hide all classes init-hide
        $(".init-hide").hide();
    });
</script>

<div style="float:right">
<?php use app\controllers\CheckInController; echo CheckInController::button($_GET["code"]); ?>
</div>

<div class="m-4">

    <ul class="nav nav-tabs" id="myTab">
        <li class="nav-item"><a href="#standard" class="nav-link" data-toggle="tab">Standard</a></li>
        <li class="nav-item"><a href="#compact" class="nav-link" data-toggle="tab">Compact</a></li>
        <li class="nav-item"><a href="#mini" class="nav-link" data-toggle="tab">Mini</a></li>
        <!-- <li class="nav-item"><a href="#prefs" class="nav-link" data-toggle="tab">Settings</a></li> -->
    </ul>
    
    <div class="card shadow table-responsive">
        <div class="col">
            <div class="tab-content">
                <?= $header; ?>
                <div class="tab-pane show active" id="standard"><?= $bodyStandard; ?></div>
                <div class="tab-pane" id="compact"><?= $bodyCompact; ?></div>
                <div class="tab-pane" id="mini"><?= $bodyFull; ?></div>
                <!-- <div class="tab-pane" id="prefs"><?= $bodyPrefs; ?></div> -->
            </div>
        </div>
    </div>
    
</div>

<br>

<small style="color:#b0b0b0;font-style: italic;">
    <details>
        <summary>Disclaimer/footer</summary>
        <p>De groene vinkjes geven aan of een module is voldaan.<br>Behoudens technische storingen of configuratiefouten zijn de resutlaten uit dit overzicht leidend.</p>
        <p>v 3.09 &copy; ROCvA MaxWare :) <?= date('Y') ?>, <?= Yii::powered() ?></p>
    </details>
</small>