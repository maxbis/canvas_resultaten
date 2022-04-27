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
$header      = $this->render( 'index-header', ['data' => $data,'timeStamp' => $timeStamp, 'rank' => $rank,'chart' => $chart,]);
$bodyCompact = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'compact']);
$bodyFull    = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'full']);
$bodyStandard= $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'standard']);

?>

<style>
    .numberCircle {
        border-radius:50%;width:32px;height:32px;padding:4px;
        background #a3586d;color:#ffffff;text-align:center;
        font:12 Arial,sans-serif;
    }

    .bleft  { border-left:dashed 1px #c0c0c0; }
    .bright { border-right:dashed 1px #c0c0c0; }
    .tleft  { text-align:left; }
    .tright { text-align:right; }
    .tcenter { text-align:center; }
    .hoverTable tr:hover td { background-color: #f6f6ff; }
    .recent7  { background-color:#fdffe3; }
    .recent14 { background-color:#fff8e3; }
    .voldaan  { background-color:#f8fff8; }
    .niet-voldaan { background-color:#fff4f4; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#myTab a[href="' + activeTab + '"]').tab('show');
    } else {
        $('#myTab a[href="#standard"]').tab('show');
    }
});
</script>

<div class="m-4">
    <ul class="nav nav-tabs" id="myTab">
        <li class="nav-item">
            <a href="#standard" class="nav-link" data-toggle="tab">Standard</a>
        </li>
        <li class="nav-item">
            <a href="#compact" class="nav-link" data-toggle="tab">Compact</a>
        </li>
        <li class="nav-item">
            <a href="#full" class="nav-link" data-toggle="tab">Full</a>
        </li>
    </ul>
    
    <div class="card shadow table-responsive">

        <div class="col">
            <div class="tab-content">
                <?= $header; ?>
                <div class="tab-pane show active" id="standard">
                    <?= $bodyStandard; ?>
                </div>
                <div class="tab-pane" id="compact">
                    <?= $bodyCompact; ?>
                </div>
                <div class="tab-pane" id="full">
                    <?= $bodyFull; ?>
                </div>
            </div>
        </div>

    </div>
</div>


<br>

<small style="color:#b0b0b0;font-style: italic;">
    <details>
        <summary>Disclaimer/footer</summary>
        <p>Aan dit overzicht kunnen geen rechten worden ontleend. De gegevens in Canvas zijn leidend.</p>
        <p>v 2204.27 &copy; ROCvA MaxWare :) <?= date('Y') ?>, <?= Yii::powered() ?></p>
    </details>
</small>

