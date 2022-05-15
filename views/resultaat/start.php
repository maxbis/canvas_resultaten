<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;

function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                        tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset($_GET['mobile']);
}

?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
</style>

<?php if (!$resultaten && !isMobileDevice() ): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        jQuery(window).on('load', function () {
            jQuery.ajax({
                type: 'POST',
                data: { '_csrf': csrfToken },
                dataType: 'html',
                url: '/resultaat/ajax-nakijken',
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

<small style="color:#999;">Bijgewerkt tot: <?= $timestamp ?></small><br/>

<table class="table table-borderless"><tr>

    <td>
        <div class="card" style="width: 22rem;">
            <div class="card-header align-items-start">
            <h5>Zoek student</h5>
            </div>

            <div class="list-group-item">
                <form method="post" action=<?php Url::toRoute(['product/start']); ?> >
                    <table><tr><td><input type="text" minlength="2" name="search"></td><td>&nbsp;</td><td><input type="submit" value="Zoek" class="btn btn-primary btn-sm"><td></tr></table>
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
                    <div style="color:#999;">Type een deel van de voor- of achternaam</div>
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
    </td>

    <?php if (!$resultaten && !isMobileDevice() ): // show nakijken section if not on mobile and no studentlist is shown ?> 
        <td>
            <div class="card" style="width: 22rem;">
                <div class="card-header">
                    <h5>Nakijken</h5>
                </div>
                <table id="nakijken" class="table table-sm hoverTable"></table>
            </div>
        </td>
    <?php endif; ?>

</tr></table>