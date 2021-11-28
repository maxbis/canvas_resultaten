<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;

?>

<div class="card">


    <div class="container">
        <div class="row  align-items-center">
            <div class="col">
                <h1><?= ($data['title']) ?></h1>
                    <?php
                        if (isset($descr)) {
                            echo "<small>".$descr."</small>";
                        }
                    ?>
                </div>
            <div class="col-md-auto">
                <?php $params = isset($params) ? $params : ''; ?>
                <?= Html::a('Export', [$action.'?export=1&'.$params], ['class'=>'btn btn-primary', 'title'=> 'Export to CSV',]) ?>
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
                            for($i=$from;$i<count($data['col']);$i++) {
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
                        echo "<tr>";
                        if ( ! isset($nocount) ) {
                            $nr++;
                            echo "<td>".$nr."</td>";
                        }
                        for($i=$from;$i<count($data['col']);$i++) {
                            if (substr($item[$data['col'][$i]],0,4)=='http') {
                                echo "<td><a href=\"".$item[$data['col'][$i]]."\">Link</a></td>";
                            } elseif( $item[$data['col'][$i]]=='1970-01-01 00:00:00') {
                                echo "<td>-</td>";
                            } else {
                                echo "<td>".$item[$data['col'][$i]]."</td>";
                            }   
    
                        }
                        echo "</tr>";
                    }
                }

            ?>

        </table>
    </div>
</div>