<?php

namespace app\controllers;
use Yii;
use DateTime;

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
        ['start' => '2024-10-28', 'end' => '2024-11-01', 'name'=>'Herfst'],
        ['start' => '2024-12-23', 'end' => '2025-01-03', 'name'=>'Kerst'],
        ['start' => '2025-02-17', 'end' => '2025-02-21', 'name'=>'Krokus'],
        ['start' => '2025-04-18', 'end' => '2025-04-18', 'name'=>'Goede vrijdag'],
        ['start' => '2025-04-21', 'end' => '2025-05-02', 'name'=>'Mei'],
        ['start' => '2025-05-05', 'end' => '2025-05-05', 'name'=>'Bevrijding'],
        ['start' => '2025-05-29', 'end' => '2025-05-30', 'name'=>'Hemelvaart'],
        ['start' => '2025-06-09', 'end' => '2025-06-09', 'name'=>'Pinkster'],
        ['start' => '2025-07-14', 'end' => '2024-08-15', 'name'=>'Zomer'],
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
        
        if ( isset($data[0]['name']) ) {
            $prediction = $this->predictAchievementDate($data, 3900, $data[0]['name']);
        } else {
            $prediction = '';
        }

        return $prediction;
    }

    function predictedStudieDuur($strStart, $strEnd) {
        $startDate = new DateTime($strStart);
        $endDate = new DateTime($strEnd);
        $interval = $endDate->diff($startDate);

        if ($interval->days < 210) {
            $studieduur = '2';
        } elseif($interval->days < 330) {
            $studieduur = '2.5';
        } elseif($interval->days < 580) {
            $studieduur = '3';
        } elseif($interval->days < 700) {
            $studieduur = '3.5';
        } elseif($interval->days < 900) {
            $studieduur = '4';
        } else {
            $studieduur = '4+';
        }
        return $studieduur;
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
        $daysToGo = max(0, ( $targetAchievement - $cumulativeAchievement ) / $slope);

        if ( $daysToGo > 0 && $daysPassed > 20 ) {
            $predictedDate = $this->getDateAfterWorkingDays($today, $daysToGo);
        } elseif ( $daysPassed > 20 ) {
            $predictedDate = $today;
        } else {
            # not tested; when less than 20 days at work, get a more or less fixed date.
            $predictedDate = $this->getDateAfterWorkingDays($today, 580 - $daysPassed);
        }

        $studieDuur =  $this->predictedStudieDuur($startDate, $predictedDate);

        $result =
                [ 'cumulativeAchievement' => round($cumulativeAchievement, 0)."/".$targetAchievement,
                  'percCompleted' => number_format( ($cumulativeAchievement / $targetAchievement)*100 ,1),
                  'startDate' => $startDate,
                  'today' => $today,
                  'daysPassed' => $daysPassed,
                  'slope' => number_format($slope, 2),
                  'mod/week' => number_format($slope*5/100, 1),
                  'week/mod' => number_format(1 / ($slope*5/100), 1),
                  'daysToGo' => round( $daysToGo, 0),
                  'predictedDate' => $predictedDate,
                  'studieDuur' => $studieDuur
                ];

        // $output = "";
        
        // $output .= "<pre>";
        // $output .= "\n cumulativeAchievement: " . round($cumulativeAchievement, 0);
        // $output .= "\n startDate:             " . $startDate;
        // $output .= "\n endDate (today):       " . $today;
        // $output .= "\n daysPassed:            " . $daysPassed;
        // $output .= "\n slope:                 " . round($slope, 2);
        // $output .= "\n mod/week:              " . round($slope*5/100, 1) ;
        // $output .= "\n week/mod:              " . round(1 / ($slope*5/100), 1);
        // $output .= "\n daysToGo:              " . round( $daysToGo, 0);
        // $output .= "\n =================================";
        // $output .= "\n predictedDate          ".$predictedDate;
        // $output .= "</pre>";
        // echo $output;
        // exit();
        
     
        return $result;
    }


    function countWorkingDays($startDate, $endDate) {

        // Count working days excluding weekends and vacation dates
        $workingDaysCount = 1; // avoid division by zero
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
