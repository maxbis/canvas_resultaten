<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<head>

<?= Html::csrfMetaTags() ?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script>
    $(document).ready(function(){
        $( ".on-change" ).change(function(a) {
            console.log(" --> "+JSON.stringify(a));
            console.log( "Handler for .change() called: " );
        });
    });
</script>

<script>
    function updateMessage(id, message) {
        
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var url= '<?= Url::toRoute(['/student/set-message']); ?>';
        var data = {'id': id, 'message':message, '_csrf': csrfToken};

        var payload = {
            _csrf: csrfToken,
        };

        var data = new FormData();
        data.append( "json", JSON.stringify( { _csrf: csrfToken } ) );

        jsonString = JSON.stringify(payload).replace(/\"/g, '');

        console.log(csrfToken);
        console.log("*** data: "+jsonString );

        fetch(  url,
                {   method: "POST",
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken,
                    },    
                    body: JSON.stringify( jsonString     )
                }
            )
        .then(res => {
            console.log("Request complete! response:", res);
        })
        .catch(error => {
            console.log(error);
        });
    }
   
</script>
</head>

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
        </div>
    </div>

    <p></p>

    <div class="card-body">
        <table class="table table-sm hoverTable">
            <thead>
                <tr>
                   <th>#</th>
                   <th>Student</th>
                   <th>Message</th>
                   <th>Dev Voldaan</th>
                </tr>
                <?php $cnt=0;
                    foreach ($data['row'] as $item) { 
                        $part = explode('|', $item['!Student']);
                        $part2 = explode('|', $item['!Actie']);
                ?>
                    <tr>
                    <td><?= $cnt++; ?></td>
                    <td><a href="<?= $part[1]."?".$part[2]."=".$part[3]; ?>"><?= $part[0]; ?></a></td>
                    <td><input type="text" value="<?= $item['Message']; ?>" onfocusout="updateMessage(<?=$part2[3]?>, 'xxx')" class="on-change2"></td>
                    <td><?= $item['Dev Modules Voldaan']; ?></td>
                    </tr>

                <?php } ?>

        </table>

    </div>
</div>