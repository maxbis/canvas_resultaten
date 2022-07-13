<style>
   .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }
</style>
<h1>Test pagina</h1>
<div class="row">

<?php
$rows = round((count($result)+1)/3);
$i=1;

echo "<div class=\"table-responsive col-md-4\">";
echo "<table class=\"table table-sm hoverTable\" >";
foreach ($result as $item) {
    if ( $i++ % $rows == 0 ) {
        echo "</table></div>";
        echo "<div class=\"table-responsive col-md-4\">";
        echo "<table class=\"table table-sm hoverTable\" >";
    }
    echo "<tr>";
    echo "<td>";
    echo $this->context->button($item['code'], true);
    echo "</td>";
    echo "<td>".$item['klas']."</td>";
    echo "<td>".$item['name']."</td>";
    echo "</tr>";
}
echo "</table></div>";
?>

</div>