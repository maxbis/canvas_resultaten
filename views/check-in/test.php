<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
</style>

<?php
$prevKlas="";

echo "<table class=\"table table-sm hoverTable\">";
foreach ($result as $item) {
    if ( $item['klas'] != $prevKlas ) echo "<tr><td></td><td></td><td></td></tr>";
    echo "<tr>";
    echo "<td>";
    echo $this->context->button($item['code'], true);
    echo "</td>";
    echo "<td>".$item['klas']."</td>";
    echo "<td>".$item['name']."</td>";
    echo "</tr>";
    $prevKlas=$item['klas'];
}
echo "</table>";