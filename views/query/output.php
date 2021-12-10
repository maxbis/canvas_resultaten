<?php
use yii\helpers\Url;
use yii\helpers\Html;
$nr=0;
$from = isset($data['show_from']) ? $data['show_from'] : 0;
$tot=[];
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
                            for($i=$from;$i<count($data['col']);$i++) {
                                $columnName=$data['col'][$i];
                                if ( substr($columnName,0,1)=='+' ) {
                                    $tot[$columnName]=0;
                                    $columnName=substr($columnName,1);
                                }
                                if ( substr($columnName,0,1)=='!' ) {
                                    $columnName=substr($columnName,1);
                                }
                                echo "<th>".$columnName."</th>";
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
                        foreach($data['col'] as $columnName) {
                            if ( substr($columnName,0,1)=='+' ) {
                                $tot[$columnName]+=$item[$columnName];
                            }
                            if ( substr($columnName,0,1)=='!' ) { #hack, column namen starts with ! link in format naam|link
                                $part=explode('|', $item[$columnName]);
                                echo "<td>".Html::a($part[0],$part[1])."</td>";
                            } else {
                                echo "<td>".$item[$columnName]."</td>";
                            }
                        }
                        echo "</tr>";
                    }

                    if ( count($tot) ) {
                        echo "<tr style=\"background-color:#e8f0ff\" >";
                        if ( ! isset($nocount) ) {
                            echo "<td></td>";
                        }
                        foreach($data['col'] as $columnName) {
                            if ( substr($columnName,0,1)=='+' ) {
                                echo "<td>";
                                echo $tot[$columnName];
                                echo "</td>";
                            } else {
                                echo "<td></td>";
                            }
                        }
                        echo "</tr>";
                    }

                }

            ?>

        </table>
    </div>
</div>