<?php

use yii\helpers\Url;

?>


<style>

    tr {
        cursor: grab;

        /* Cursor for grabbing (non-dragging state) */
    }

    tr.dragging {
        cursor: grabbing;
        /* Cursor for dragging state */
    }

    .dragging {
        background-color: lightblue;
    }

    .moved {
        background-color: lightblue;
    }

    .ghost-row {

    }
</style>


<script>
    var row, nextRow;
    var animationFrameId;

    function start() {
        row = event.target;
        event.target.classList.add('dragging');
        row.style.transform = 'scale(1.1)';
        document.body.style.cursor = 'grabbing';
    }
    function dragover() {
        var e = event;
        e.preventDefault();

        const targetRow = e.target.closest('tr');
        const tbody = targetRow.parentNode;
        const movingDown = Array.from(tbody.children).indexOf(targetRow) > Array.from(tbody.children).indexOf(row);

        window.cancelAnimationFrame(animationFrameId);
        animationFrameId = window.requestAnimationFrame(() => {
            if (movingDown) {
                tbody.insertBefore(row, targetRow.nextSibling);
            } else {
                tbody.insertBefore(row, targetRow);
            }
            console.log(targetRow.rowIndex);
            console.log(targetRow.getAttribute('index'));
            // Add a 'ghost' row to smooth out the transition visually
            if (nextRow) nextRow.classList.remove('ghost-row');
            nextRow = targetRow;
            nextRow.classList.add('ghost-row');
        });
    }

    function end() {
        var e = event;
        e.preventDefault();
        const targetRow = e.target.closest('tr');

        row.classList.remove('dragging');
        if (targetRow.rowIndex != targetRow.getAttribute('index')) {
            row.classList.add('moved');
        } else {
            row.classList.remove('moved');
        }
        row.style.transform = 'scale(1.0)';
        if (nextRow) {
            nextRow.classList.remove('ghost-row');
        }
        window.cancelAnimationFrame(animationFrameId);
    }
</script>

<h1>Reorder modules</h1>
<div id="myTable" class="card-body">
<table class="table table-sm hoverTable">
        <thead>
            <tr>
                <th style="width:60px;">Order</th>
                <th style="width:60px;">Blok</th>
                <th style="width:260px;">Naam</th>
                <th style="width:60px;">Actief</th>
                <th style="width:120px;">Cursus</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            foreach ($items as $item) {
                $index = $index + 1;
                echo "<tr index=\"" . $index . "\" value=\"" . $item['id'] . "\" draggable=\"true\" ondragstart=\"start()\" ondragover=\"dragover()\" ondragend=\"end()\">";
                echo "<td>$index</td>";
                echo "<td>" . $item['blok'] . "</td>";
                $url = Url::to(['/module-def/update', 'id' => $item['id']]);
                echo "<td><a href=\"$url\" title=\"update\">" . $item['naam'] . "</a></td>";
                echo "<td>" . $item['actief'] . "</td>";
                echo "<td><a href=\"https://talnet.instructure.com/courses/".$item['cursus_id']."/modules\" target=\"_blank\" title=\"Naar canvas module\">" . $item['cursus_naam'] . "➞</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php $url = Url::toRoute(['module-def/reorder']); ?>

<form id="myForm" method="POST" action="<?= $url ?>">
    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>"></input>
    <input type="hidden" name="order" id="order" value=""></input>
    <a href="/report/modules" style="width: 80px;" class="btn btn-secondary btn-sm"><< back</a>
    <button type="submit" style="width: 80px;margin-left:20px;" id="submitButton" onclick="mySubmit()" class="btn btn-danger btn-sm">Save</button>
    <button type="button" style="width: 80px;margin-left:20px;" class="btn btn-primary btn-sm" onclick="window.location.reload();">Reset</button>
</form>


<script>
    function mySubmit() {
        const tableRows = document.querySelectorAll('#myTable tr');
        let values = [];

        tableRows.forEach(row => {
            var value = row.getAttribute('value');
            if (value !== null) {
                values.push(value);
            }
        });

        const serializedValues = values.join(',');  // Serialize array into a string
        document.getElementById('order').value = serializedValues;  // Set the serialized values to the hidden input
        console.log(serializedValues);

        document.getElementById('myForm').submit();
    }
</script>