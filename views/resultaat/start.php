<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;

function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                        tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset($_GET['mobile']);
}
$subDomain=Yii::$app->params['subDomain'];
?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
    .bottom-button {
        padding: 0.375rem 0.75rem;
        font-size: 0.7em;
        text-align: center;
        cursor: pointer;
        color: #404040;
        font-weight: 400;
        background-color: #f8f9fa;
        border: solid 1px;
        border-color: #d0d0d0;
        border-radius: 0.25rem;
        margin: 2px;
        width: 120px;
    }
    .bottom-button:hover {background-color: #e6f1ff; text-decoration:none;}

    .bottom-button:active {
        background-color: #d0d0d0;
    }
    .top{
        display:flex;
        flex-direction:row;
        justify-content: space-between;
        align-items:top;
        height:100x;
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left:25px;
        margin-right:20px;
    }
    .my-header{
        background-color: #f7f7f7;
        border-bottom: 1px solid #e7e7e7;
    }
    .start-column {
        display:flex;
        flex-direction:row;
        justify-content: space-between;
        max-width: 850px;
    }
    h5 {
        color:#606060;
    }
    th, .sub {
        font-weight: 400;
        font-size: 0.8em;
        color:#999;
    }
    .ac-button {
        display: inline-block;
        padding: 1px 2px;
        font-size: 0.7em;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: rgba(0, 123, 255, 0.25);;
        border: none;
        border-radius: 4px;
        transition: background-color 0.1s ease;
        margin-top: 0px;
    }

    .ac-button:hover, .regular-link:hover {
        background-color: #ffdd00;
        color:#000000;
    }

</style>


<?php if (!$resultaten && !isMobileDevice() ): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var apiURL= '<?= Url::toRoute(['resultaat/ajax-nakijken']); ?>';
        jQuery(window).on('load', function () {
            jQuery.ajax({
                type: 'POST',
                data: { '_csrf': csrfToken },
                dataType: 'html',
                url: apiURL,
                success: function(data){
                    document.getElementById('nakijken').innerHTML = data;
                },
                error: function(data){
                    console.log('Ajax Error');
                    console.log(data);
                }
            });
        });
    </script>
<?php endif; ?>

<p><small style="color:#999;">Laatste update: <?= $timestamp ?></small><p>

    <div class="start-column">
        <div>
            <div class="card" style="width: 22rem;">
                <div class="my-header align-items-start">
                    <div class="top"><h5>Zoek student</h5></div>
                </div>

                <div class="list-group-item">
                    <form method="post" action=<?php Url::toRoute(['resultaat/start']); ?> >
                        <table>
                            <tr>
                                <td><input type="text" minlength="2" name="search"></td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td><input type="submit" value="Zoek" class="btn btn-primary btn-sm" title="Zoek student"><td>
                            </tr>
                        </table>
                        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
                        <div class="sub">Type een deel van de voor- of achternaam</div>
                </div>

                <?php
                    if ($resultaten && $found>0) {
                        echo "<table class=\"table table-sm hoverTable\">";
                        foreach ($resultaten as $resultaat) {
                            echo "<tr><td>&nbsp;</td><td>";
                            echo Html::a($resultaat->name." (".$resultaat->klas.")", ['/public/index', 'code'=>$resultaat->code], ['title'=> 'Naar overzicht van '.$resultaat->name.'',
                                'onmouseover'=>"this.style.background='yellow'", 'onmouseout'=>"this.style.background='none'"]);
                            echo "</td></tr>";
                        }
                        echo "</table>";
                    }
                ?>
            </div>
        </div>

        <div>&nbsp;&nbsp;&nbsp;</div>

        <?php if (!$resultaten && !isMobileDevice() ): // show nakijken section if not on mobile and no studentlist is shown ?> 
     
            <div class="card" style="width: 22rem;">
                <div class="my-header">
                    <div class="top">
                        <h5>Nakijken</h5>
                        <div>
                            <a href="/grade/not-graded" class="bottom-button" title="Nakijkoverzicht van dit cohort"><?= $subDomain?></a>
                            <a href="/grade/not-graded-all" class="bottom-button"  title="Nakijkoverzicht van alle cohorten">Alles</a>
                        </div>
                    </div>
                </div>
                <div><table id="nakijken" class="table table-sm hoverTable"></table></div>
                
            </div>
            <?php endif; ?>
    </div> <!-- end main flex-box -->
 