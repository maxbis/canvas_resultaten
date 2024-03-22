<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

function isMobileDevice()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|
                        tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) || isset ($_GET['mobile']);
}
$subDomain = Yii::$app->params['subDomain'];
?>

<style>
    .hoverTable tr:hover td {
        background-color: #f6f6ff;
    }

    .bottom-button {
        padding: 0.375rem 0.75rem;
        font-size: 0.7em;
        text-align: center;
        cursor: pointer;
        color: #404040;
        font-weight: 400;
        background-color: #f8f9fa;
        border: solid 1px;
        border-color: #d0d0d0;
        border-radius: 0.25rem;
        margin: 2px;
        width: 120px;
    }

    .bottom-button:hover {
        background-color: #e6f1ff;
        text-decoration: none;
    }

    .bottom-button:active {
        background-color: #d0d0d0;
    }

    .top {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: top;
        height: 100x;
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left: 25px;
        margin-right: 20px;
    }

    .my-header {
        background-color: #f7f7f7;
        border-bottom: 1px solid #e7e7e7;
    }

    .start-column {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        max-width: 850px;
    }

    h5 {
        color: #606060;
    }

    th,
    .sub {
        font-weight: 400;
        font-size: 0.8em;
        color: #999;
    }

    .ac-button {
        display: inline-block;
        padding: 1px 2px;
        font-size: 0.7em;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: rgba(0, 123, 255, 0.25);
        ;
        border: none;
        border-radius: 4px;
        transition: background-color 0.1s ease;
        margin-top: 0px;
    }

    .ac-button:hover,
    .regular-link:hover {
        background-color: #ffdd00;
        color: #000000;
    }
</style>

<style>
    .form-container,
    .results-container,
    .nakijk-container {
        background-color: #ffffff;
        padding-left: 20px;
        padding-right: 20px;
        padding-top: 10px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        width: 380px;
    }

    .form-group {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .form-group input[type="text"] {
        width: 100%;
        border: 1px solid #cccccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-group button {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin: 20px;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }

    .results-container ul {
        max-height: 600px;
        overflow-y: auto;
        list-style-type: none;
        padding: 0;
    }

    .results-container li {
        margin-bottom: 5px;
    }

    .results-container li:hover {
        background-color: #e0e0e0;
        cursor: pointer;
    }

    .results-container a:link,
    .results-container a:visited {
        color: darkblue;
        text-decoration: none;
    }
</style>


<?php if (!$resultaten && !isMobileDevice()): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var apiURL = '<?= Url::toRoute(['resultaat/ajax-nakijken']); ?>';
        jQuery(window).on('load', function () {
            jQuery.ajax({
                type: 'POST',
                data: { '_csrf': csrfToken },
                dataType: 'html',
                url: apiURL,
                success: function (data) {
                    document.getElementById('nakijken').innerHTML = data;
                },
                error: function (data) {
                    console.log('Ajax Error');
                    console.log(data);
                }
            });
        });

    </script>
<?php endif; ?>

<p><small style="color:#999;">Laatste update:
        <?= $timestamp ?>
    </small>
<p>


<div class="container">

    <div class="row">
        <div class="col-sm">
            <div class="row">
                <div class="form-container">
                    <h5>Zoek Student</h5>
                    <form method="post" action=<?php Url::toRoute(['resultaat/start']); ?>>
                    <div class="form-group">
                        <input type="text" id="studentName" name="search" minlength="2" required>
                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />
                        <button type="submit">Zoek</button>
                    </div>
                    </form>
                </div>

                <?php if ($resultaten && $found > 0) { ?>
                    <div class="results-container">
                        <h5>Students</h5>
                        <ul>
                            <?php $prevCohort = $resultaten[0]['cohort']; ?>
                            <?php foreach ($resultaten as $student): ?>
                                <?php
                                if ($student['cohort'] != $prevCohort) {
                                    $prevCohort = $student['cohort'];
                                    $style = "margin-top:20px;";
                                } else {
                                    $style = "";
                                }
                                ?>
                                <li style="<?= $style; ?>">
                                    <?php
                                    echo "<span style='color:#808080'>" . $student['klas'] . "</span>&nbsp;";
                                    echo "<a href=\"https://${student['cohort']}.cmon.ovh/public/index?code=${student['code']}\">";
                                    echo $student['name'];
                                    echo "</a>";
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>

        </div>

        <div class="col-sm">
            <?php if (!$resultaten && !isMobileDevice()): // show nakijken section if not on mobile and no studentlist is shown               ?>

                <div class="nakijk-container">
                    <h5>Nakijken</h5>

                    <table id="nakijken" class="table table-sm hoverTable">
                        <tr>
                            <td>...</td>
                        </tr>
                    </table>

                </div>
            <?php endif; ?>
        </div>

    </div>
</div>