<?php

namespace app\controllers;
use Yii;

class PredictionController
{

    private $vacationPeriods = [
        ['start' => '2022-01-01', 'end' => '2022-04-01', 'name'=>'vrij22'],
        ['start' => '2023-01-01', 'end' => '2023-04-01', 'name'=>'vrij23'],
        ['start' => '2023-10-23', 'end' => '2023-10-27', 'name'=>'Herfst'],
        ['start' => '2023-12-25', 'end' => '2024-01-05', 'name'=>'Kerst'],
        ['start' => '2024-02-29', 'end' => '2024-02-23', 'name'=>'Krokus'],
        ['start' => '2024-03-29', 'end' => '2024-03-29', 'name'=>'Goede vrijdag'],
        ['start' => '2024-04-01', 'end' => '2024-04-01', 'name'=>'Paasmaandag'],
        ['start' => '2024-04-29', 'end' => '2024-05-10', 'name'=>'Mei'],
        ['start' => '2024-05-20', 'end' => '2024-05-20', 'name'=>'Pinkstermaandag'],
        ['start' => '2024-07-15', 'end' => '2024-08-16', 'name'=>'Zomer'],
        ['start' => '2024-10-26', 'end' => '2024-11-03', 'name'=>'Herfst'],
        ['start' => '2024-12-21', 'end' => '2025-01-05', 'name'=>'Kerst'],
        ['start' => '2025-02-15', 'end' => '2025-02-23', 'name'=>'Krokus'],
    ];

    private  $vacationDates = [];

    function __construct() {
        foreach ($this->vacationPeriods as $period) {
            $this->vacationDates = array_merge($this->vacationDates, $this->generateDatesInRange($period['start'], $period['end']));
        }
    }


    public function predict($id) {
        $sql ="select u.name name, s.submitted_at date, s.entered_score achievement
            from submission s
            join user u on u.id = s.user_id
            where  s.user_id = $id
            and entered_score != 0
            and YEAR(s.submitted_at) > 1970
            order by date";
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if ( $data ) {
            $prediction = $this->predictAchievementDate($data, 3900, $data[0]['name']);
            return $prediction;
        }
        return;
    }

    function predictAchievementDate($dataset, $targetAchievement, $name="") {
        // Calculate cumulative achievements
        $cumulativeAchievement = 0;
        foreach ($dataset as &$data) {
            $cumulativeAchievement += $data['achievement'];
            $data['cumulative'] = $cumulativeAchievement;
        }
        
        // array is sorted so first element is oldest
        $startDate = isset($dataset[0]['date']) ? $dataset[0]['date'] : null;

        $decay = 0.85; // the part to be done is decelerated with 15%, becasue it becomes more difficult. 
        $today = date('Y-m-d');
        $daysPassed = $this->countWorkingDays($startDate, $today); 
        $slope = ( $cumulativeAchievement / $daysPassed * $decay );
        if ( $slope ==0 ) { $slope = 0.01; }
        $daysToGo = ( $targetAchievement - $cumulativeAchievement ) / $slope;
        $predictedDate = $this->getDateAfterWorkingDays($today, $daysToGo);

        $output = "";
        
        $output .= "<pre>";
        $output .= "\n".$name;
        $output .= "\n cumulativeAchievement: " . $cumulativeAchievement;
        $output .= "\n startDate:             " . $startDate;
        $output .= "\n endDate (today):       " . $today;
        $output .= "\n daysPassed:            " . $daysPassed;
        $output .= "\n slope:                 " . intval($slope*100)/100;
        $output .= "\n mod/wk:                " . intval($slope*5)/90;
        $output .= "\n wk/mod:                " . intval(10/(intval($slope*5)/90))/10;
        $output .= "\n daysToGo:              "  .intval($daysToGo);
        $output .= "\n ====================================";
        $output .= "\npredictedDate           " . $predictedDate;
        $output .= "</pre>";
        // echo $output;
        // exit();
     
        return $output;
    }


    function countWorkingDays($startDate, $endDate) {

        // Count working days excluding weekends and vacation dates
        $workingDaysCount = 0;
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {

            if ($this->isWeekday($currentDate) && !in_array($currentDate, $this->vacationDates)) {
                $workingDaysCount++;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $workingDaysCount;
    }

    function getDateAfterWorkingDays($startDate, $N) {
    
        // Find the date after N working days excluding weekends and vacation dates
        $workingDaysCount = 0;
        $currentDate = $startDate;
        while ($workingDaysCount < $N) {
            if ($this->isWeekday($currentDate) && !in_array($currentDate, $this->vacationDates)) {
                $workingDaysCount++;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $currentDate;
    }
    

    function isWeekday($date) {
        $dayOfWeek = date('w', strtotime($date));
        return ($dayOfWeek >= 1 && $dayOfWeek <= 5); // 1 for Monday and 5 for Friday
    }

    function generateDatesInRange($startDate, $endDate) {
        $dates = [];
        $currentDate = $startDate;
    
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $dates;
    }

}
