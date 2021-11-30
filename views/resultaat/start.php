<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Zoek student';

?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <div style="color:#999;">
        </br>
        <p>Type een deel van de voor- of achternaam</p>
        
    </div>
   

    <div class="row">

        <div class="card" >
            <div class="card-header align-items-start">
                <form action="resultaat/start" method="post">
                    <input type="text" minlength="2" name="search">
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
                    &nbsp; &nbsp; &nbsp;
                    <input type="submit" value="Zoek" class="btn btn-primary btn-sm">
                </form>
            </div>
            <?php if ($resultaten && $found>0): ?>
                <ul class="list-group list-group-flush">
                    <?php
                        foreach ($resultaten as $resultaat) {
                            echo "<li class=\"list-group-item\">";
                            echo Html::a($resultaat->student_naam." (".$resultaat->klas.")", ['/query/student', 'studentNummer'=>$resultaat->student_nummer], ['title'=> 'Naar overzicht van '.$resultaat->student_naam.'',
                                'onmouseover'=>"this.style.background='yellow'", 'onmouseout'=>"this.style.background='none'"]);
                            echo "</li>";
                        }

                    ?>
                </ul>
            <?php endif; ?>
            <?php if ($found==0): ?>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        -- niets gevonden --
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
 
</div>

<p></br></br></br></p>
