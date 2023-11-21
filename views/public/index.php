<?php
    use yii\helpers\Html;
    use yii\helpers\Url;

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
    $header      = $this->render( 'index-header', ['data' => $data,'timeStamp' => $timeStamp, 'rank' => $rank, 'pogingen' => $pogingen, 'minSubmitted'=> $minSubmitted, 'chart' => $chart]);
    $bodyCompact = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'compact' ]);
    $bodyFull    = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'mini' ]);
    $bodyStandard= $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'standard' ]);
    $bodyTodo    = $this->render( 'index-body',   ['data' => $data,'timeStamp' => $timeStamp, 'aggregatedData'=>$aggregatedData, 'style'=>'todo' ]);
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
    .grey { color:#808080;}

    .top{
        display:flex;
        flex-direction:row;
        align-items:top;
        height:35px;
        width:120px;
        margin-top:25px;
    }

    .ranking {
        display:none;
    }

    .klas { font-size:12px;color:#999;}

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

<script>
    $(function() {
        var ranking = localStorage.getItem('Ranking');

        if ( ranking == 1 ) {
            $('.ranking').show();
            $('.top').css({backgroundColor:'#FFFFFF'});
            localStorage.setItem('Ranking', 1);
        } else {
            $('.top').css({backgroundColor:'#FAFAFA'});
        }
    });

    $(document).ready(function(){
        var oldValue="";
        $('#ranking').click(function() {
            if ( $(".ranking").is(':visible') ) {
                $('.ranking').hide();
                $('.top').css({backgroundColor:'#FAFAFA'});
                localStorage.setItem('Ranking', 0);
            } else {
                $('.ranking').show(500);
                $('.top').css({backgroundColor:'#FFFFFF'});
                localStorage.setItem('Ranking', 1);
            }
        });
        $('.editable').focus(function(){
            //console.log("Focus");
            oldValue=$(this).html();
            $(".editable").attr("style", "font-style: normal;font-size:18px;")
        });
        $('.editable').blur(function(){
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var url= '<?= Url::toRoute(['/student/set-message']); ?>';
            var myId=$(this).attr('id');;
            var myMessage=$(this).html();
            myMessage = myMessage.replace(/<[^>]*>?/gm, ''); // filter html code
            
            if (oldValue!=myMessage) {
                changedValue=0;
               // console.log("Update id:"+myId+" with message:"+myMessage.trim() );
            
                $.ajax({
                    type: 'post',
                    url:  url,
                    data: '_csrf=' +csrfToken+"&id="+myId+"&message="+myMessage
                });
                // console.log("DB Updated");
            }
            $(".editable").attr("style", "font-style: italic;font-size:16px;")
        });
    });
</script>


<div style="float:right">
<?php use app\controllers\CheckInController; echo CheckInController::button($_GET["code"]); ?>
</div>

<div class="m-4">

    <ul class="nav nav-tabs" id="myTab">
        <li class="nav-item"><a href="#standard" class="nav-link" data-toggle="tab" title="Laat alle modules van alle blokken zien">Standard</a></li>
        <li class="nav-item"><a href="#compact" class="nav-link" data-toggle="tab" title="Blokken die geheel zijn voldaan zijn dichtgeklapt">Compact</a></li>
        <li class="nav-item"><a href="#mini" class="nav-link" data-toggle="tab" title="Alle blokken zijn dichtgeklapt">Mini</a></li>
        <li class="nav-item"><a href="#todo" class="nav-link" data-toggle="tab" title="Laat alleen de modules zien die nog niet zijn voldaan">Todo</a></li>
        <!-- <li class="nav-item"><a href="#prefs" class="nav-link" data-toggle="tab">Settings</a></li> -->
    </ul>
    
    <div class="card shadow table-responsive">
        <div class="col">
            <div class="tab-content">
                <?= $header; ?>
                <div class="tab-pane show active" id="standard"><?= $bodyStandard; ?></div>
                <div class="tab-pane" id="compact"><?= $bodyCompact; ?></div>
                <div class="tab-pane" id="mini"><?= $bodyFull; ?></div>
                <div class="tab-pane" id="todo"><?= $bodyTodo; ?></div>
                <!-- <div class="tab-pane" id="prefs"><?= $bodyPrefs; ?></div> -->
            </div>
        </div>
    </div>
    
</div>

<br>

<?php
    $link = Html::a("score/aantal", [preg_replace('/\&score=.?/','',Yii::$app->request->url)."&score=".abs($score-1) ], ['title'=> 'Toggle graph (aantal ingeleved - score)',]);
?>

<small style="color:#b0b0b0;font-style: italic;">
    <details>
        <summary>Disclaimer/footer</summary>
        <p>De groene vinkjes geven aan of een module is voldaan. Behoudens technische storingen of configuratiefouten zijn de resultaten uit dit overzicht leidend.</p>

        <?php if (! Yii::$app->user->isGuest ) { ?>
            <ul>
                <li><a href="https://talnet.educus.nl/app/deelnemer/Deelnemerkaart/<?=$data[0]['student_nummer']?>" target="_blank">Eduarte (indien toegang)</a></li>
                <li><a href="https://talnet.educus.nl/app/deelnemer/Maandoverzicht/<?=$data[0]['student_nummer']?>" target="_blank">Absentie maandoverzicht (indien toegang)</a></li>
            </ul>
        <?php } ?>

        <?php
            // if ( $prediction ) {
            //     echo "<p><table style=\"color:#D0D0D0;\">";
            //     foreach($prediction as $key => $value) {
            //         echo "<tr>\n";
            //         $color="#808080";
            //         $fontWeight = 500;
            //         if ( $key == "week/mod" ) {
            //             $fontWeight = 700;
            //             if ( $value > 2.0 ) {
            //                 $color = "#800000";
            //             } elseif ( $value > 1.7 ) {
            //                 $color = "#ff0000";
            //             } elseif ($value > 1.6) {
            //                 $color = "#FFA500";
            //             } elseif ($value > 1) {
            //                 $color = "#008000";
            //             } else {
            //                 $color = "#66CDAA";
            //             }
            //         }
            //         echo "<td>$key&nbsp;&nbsp;&nbsp;</td><td style=\"font-weight:$fontWeight;color:$color\">$value</td>";
            //         echo "</tr>\n";
            //     }
            //     echo "</table><p>";
            // }
        if ( $prediction ) {
            echo "<p>Je hebt <b>".$prediction['percCompleted']."%</b> van de eerste 1.5 jaar afgerond. ";
            echo "Naar verwachting kan jij op <b>".$prediction['predictedDate']."</b> op stage.";
            echo "<br>Volgens deze indicatie zou je studie naar verwachting <b>".$prediction['studieDuur']."</b> jaar duren.<p>";
        }
        ?>
        <p>flip <?=$link?> (laat ingeleverd werk óf behaalde punten zien)</p>
        <p>v 2.12.02 &copy; ROCvA MaxWare :) <?= date('Y') ?>, <?= Yii::powered() ?></p>
    </details>
</small>

</div>

