<?php

use yii\helpers\Html;
?>

<style>
    .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }

    .nakijken {
        background-color: #f5ffe3;
        padding-left: 15px;
    }

    .grey-column {
        color: #a0a0a0;
    }

    .button {
        display: inline-block;
        padding: 1px 2px;
        font-size: 0.7em;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: rgba(0, 123, 255, 0.6);
        ;
        border: none;
        border-radius: 4px;
        transition: background-color 0.1s ease;
    }

    .bottom-button {
        padding: 0.375rem 0.75rem;
        font-size: 0.8em;
        text-align: center;
        cursor: pointer;
        color: #404040;
        font-weight: 400px;
        background-color: #f8f9fa;
        border: solid 1px;
        border-color: #d0d0d0;
        border-radius: 0.25rem;
        float: right;
        margin-left: 20px;
    }

    .button:hover,
    .regular-link:hover {
        background-color: #ffdd00;
        color: #000000;
    }

    td {
        padding-right: 5px;
    }

    th {
        font-size: 0.8em;
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
        <br>
        <h1>Processing...</h1></br>
    </div>
</div>
<h3><?= $data[0]['module_name'] ?></h3>
<div class="card" id="main">
    <div class="container">
        <table class="table table-sm hoverTable">
            <thead>
                <th>#</th>
                <th colspan=2>AC</th>
                <th>Opdracht</th>
                <th>Student</th>
                <th>Klas</th>
                <th></th>
                <th>Ingeleverd</th>
                <th>P</th>
                <th></th>
                <th>Canvas</th>
                <th></th>
            </thead>

            <?php
            $prev_assignment_pos = 0;
            foreach ($data as $row) {
                ?>
                <tr>
                    <td class="grey-column">
                        <?php if ($prev_assignment_pos != $row['assignment_pos']) {
                            echo $row['assignment_pos'];
                        } ?></td>

                    <?php if ($prev_assignment_pos != $row['assignment_pos']) { ?>
                        <td>
                            <?php echo Html::a('✎ ', ['nakijken/update', 'assignment_id' => $row['assignment_id'], 'alt_return' => 1], ['class' => 'link-class regular-link']); ?>
                        </td>
                        <td>
                            <?php if ($row['nakijken_id'] <> '') { ?>
                                <a class="button ac-link" href="<?= $row['ac_link'] ?>" target="_blank" title="Auto Correct">AC➞</a>
                            <?php } ?>
                        </td>
                    <?php } else { ?>
                        <td></td>
                        <td></td>
                    <?php } ?>

                    <td>
                        <?php if ($prev_assignment_pos != $row['assignment_pos']) { ?>
                            <a href="https://talnet.instructure.com/courses/<?= $row['course_id'] ?>/assignments/<?= $row['assignment_id'] ?>"
                                target="_blank" title="Naar opdracht"><?= $row['assignment_name'] ?>➞</a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php echo Html::a($row['student_name'], ['public/index', 'code' => $row['student_code']], ['class' => 'link-class']); ?>
                    </td>
                    <td class="grey-column"><?= $row['student_klas'] ?></td>
                    <td>&nbsp;&nbsp;&nbsp;</td>
                    <td><?= $row['ingeleverd'] ?></td>
                    <td><?= $row['poging'] ?></td>
                    <td>&nbsp;&nbsp;&nbsp;</td>
                    <td>
                        <a class="button" href="<?= $row['canvas_link'] ?>" target="_blank" title="Naar opdracht">Canvas➞</a>
                    </td>
                    <td>&nbsp;&nbsp;&nbsp;</td>

                </tr>
                <?php
                $prev_assignment_pos = $row['assignment_pos'];
            }
            ?>
        </table>
        <?php if (isset($lastLine))
            echo $lastLine; ?>
    </div>
</div>

<p style="margin:20px;margin-top:40px;">
    <a class="button apply-to-all-ac" title="Auto Correct All">AC All➞</a>
    <br>
    <small style="font-size:11px;color:#a0a0a0">Deze actie opent voor elke regel een tab. Dit werkt alleen als de browser instellingen goed staan. </small>
</p>

<?php
$script = <<<JS
document.querySelector('.apply-to-all-ac').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default behavior of the link
    
    // Get all links with the class 'ac-link'
    let links = document.querySelectorAll('.ac-link');
    
    links.forEach(function(link) {
        // Simulate clicking each link
        console.log(link.href);
        window.open(link.href, link.target || '_self');
    });
});
JS;
$this->registerJs($script);
?>
