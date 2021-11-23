<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
?>

<div class="card"  style="width: 900px">

    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h1><?= Html::encode($data['title']) ?></h1>
                    <?php
                        if (isset($descr)) {
                            echo "<small>".$descr."</small>";
                        }
                    ?>
                </div>
            <div class="col-md-auto">
                <?= Html::a('Export', [$action.'?export=1'], ['class'=>'btn btn-primary', 'title'=> 'Export to CSV',]) ?>
            </div>
        </div>
    </div>

    <p></p>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <?php
                        if ( ! isset($nocount) ) echo "<td>#</td>";
                        if ( $data['row'] ) {
                            for($i=0;$i<count($data['col']);$i++) {
                                echo "<th>".$data['col'][$i]."</th>";
                            }
                        } else {
                            echo "<td>Empty result set</td>";
                        }
                    ?>
            </thead>
            
            <?php
                if ( $data['row'] ) {
                    foreach($data['row'] as $item) {
                        if ( ! isset($nocount) ) {
                            $nr++;
                            echo "<tr>";
                            echo "<td>".$nr."</td>";
                        }
                        for($i=0;$i<count($data['col']);$i++) {
                            echo "<td>".$item[$data['col'][$i]]."</td>";
    
                        }
                        echo "</tr>";
                    }
                }

            ?>

        </table>
    </div>
</div>