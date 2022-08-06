<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelRadiationDataPage {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMinAvailableDates($channelId) {
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select min(datatime) mindate from $tablechannelmain where  channelid =:channelid";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr;
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "minimumdate" => $row['mindate']
            );
        }
//             echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
        return $tempArr;
    }

     public function getRadiationdailyAll($sDate, $eDate) {

        $sqlQuery = "select  t.valuedate valuedate ,  
                        CAST(t.value AS DECIMAL(10,2)) rad                    
                        from channel_0120_values t  
                        where date(t.datatime) between  :sdate and :edate
                        order by t.valuedate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'rad' => $row['rad']
                );
            }
        }
        return $tempArr;
    }

    public function getRadiationDurationPerDays($sDate, $eDate) {

        $sqlQuery = "select  date(t.datatime) valuedate, CAST(avg(t.value) AS DECIMAL(10,2)) avgrad,
                        (select value from channel_0120_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxrad,
                        (select DATE_FORMAT(valuedate, '%H:%i' ) from channel_0120_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxradtime,
                        (select value from channel_0120_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) minrad,
                        (select DATE_FORMAT(valuedate, '%H:%i' )  from channel_0120_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) minradtime
                      from channel_0120_values t  
                       where date(t.datatime) between  :sdate and :edate
                       group by  date( t.datatime ) order by t.valuedate desc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'avgrad' => $row['avgrad'],
                    'maxrad' => $row['maxrad'],
                    'maxradtime' => $row['maxradtime'],
                    'minrad' => $row['minrad'],
                    'minradtime' => $row['minradtime']
                );
            }
        }
        return $tempArr;
    }

     public function getRadiationDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

        $selectDefault = 'D';
        $selectYandM = 'YM';
        $selectXtoY = 'XY';
        $selectCompare = 'C';
        // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
        $mainArr = array();
        if ($inputSelection == $selectDefault) {
            $date = date('Y-m-d', strtotime('-31 days', strtotime($input1)));
            date_default_timezone_set('UTC');
            $end_date = $input1;
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->getRadiationDailyOverviewByDate($date);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectYandM) {
            date_default_timezone_set('UTC');
            $end_date = date("Y-m-t", strtotime($input1));
            $date = date("Y-m-d", strtotime($input1));
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->getRadiationDailyOverviewByDate($date);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectXtoY) {
            date_default_timezone_set('UTC');
            $date = $input1;
            $end_date = $input2;
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->getRadiationDailyOverviewByDate($date);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = $str_arr[$t];
                $tempArr = $this->getRadiationDailyOverviewByDate($tempdate);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        }

//         echo "<pre>";
//        print_r($mainArr);
//        echo "<post>";

        return $mainArr;
    }

    public function getRadiationDailyOverviewByDate($sdate) {

        $finalArr = Array();
        $avgTempArr = $this->getRadiationDailyAvg($sdate, $sdate);
        if ($avgTempArr != null) {
            $finalArr = array_merge($finalArr, $avgTempArr);
            $minTempArr = $this->getMinRadiationByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $minTempArr);
            $maxTempArr = $this->getMaxRadiationByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $maxTempArr);
//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";
        }
        return $finalArr;
        //       }
    }

    public function getRadiationDailyAvg($sDate, $eDate) {
        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgRad                    
                    from channel_0120_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                    group by  day( channelv.valuedate ) order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr = array(
                    'valuedate' => $row['valuedate'] ?? '',
                    'paramdate' => $row['paramdate'],
                    'avgRad' => $row['avgRad']
                );
            }
        }
        return $tempArr;
    }

    public function getMinRadiationByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minRad                    
                    from channel_0120_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value asc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $minTemp = array(
            'minRadtime' => $row['valuedate'],
            'minRad' => $row['minRad']
        );

        return $minTemp;
    }

    public function getMaxRadiationByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxRad                    
                    from channel_0120_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array(
            'maxRadtime' => $row['valuedate'],
            'maxRad' => $row['maxRad']
        );

        return $maxTemp;
    }

    public function getRadiationOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

        $selectDefault = 'D';
        $selectYandM = 'YM';
        $selectXtoY = 'XY';
        $selectCompare = 'C';
        // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
        $mainArr = array();
        if ($inputSelection == $selectDefault) {
            $str_arr = explode("-", $input1);
            $year = $str_arr[1];
            for ($t = 1; $t <= 12; $t++) {
                $month = $t;
                if ($t < 10) {
                    $month = '0' . $t;
                }
                $monthYear = $month . '-' . $year;
                $tempArr = $this->getRadiationOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getRadiationOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectXtoY) {
            $start = new DateTime(trim($input1));
            $start->modify('first day of this month');
            $end = new DateTime(trim($input2));
            $end->modify('first day of next month');
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $monthyear = $dt->format("m-Y");
                $tempArr = $this->getRadiationOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getRadiationOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

        return $mainArr;
    }

    public function getRadiationOverviewByMonth($sdate) {

        $finalArr = Array();
        $AvgTempArr = $this->getAverageRadPerMonth($sdate);
        $finalArr = array_merge($finalArr, $AvgTempArr);
        $minTempArr = $this->getMinRadByMonth($sdate);
        $finalArr = array_merge($finalArr, $minTempArr);
        $maxTempArr = $this->getMaxRadByMonth($sdate);
        $finalArr = array_merge($finalArr, $maxTempArr);

        return $finalArr;
    }

    public function getMinRadByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minRad                    
                    from channel_0120_values channelv		
                    where    DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value asc limit 1;";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array();
        if ($row != null && $row['paramdate'] != null) {
            $globalRadAvg = array(
                'minRadtime' => $row['valuedate'] ?? '-',
                'minRad' => $row['minRad'] ?? '-'
            );
        }
        return $globalRadAvg;
    }

    public function getMaxRadByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxRad                    
                    from channel_0120_values channelv		
                    where   DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value desc limit 1  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxRad = array();
        if ($row != null && $row['paramdate'] != null) {
            $maxRad = array(
                'maxRadtime' => $row['valuedate'],
                'maxRad' => $row['maxRad']
            );
        }
        return $maxRad;
    }

    public function getAverageRadPerMonth($sDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%m-%Y')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgRad 
                    from channel_0120_values channelv		
                    where  DATE_FORMAT(channelv.valuedate, '%m-%Y') =:sdate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row != null && $row['paramdate'] != null) {
                $tempArr = array(
                    'valuedate' => $row['valuedate'],
                    'paramdate' => $row['paramdate'],
                    'avgRad' => $row['avgRad']
                );
            }
        }
        return $tempArr;
    }

   

// ************************ *******************************************************

   
}
