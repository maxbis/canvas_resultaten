<?php

use yii\helpers\Html;

function getIsoWeeksInYear($year)
{
    $date = new DateTime;
    $date->setISODate($year, 53);
    return ($date->format("W") === "53" ? 53 : 52);
}

$workLoadperWeek = [];

foreach ($data['row'] as $item) { // read all weeks from query into ass. array.
    $workLoadperWeek[$item['Week']] = intval($item['+Aantal']);
}

$aantalWeken = 20;                                  // Number of weeks in graph
$weekNumber = date("W");                            // This week number
$weeksThisYear = getIsoWeeksInYear(date("Y"));    // max. week number of this year

$start = $weekNumber - $aantalWeken;
if ($start < 0) { // roll over to last year
    $start += $weeksThisYear;
}

$chartArray = [['Week', 'Taken']];

for ($i = 0; $i < $aantalWeken; $i++) {
    $week = $start + $i;
    if ($week > $weeksThisYear) { // roll over to next year
        $week -= $weeksThisYear;
    }
    if (array_key_exists($week, $workLoadperWeek)) {
        array_push($chartArray, [strval($week), intval($workLoadperWeek[$week])]); // value from query
    } else {
        array_push($chartArray, [strval($week), 0]); // no value means 0
    }
}

//dd($chartArray);

$chart = [
    'visualization' => 'LineChart',
    'data' => $chartArray,
    'options' => ['title' => 'Weekly Activity ' . $data['row'][0]['Student']]
];

use scotthuangzl\googlechart\GoogleChart;

echo GoogleChart::widget($chart);
echo Html::a('<< Terug', Yii::$app->request->referrer, ['class' => 'btn btn-light']);
// echo  Html::a('<< Volledig Overzicht', ['/public/index', 'code' => $code], ['class' => 'btn btn-info', 'title' => 'Student Overzicht',]);
