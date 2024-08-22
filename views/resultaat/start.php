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
    .nakijk-container,
    .msg-container {
        background-color: #ffffff;
        padding-left: 20px;
        padding-right: 20px;
        padding-top: 10px;
        padding-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        width: 380px;
        position: relative;
    }

    .msg-container {
        background-color: #edffee;
        display: none;
    }

    .results-container {
        display: none;
    }

    .form-group {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .form-group input[type="text"] {
        width: 100%;
        /* border: 1px solid #cccccc;
        border-radius: 4px;
        box-sizing: border-box; */
    }

    .form-group button {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-left: 8px;
        min-width: 40px;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        margin-left: 20px;
        /* Adjusts the alignment with the text box above */
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

    .loader {
        border: 5px solid #f3f3f3;
        /* Light grey */
        border-top: 5px solid #3498db;
        /* Blue */
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 2s linear infinite;
        margin: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .update-link,
    .update-link:hover {
        color: #050505;
        text-decoration: none;
        cursor: pointer;
    }

    .close-btn {
        position: absolute;
        top: 0;
        right: 10px;
        /* Adjust the right and top values as needed to position the close button */
        cursor: pointer;
        font-size: 20px;
        /* Adjust the font size as needed */
    }
</style>

<?php
if (!empty ($search)) {
    // Register a script in the view
    $this->registerJs("
        $(document).ready(function() {
            // Set the value of the search input
            $('#search-students input[name=\"search\"]').val(" . \yii\helpers\Json::htmlEncode($search) . ");

            // Trigger the click event of button1
            $('#button1').click();
        });
    ", \yii\web\View::POS_READY, 'my-search-trigger');
}
?>

<?php if (!isMobileDevice()): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <script>

        function loadNakijkOverzicht() {
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var nakijkOverzichtApi = '<?= Url::toRoute(['resultaat/ajax-nakijken']); ?>';

            $.ajax({
                type: 'POST',
                data: { '_csrf': csrfToken },
                dataType: 'html',
                url: nakijkOverzichtApi,
                success: function (data) {
                    $('#nakijken').html(data);
                },
                error: function (data) {
                    console.log('Ajax Error');
                    console.log(data);
                }
            });
        }

        $(window).on('load', function () {
            setTimeout(loadNakijkOverzicht, 200); // Delay of 200 milliseconds
        });

    </script>


<?php endif; ?>

<script>
    $(document).ready(function () {

        $('#studentName').keypress(function (e) {
            if (e.which == 13) { // Enter key has the keycode 13
                e.preventDefault(); // Prevent the default form submit action
                $('#button1').click(); // Trigger the click event of the second button
            }
        });

        $('#messageDiv').click(function () {
            // Hide the div when it is clicked
            $(this).slideUp('slow');
        });

    });
</script>


<script>

    var timeoutId = null;
    function updateAssignment(moduleId) {
        console.log('updateAssigment: ' + moduleId);
        $('#nakijken').html(`<tr><td><div class="loader"></div></td></tr>`);
        updateModule(moduleId); // AJAX call to update DB via Pytjon sctips on server
    
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
        timeoutId = setTimeout(function () {
            $("#messageDiv").slideUp('slow');
        }, 10000);
    }

    function checkForOneLink(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var links = doc.querySelectorAll('a');
        if (links.length === 1) {
            return links[0].href;
        } else {
            return "";
        }
    }

    var searchStudentsApi = '<?= Url::toRoute(['resultaat/search-students']); ?>';
    function submitFormWithValue(value) {
        var form = $('#search-students');
        var searchInput = form.find('input[name="search"]').val();

        var searchInputField = form.find('input[name="search"]');

        if (searchInputField.val().length < 2) {
            searchInputField.val(''); // Clear the current input
            searchInputField.attr('placeholder', 'Minimum 2 chars');
            return;
        }

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cohort';
        input.value = value;

        form.append(input);
        var formData = form.serialize();

        $.ajax({
            url: searchStudentsApi,
            type: 'POST',
            dataType: 'html',
            data: formData,
            success: function (data) {
                console.log('Student-search form submitted successfully');
                console.log('Data:' + data);

                if (data == "") {
                    originalValue = searchInputField.val();
                    searchInputField.val('');
                    searchInputField.val('Nothing found');
                    setTimeout(function () { searchInputField.val(originalValue); }, 500);
                    return;
                }

                // parse html-data to checker, if only one link is returned redirect to it and stop
                if (link = checkForOneLink(data)) {
                    window.location.href = link;
                    return;
                } else {
                    document.getElementById('students-list').innerHTML = data;
                    $("#results-container").show();
                }

            },
            error: function () {
                console.log('An error occurred in API call search-student');
            }
        });
    }


    function updateModule(moduleId) {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var updateModuleApi = '<?= Url::toRoute(['canvas-update/update']); ?>';
        updateModuleApi = updateModuleApi + '?assignmentGroup=' + moduleId + '&flag=ajax';

        $.ajax({
            type: 'POST',
            data: { '_csrf': csrfToken },
            dataType: 'html',
            url: updateModuleApi,
            success: function (data) {
                $('#messageText').html(data);
                $("#messageDiv").slideDown('slow');
                loadNakijkOverzicht();
            },
            error: function (data) {
                console.log('Ajax Error');
                console.log(data);
            }
        });
    }

</script>
        

<p><small style="color:#999;">Laatste update:
        <?= $timestamp ?>
    </small>
<p>


<div class="container">

    <div class="row">
        <div id="messageDiv" class='col-11 msg-container message'>
            <span id="messageText">...</span>
            <span class="close-btn">&times;</span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <div class="form-container">
                <h5>Zoek Student</h5>
                <form id="search-students" method="post" action=<?php Url::toRoute(['resultaat/start']); ?>>
                    <!-- <?php $form = ActiveForm::begin(['id' => 'search-students',]); ?> -->
                    <div class="form-group">
                        <input type="text" id="studentName" name="search" minlength="2" placeholder="(deel van) naam"
                            required>
                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />
                        <button type="button" id="button1" onclick="submitFormWithValue('<?= $subDomain ?>')">
                            <?= $subDomain ?>
                        </button>
                        <button type="button" id="button2" onclick="submitFormWithValue('all')">All</button>
                    </div>
                </form>
                <!-- <?php ActiveForm::end(); ?> -->
            </div>

            <div class="results-container" id="results-container">
                <h5>Studenten</h5>
                <ul id="students-list">
                </ul>
            </div>

        </div>

        <div class="col-sm">
            <?php if (!isMobileDevice()): // show nakijken section if not on mobile and no studentlist is shown                                              ?>
                <div class="nakijk-container">
                    <h5>Nakijken</h5>
                    <table id="nakijken" class="table table-sm hoverTable">
                        <tr>
                            <td>
                                <div class="loader"></div>
                            </td>
                        </tr>
                    </table>

                </div>
            <?php endif; ?>
        </div>

    </div>
</div>