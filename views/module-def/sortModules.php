<?php

use yii\helpers\Url;

?>


<style>
    xtd,
    xtr,
    xth {
        border: 1px solid black;
        border-collapse: collapse;
        cursor: all-scroll;
        padding-left: 8px;
        padding-right: 8px;
    }

    tr {
        cursor: grab;

        /* Cursor for grabbing (non-dragging state) */
    }

    tr.dragging {
        cursor: grabbing;
        /* Cursor for dragging state */
    }

    .xtable {
        border-collapse: collapse;
        -webkit-user-select: none;
        /* Safari */
        -ms-user-select: none;
        /* IE 10+ and Edge */
        user-select: none;
        /* Standard syntax */
        margin: 0 auto;
    }

    .dragging {
        background-color: lightblue;
    }

    .moved {
        color: red;
    }

    .ghost-row {}
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
                <th style="width:90px;">Cursus ID</th>
                <th style="width:120px;">Cursusnaam</th>
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
                echo "<td><a href=\"$url\">" . $item['naam'] . "</a></td>";
                echo "<td>" . $item['actief'] . "</td>";
                echo "<td>" . $item['cursus_id'] . "</td>";
                echo "<td>" . $item['cursus_naam'] . "</td>";
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
    <button type="submit" style="width: 80px;" id="submitButton" onclick="mySubmit()" class="btn btn-danger btn-sm">Save</button>
    <button type="button" style="width: 80px;" class="btn btn-secondary btn-sm" onclick="window.location.reload();">Cancel</button>
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