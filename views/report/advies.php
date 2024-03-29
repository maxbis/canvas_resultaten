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
            document.execCommand('selectAll', false, null);
            // var myId=$(this).attr('id');
            oldValue=$(this).html();
        });
        $('.editable').blur(function(){
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var url= '<?= Url::toRoute(['/student/set-message']); ?>';
            var myId=$(this).attr('id');
            var parts = myId.split('-'); // splits the string into an array: ["comment", "1234"]
            myField = parts[0];
            myId = parts[1]; 
            
            var myMessage=$(this).html();
            myMessage = myMessage.replace(/<[^>]*>?/gm, ''); // filter html code
            
            if (oldValue!=myMessage) {
                changedValue=0;
                console.log("Update id:"+myId+" with message:"+myMessage.trim()+" myField:"+myField );
            
                $.ajax({
                    type: 'post',
                    url:  url,
                    data: '_csrf=' +csrfToken+"&id="+myId+"&message="+myMessage+"&field="+myField
                });
                console.log("Ajax call sent");
            }

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

        let formData = new FormData();
        formData.append('_csrf', csrfToken);

        console.log(csrfToken);
        console.log("*** data: "+jsonString );

        fetch(  url,
                {   method: "POST",   
                    body: new URLSearchParams("_csrf="+csrfToken),
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
            <div class="col-md-auto">
                <?php
                    if ( isset($action)) {
                         echo Html::a('Export', [$action['link'] .'?'. $action['param']??=''], ['class' => $action['class']??='', 'title' => $action['title']??='Title',]);
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
                   <th style="color:#A0A0A0;">#</th>
                   <th title="Aantal modules voldaan">V</th>
                   <th title="Aantal opdrachten ingeleverd">O</th>
                   <th title="Studentnaam">Student</th>
                   <th title="Tekst zichtbaar voor studenten" style="width:600px;">Advies</th>
                   <th title="Tekst niet zichtbaar voor studenten" style="width:200px;">Label/Code</th>
                </tr>
                <?php
                    if (isset($data['row'])) {
                        $cnt=0;
                        foreach ($data['row'] as $item) {
                            $message = $item['message'];
                            if ($message=="") $message="-";
                            $comment = $item['comment'];
                            if ($comment=="") $comment="-";
                ?>
                        <tr>
                            <td style="color:#A0A0A0;"><?= $cnt++; ?></td>
                            <td><?= $item['voldaan']; ?></td>
                            <td><?= $item['ingeleverd']; ?></td>
                            <td><a href="/public/index?code=<?=$item['code']?>"><?= $item['name']; ?></a></td>
                            <td><span style="" class="editable" contentEditable="true" id="message-<?=$item['id']?>"><?= $message; ?></span></td>
                            <td><span style="" class="editable" contentEditable="true" id="comment-<?=$item['id']?>"><?= $comment; ?></span></td>
                        </tr>
                        </tr>

                <?php 
                        }
                    } else {
                        echo "<td><i>Empty result set</i></td>";
                    }
                ?>

        </table>

    </div>
</div>