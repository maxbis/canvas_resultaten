<?php

use yii\helpers\Html;
?>

<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
    .nakijken {
        background-color:#f5ffe3;
        padding-left:15px;
    }
    .grey-column {
        color:#a0a0a0;
    }
    td {
        padding-right:5px;
    }
    th {
        font-size:0.8em;
    }
</style>

<script>
    function hide() {
        document.getElementById("main").style.display = "none";
        document.getElementById("wait").style.display = "block";
   }
</script>

<div class="card" id="wait" style="display:none;">
    <div class="container">
        <br><h1>Processing...</h1></br>
    </div>
</div>
<h3><?=$data[0]['module_name']?></h3>
<div class="card" id="main">
    <div class="container">
        <table class="table table-sm hoverTable">
        <thead>
            <th></th>
            <th>Opdracht</th>
            <th>Student</th>
            <th>Klas</th>
            <th></th>
            <th>Ingeleverd</th>
            <th>P</th>
            <th></th>
            <th>Canvas</th>
            <th></th>
            <th>AC</th>
            <th></th>
        </thead>
        <?php foreach ($data as $row) { ?>
            <tr>
                <td class="grey-column"><?=$row['assignment_pos']?></td>
                <td>
                    <?php echo Html::a($row['assignment_name'], ['public/details-module', 'assGroupId' => $row['module_id'], 'code' => $row['student_code']], ['class' => 'link-class']); ?>
                </td>
                <td>
                    <?php echo Html::a($row['student_name'], ['public/index', 'code' => $row['student_code']], ['class' => 'link-class']); ?>
                </td>
                <td class="grey-column"><?=$row['student_klas']?></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td><?=$row['ingeleverd']?></td>
                <td><?=$row['poging']?></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td> 
                    <a style="font-size: 0.8em;" href="<?=$row['canvas_link']?>" target="_blank" onmouseover="this.style.background='yellow'" onmouseout="this.style.background='none'" title="Naar opdracht">Canvas➞</a>
                </td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td class="nakijken">
                    <?php echo Html::a('✎ ', ['nakijken/update', 'assignment_id' => $row['assignment_id']], ['class' => 'link-class']); ?>
                </td>
                <td class="nakijken">
                    <?php if ($row['nakijken_id'] <> '') { ?>
                        <a style="font-size: 0.8em;" href="<?=$row['ac_link']?>" target="_blank" onmouseover="this.style.background='yellow'" onmouseout="this.style.background='none'" title="Naar opdracht">AC➞</a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </table>
    </div>
</div>


<?php
foreach ($data as $row) {
    foreach ($row as $fieldName => $fieldValue) {
        echo "Field: $fieldName, Value: $fieldValue<br/>";
    }
    echo "----------------------<br/>";
}
?>
</div>