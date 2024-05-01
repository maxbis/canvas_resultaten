<?php

use yii\helpers\Url;
use kartik\sortable\Sortable;


?>

<script>

    function updateOrder() {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var updateSortOrder = '<?= Url::toRoute(['report/update-order']); ?>';

        var items = [];
        $('.sortable-list').children().each(function (index, element) {
            items.push({
                id: $(element).data('id'),
                pos: index
            });
        });

        $.ajax({
            type: 'POST',
            data: { '_csrf': csrfToken,  items: items },
            dataType: 'html',
            url: updateSortOrder,
            success: function (response) {
                if (response.status === 'success') {
                    console.log('Order updated successfully');
                }
            },
            error: function (data) {
                console.log('Ajax Error');
                console.log(data);
            }
        });
    }

</script>

<?php

$itemsList = [];

foreach ($items as $item) {
    $itemsList[] = [
        'content' => $item['naam'], // Adjust according to your attribute
        'options' => ['data-id' => $item['id']]
    ];
}

echo Sortable::widget([
    'type' => Sortable::TYPE_LIST,
    'items' => $itemsList,
    'pluginEvents' => [
        'sortupdate' => 'function() { updateOrder(); }',
    ],
    'options' => ['class' => 'sortable-list'],
]);
