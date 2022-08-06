<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelDataCompareDataPage {

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

    /**
     *  The inner function contains 5 parameter,
     *  1 - Method name to be called
     *  2 - wether it List dates - C, Year and Month - YM, D - Default values, XY- date X to Date Y - XY
     *  3 - Date x
     *  4 - Date y 
     *  5 - Date list
     * 
     * @param type $methodName
     * @param type $inputSelection
     * @param type $input1
     * @param type $input2
     * @param type $datelist
     * @return type
     */
    public function getRawResultsWithStartAndEndDates($methodName, $inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->$methodName($date);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectYandM) {
            date_default_timezone_set('UTC');
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = $str_arr[$t];
                $tempArr = $this->$methodName($tempdate);
                if ($tempArr != null) {
                    $mainArr[] = array(
                        'dateData' => $tempArr,
                        'yearDate' => $tempdate
                    );
                }
            }
        } else if ($inputSelection == $selectXtoY) {
            date_default_timezone_set('UTC');
            $date = $input1;
            $end_date = $input2;
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->$methodName($date);
                if ($tempArr != null) {
                    //$mainArr = $tempArr;
                    $mainArr[] = array(
                        'dateData' => $tempArr,
                        'yearDate' => $date
                    );
                }
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = $str_arr[$t];
                $tempArr = $this->$methodName($tempdate);
                if ($tempArr != null) {
                    $mainArr[] = array(
                        'dateData' => $tempArr,
                        'yearDate' => $tempdate
                    );
                }
            }
        }

//         echo "<pre>";
//        print_r($mainArr);
//        echo "<post>";

        return $mainArr;
    }

    function getPerciDailyAllDataSum10Mins($sdate) {

        $sqlQuery = " select  temp10mins.date10mins valuedate,
                            DATE_FORMAT(temp10mins.date10mins, '%H:%i:00' ) valuetime,
                            ifnull(tempdata10mins.value,0) value,
                            CAST(sum(ifnull(tempdata10mins.value,0)) OVER(ORDER BY temp10mins.date10mins) AS DECIMAL(10,2) ) comvalue
                             from 
                            (WITH RECURSIVE seq AS (SELECT 0 AS value UNION ALL SELECT value + 1 FROM seq WHERE value < 143) 
                                            SELECT DATE(:sdate) + INTERVAL (value * 10) MINUTE AS date10mins
                                            FROM seq AS parameter ORDER BY value) temp10mins
                            left join 
                            (SELECT
                                    FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(datatime) / (10*60))
                                                  * (10*60)) AS data10mins,
                                    CAST(sum(value) AS DECIMAL(10,2)) value
                                from channel_0101_values where  date(datatime) between :sdate and :edate
                                GROUP BY data10mins) tempdata10mins
                            on temp10mins.date10mins = tempdata10mins.data10mins ";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':sdate', $sdate);
        $stmt->bindParam(':edate', $sdate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'valuetime' => $row['valuetime'],
                    'value' => $row['value'],
                    'comvalue' => $row['comvalue'],
                );
            }
        }

        return $tempArr;
    }

    function getPerciDailyAllDataSum1Hr($sdate) {

        $sqlQuery = " select tempdatebyhr.datebyhr valuedate,
                    DATE_FORMAT(tempdatebyhr.datebyhr, '%H:00:00' ) valuetime,
                    ifnull(tempdatabyhr.value,0) value ,
                     CAST(sum(ifnull(tempdatabyhr.value,0)) OVER(ORDER BY tempdatebyhr.datebyhr) AS DECIMAL(10,2) ) comvalue
                    from
                    (WITH RECURSIVE seq AS (SELECT 0 AS value UNION ALL SELECT value + 1 FROM seq WHERE value < 23) 
                                    SELECT DATE(:sdate) + INTERVAL (value * 60) MINUTE AS datebyhr 
                                    FROM seq AS parameter ORDER BY value) tempdatebyhr
                     left join      
                    (SELECT
                            DATE_FORMAT(datatime, '%Y-%m-%d %H:00:00' ) datatime,
                            CAST(sum(value) AS DECIMAL(10,2)) value
                        from channel_0101_values where  date(datatime) between :sdate and :edate
                        GROUP BY DATE_FORMAT(datatime, '%Y-%m-%d %H') ) tempdatabyhr
                        on tempdatebyhr.datebyhr = tempdatabyhr.datatime ";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':sdate', $sdate);
        $stmt->bindParam(':edate', $sdate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'valuetime' => $row['valuetime'],
                    'value' => $row['value'],
                    'comvalue' => $row['comvalue'],
                );
            }
        }

        return $tempArr;
    }

    
       function getPerciMonthlyHourlyDailySumByDate($sMonth) {

        $sqlQuery = " select tempdate.datevalue valuedate,
                    DATE_FORMAT(tempdate.datevalue, '%Y-%m-%d' ) valuetime,
                    ifnull(tempdata.value,0) value ,
                     CAST(sum(ifnull(tempdata.value,0)) OVER(ORDER BY tempdate.datevalue) AS DECIMAL(10,2) ) comvalue
                    from
                    (select date datevalue from yeardays
                    where DATE_FORMAT(date , '%Y-%m') = :sMonth) tempdate
                     left join      
                    (SELECT
                            DATE_FORMAT(datatime, '%Y-%m-%d' ) datatime,
                            CAST(sum(value) AS DECIMAL(10,2)) value
                        from channel_0101_values where  DATE_FORMAT(datatime , '%Y-%m') = :sMonth
                        GROUP BY DATE_FORMAT(datatime, '%Y-%m-%d') ) tempdata
                        on tempdate.datevalue = tempdata.datatime ";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':sMonth', $sMonth);        
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'valuetime' => $row['valuetime'],
                    'value' => $row['value'],
                    'comvalue' => $row['comvalue'],
                );
            }
        }

        return $tempArr;
    }
    
    
    
