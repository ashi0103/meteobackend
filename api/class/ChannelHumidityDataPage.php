<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelHumidityDataPage {

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

     public function getHumiditydailyAll($sDate, $eDate) {

        $sqlQuery = "select  t.valuedate valuedate ,  
                        CAST(t.value AS DECIMAL(10,2)) humidity                    
                        from channel_0140_values t  
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
                    'rad' => $row['humidity']
                );
            }
        }
        return $tempArr;
    }

    public function getHumidityDurationPerDays($sDate, $eDate) {

        $sqlQuery = "select  date(t.datatime) valuedate, CAST(avg(t.value) AS DECIMAL(10,2)) avgHumidity,
                        (select value from channel_0140_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxHumidity,
                        (select DATE_FORMAT(valuedate, '%H:%i' ) from channel_0140_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxHumiditytime,
                        (select value from channel_0140_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) minHumidity,
                        (select DATE_FORMAT(valuedate, '%H:%i' )  from channel_0140_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) minHumiditytime
                      from channel_0140_values t  
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
                    'avgrad' => $row['avgHumidity'],
                    'maxrad' => $row['maxHumidity'],
                    'maxradtime' => $row['maxHumiditytime'],
                    'minrad' => $row['minHumidity'],
                    'minradtime' => $row['minHumiditytime']
                );
            }
        }
        return $tempArr;
    }

     public function getHumidityDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getHumidityDailyOverviewByDate($date);
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
                $tempArr = $this->getHumidityDailyOverviewByDate($date);
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
                $tempArr = $this->getHumidityDailyOverviewByDate($date);
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
                $tempArr = $this->getHumidityDailyOverviewByDate($tempdate);
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

    public function getHumidityDailyOverviewByDate($sdate) {

        $finalArr = Array();
        $avgTempArr = $this->getHumidityDailyAvg($sdate, $sdate);
        if ($avgTempArr != null) {
            $finalArr = array_merge($finalArr, $avgTempArr);
            $minTempArr = $this->getMinHumidityByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $minTempArr);
            $maxTempArr = $this->getMaxHumidityByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $maxTempArr);
//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";
        }
        return $finalArr;
        //       }
    }

    public function getHumidityDailyAvg($sDate, $eDate) {
        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgHumidity                    
                    from channel_0140_values channelv		
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
                    'avgHumidity' => $row['avgHumidity']
                );
            }
        }
        return $tempArr;
    }

    public function getMinHumidityByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minHumidity                    
                    from channel_0140_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value asc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $minTemp = array(
            'minHumiditytime' => $row['valuedate'],
            'minHumidity' => $row['minHumidity']
        );

        return $minTemp;
    }

    public function getMaxHumidityByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxHumidity                    
                    from channel_0140_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array(
            'maxHumiditytime' => $row['valuedate'],
            'maxHumidity' => $row['maxHumidity']
        );

        return $maxTemp;
    }

    public function getHumidityOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getHumidityOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getHumidityOverviewByMonth($monthYear);
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
                $tempArr = $this->getHumidityOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getHumidityOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

        return $mainArr;
    }

    public function getHumidityOverviewByMonth($sdate) {

        $finalArr = Array();
        $AvgTempArr = $this->getAverageHumidityPerMonth($sdate);
        $finalArr = array_merge($finalArr, $AvgTempArr);
        $minTempArr = $this->getMinHumidityByMonth($sdate);
        $finalArr = array_merge($finalArr, $minTempArr);
        $maxTempArr = $this->getMaxHumidityByMonth($sdate);
        $finalArr = array_merge($finalArr, $maxTempArr);

        return $finalArr;
    }

    public function getMinHumidityByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minHumidity                    
                    from channel_0140_values channelv		
                    where    DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value asc limit 1;";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array();
        if ($row != null && $row['paramdate'] != null) {
            $globalRadAvg = array(
                'minHumiditytime' => $row['valuedate'] ?? '-',
                'minHumidity' => $row['minHumidity'] ?? '-'
            );
        }
        return $globalRadAvg;
    }

    public function getMaxHumidityByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxHumidity                    
                    from channel_0140_values channelv		
                    where   DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value desc limit 1  ";
   
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxRad = array();
        if ($row != null && $row['paramdate'] != null) {
            $maxRad = array(
                'maxHumiditytime' => $row['valuedate'],
                'maxHumidity' => $row['maxHumidity']
            );
        }
        return $maxRad;
    }

    public function getAverageHumidityPerMonth($sDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%m-%Y')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgHumidity 
                    from channel_0140_values channelv		
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
                    'avgHumidity' => $row['avgHumidity']
                );
            }
        }
        return $tempArr;
    }

   

// ************************ *******************************************************

   
}