//        public function finPerciLengthByDays($startDate, $endDate) {
//        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d' )) valuedate from channel_0098_values channelv
//                    where channelv.channelid ='0098' and 
//                    DATE_FORMAT(channelv.datatime, '%Y-%m-%d') between :startDate and  :endDate
//                    group by  DATE_FORMAT(channelv.valuedate, '%Y-%m-%d' ) order by channelv.valuedate asc";
//
//        $stmt = $this->conn->prepare($sqlQuery);
//        $stmt->bindParam(":startDate", $startDate);
//        $stmt->bindParam(":endDate", $endDate);
//        $stmt->execute();
//        $itemCount = $stmt->rowCount();
//        if ($itemCount > 0) {
//            $tempArr = array();
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                extract($row);
//                $e = array(
//                    "valuedate" => $valuedate
//                );
//                $perciLength = $this->find1DaysPerciLength($e['valuedate']);
//                array_push($tempArr, $perciLength);
//            }
//
//            return($tempArr);
//        }
//    }

    public function find1DaysPerciLength($tdate) {
        $sqlQuery = "select  distinct valuedate,value from channel_0098_values
			where channelid ='0098' and  date(datatime) =:tdate  order by valuedate asc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $uacnt = count($tempArr);
            $totalTime = 0;
            $time = 0;
            $step = 0;
            $tempval = 0;
            $lasttmptime = 0;
            $skipflag = 0;
            $diff = 0;
            for ($t = 0; $t < $uacnt - 1; $t++) {
                $step = $step + 1;
                $tempvalue = $tempArr[$t];
                $tempval = $tempvalue['value'];
                $lasttmptime = $tempvalue['valuedate'];

                $tempvalueNext = $tempArr[$t + 1];
                $tempvalNext = $tempvalueNext['value'];
                $lasttmptimeNext = $tempvalueNext['valuedate'];


                if (($tempvalue['value'] == 0 && $tempvalNext == 0) || ($tempvalue['value'] == !0 && $tempvalNext == 0)) {

                    continue;
                } else {
                    if (($tempvalue['value'] != 0 && $tempvalNext != 0 ) ||
                            ($tempvalue['value'] == 0 && $tempvalNext != 0 )) {
                        $skipflag = 1;
                        $diff = abs(strtotime($lasttmptimeNext) - strtotime($lasttmptime));
                    }

//                    echo "<pre>";
//                    print_r(" t1-> " . $lasttmptime);
//                    print_r(" t2-> " . $lasttmptimeNext);
//                    echo "<post>";
//                    echo "<pre>";
//                    print_r(" diff " . $diff);
//                    echo "<post>";

                    $totalTime = $totalTime + $diff;
                }
            }

            if ($step + 1 == $uacnt && $tempvalNext != 0) {
                //check if day is reached to next day >
                $totalTime = $totalTime - $diff;
                $datetime = (new DateTime())->format("Y-m-d H:i:s");
                $todaydateonly = (new DateTime())->format("Y-m-d");
                $eventdateonly = (new DateTime($lasttmptime))->format("Y-m-d");
                // First check if event date passed
                if ($todaydateonly > $eventdateonly) {
                    $endevent = $eventdateonly . ' 23:59:59';
                    $eventendtime = (new DateTime($endevent))->format("Y-m-d H:i:s");
                    $diff = abs(strtotime($eventendtime) - strtotime($lasttmptime));
                    $totalTime = $totalTime + $diff;
                }
                // check for same day
                if ($todaydateonly == $eventdateonly && $datetime > $lasttmptime) {
                    $diff = abs(strtotime($datetime) - strtotime($lasttmptime));
                    $totalTime = $totalTime + $diff;
                }
            }

            //Converting to minutes
            // $totalTime = floor($totalTime / (60));
            //$total = gmdate("H:i:s", $totalTime);
            $totalValue = number_format(($totalTime / (60 * 60)), 1);
            $util = new UtilCommon;
            $total = array(
                'total' => $util->secondsToMinsHrs($totalTime),
                'totalvalue' => $totalValue,
                'date' => $tdate,
            );

//            // echo $total;
//            echo "<pre>";
//            print_r($totalValue);
//            echo "<post>";
            return $total;
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
    }
    
    
    function calcperciextremeDay($criteria, $sdate, $edate) {

        if ($sdate == $edate) {
            $criteriaval = $criteria . ' Day';
            $sqlQueryMax = " select CAST(DATE_FORMAT(max(runtime) , '%Y-%m-%d %H') as datetime)   as vlastrun , 
                            CAST(DATE_FORMAT(now() , '%Y-%m-%d %H') as datetime)  as vtimenow 
                            from extremevalues   
                            where criteria = :criteriaval ";
            $stmtMax = $this->conn->prepare($sqlQueryMax);
            $stmtMax->bindParam(':criteriaval', $criteriaval);
            $stmtMax->execute();
            $rowMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $sdate = $rowMax['vlastrun'];
            $edate = $rowMax['vtimenow'];
        }

        $channelname = 'precipitation';
        $stmt = $this->conn->prepare("CALL calculateExtremeDayValues(:channelid, :criteria, :sdate,:edate)");
        $stmt->bindParam(':channelid', $channelname);
        $stmt->bindParam(':criteria', $criteria);
        $stmt->bindParam(':sdate', $sdate);
        $stmt->bindParam(':edate', $edate);
        $stmt->execute();
        echo(" Success " . $criteria . ' sdate ' . $sdate . ' edate ' . $edate);
    }

    private function getPeriodPerci($period) {
        $rangeOptions = array(
            'YR' => 'YR', // Year Rain
            'SR' => 'SR', //Seasonal Rain
            'MR' => 'MR', //Monthly Rain
            'D10R' => '9 day', //10 Day Rain
            'D5R' => '4 day', // 5 Day Rain
            'D3R' => '2 day', // 3 Day Rain
            'D2R' => '1 day', // 2 Day Rain
            'D1R' => '0 day', // Day Rain
            'HR72R' => '72 Hour', //72 hr Rain
            'HR48R' => '48 Hour', // 48 hr Rain
            'HR24R' => '24 Hour', // 24 hr Rain
            'HR12R' => '12 Hour', // 12 hr Rain
            'HR6R' => '6 Hour', // 6 Hr Rain                    
            'HR1R' => '1 Hour', //  full: "1 Hr Rain",                   
            'MIN10R' => '10 Minute', //  full: "10 Min Rain",
            'IR' => 'IR' //  RainIntensity
        );

        $range = $rangeOptions[$period];

        return $range;
    }

    private function getDataStartEndDatePerci($data, $param5, $param6) {

        $startEndDateArr = array();
        $seasionList = ['Winter', 'Spring', 'Summer', 'Autumn'];
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $years;
        $custom = 'C';

        if ($data == 'Y') {
            //Year
        } else if ($data == 'S') {
            //Season            
            if ($param5 == 'Winter') {
                $start = date('12-01-2020'); // hard-coded '01' for first day
                $end = date('02-t-2021');
            } else if ($param5 == 'Spring') {
                $start = date('03-01-2021'); // hard-coded '01' for first day
                $end = date('05-31-2021');
            } else if ($param5 == 'Summer') {
                $start = date('06-01-2021'); // hard-coded '01' for first day
                $end = date('08-31-2021');
            } else if ($param5 == 'Autumn') {
                $start = date('09-01-2021'); // hard-coded '01' for first day
                $end = date('11-30-2021');
            }
        } else if ($data == 'M') {
            //Month
        } else if ($data == 'C') {
            //Custom
        } else if ($data == 'ALL') {
            //All Data
        }
    }

    public function getPercidayRain($period, $minmax, $data, $param5, $param6) {

        $OrderBy = ' desc '; // MAX
        if ($minmax == 'MIN') {
            $OrderBy = ' asc ';
        }
        $range = $this->getPeriodPerci($period);

        $perciStartEndDate = $this->getDataStartEndDatePerci($data, $param5, $param6);

        $sqlQuery = " select  date_add(t.Date, interval -$range ) as fromdt, t.Date as todate ,
            (select sum(t1.value) as sumvalue from 
            (select CAST(sum(channelv.value) AS DECIMAL(10,3) ) as value 
                    from channel_0101_values channelv
                       where channelv.channelid ='0101' and 
                            date(channelv.valuedate)  between 
                      date_add(t.Date, interval -$range) and t.Date
                             group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') ) t1
                     )   as sumvalue
            from yeardays t where t.Date 
            between  '2021-01-07' and '2021-07-31' order by sumvalue desc 
            limit 1 ; ";

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

    public function getPerciHrRain($period, $minmax, $data) {

        $perciPeriod = $this->getPeriodPerci($period);
        $perciPeriod = $this->getDataStartEndDatePerci($period);

        $sqlQuery = " select  date_add(t.Date, interval -1 day) as fromdt, t.Date as todate ,
            (select sum(t1.value) as sumvalue from 
            (select CAST(sum(channelv.value) AS DECIMAL(10,3) ) as value 
                    from channel_0101_values channelv
                       where channelv.channelid ='0101' and 
                            date(channelv.valuedate)  between 
                      date_add(t.Date, interval -1 day) and t.Date
                             group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') ) t1
                     )   as sumvalue
            from yeardays t where t.Date 
            between  '2021-01-07' and '2021-07-31' order by sumvalue desc 
            limit 1 ; ";

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
                       group by  date( t.datatime ) order by t.valuedate;";

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
