<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelPerciDataPage {

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

    public function getPreciTypeByDate($sdate, $edate) {
        $sqlQuery = "select 
                        distinct IF(value=60,1,0) rain  , 
                        value,
                        IF(value=70,1,0) snow, 
                        IF(value=69,1,0) freezRain,
                        IF(value=67,1,0) sleet,
                        IF(value=90,1,0) hail,
                        IF(value=40,1,0) unspeci,
                        (DATE_FORMAT(valuedate, '%H:%i')) valuetime,
                        (DATE_FORMAT(valuedate, '%Y')) valueyear,
                        (DATE_FORMAT(valuedate, '%c')) valuemonth,
                        (DATE_FORMAT(valuedate, '%e')) valueday,
                        (DATE_FORMAT(valuedate, '%k')) valuehr,
                        (DATE_FORMAT(valuedate, '%i')) valuemin,
                        date(datatime) valuedate,
                        DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i') fulldate
                    from channel_0098_values
                    where channelid ='0098'
                    and  date(datatime) between :sdate and :edate 
                    order by DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i') asc";

        $stmtPerciType = $this->conn->prepare($sqlQuery);
        $stmtPerciType->bindParam(":sdate", $sdate);
        $stmtPerciType->bindParam(":edate", $edate);
        $stmtPerciType->execute();
        $itemCount = $stmtPerciType->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmtPerciType->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'rain' => $row['rain'],
                    'snow' => $row['snow'],
                    'freezRain' => $row['freezRain'],
                    'sleet' => $row['sleet'],
                    'hail' => $row['hail'],
                    'unspeci' => $row['unspeci'],
                    'valuetime' => $row['valuetime'],
                    'valueyear' => $row['valueyear'],
                    'valuemonth' => $row['valuemonth'],
                    'valueday' => $row['valueday'],
                    'valuehr' => $row['valuehr'],
                    'valuemin' => $row['valuemin'],
                    'valuedate' => $row['valuedate'],
                    'fulldate' => $row['fulldate'],
                );
            }

            $finalArr = Array();
            $eventArray = $this->getFinalperciRecordData($tempArr, 'rain', 'Rain');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getFinalperciRecordData($tempArr, 'snow', 'Snow');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getFinalperciRecordData($tempArr, 'freezRain', 'Freez Rain');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getFinalperciRecordData($tempArr, 'sleet', 'Sleet');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getFinalperciRecordData($tempArr, 'hail', 'Hail');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getFinalperciRecordData($tempArr, 'unspeci', 'Unspecified');
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getMissingPerciRecordData($finalArr, 'none', 'None', $sdate, $edate);
            $finalArr = array_merge($finalArr, $eventArray);

            usort($finalArr, function ($item1, $item2) {
                return $item1['fullstartdate'] <=> $item2['fullstartdate'];
            });

            return $finalArr;
        }
    }

    function getFinalperciRecordData($tempArr, $event, $eventColVal) {
        $uacnt = count($tempArr);
        $finalArr = array();
        for ($t = 0; $t < $uacnt - 1; $t++) {
            $tempvalue = $tempArr[$t];
            if ($tempvalue[$event] == 0) {
                continue;
            } else {
                $tempvalueStart = $tempArr[$t];
                $t++;
                $tempvalueEnd = $tempArr[$t];
                $finalArr[] = array(
                    'event' => $eventColVal,
                    'fullstartdate' => $tempvalueStart['fulldate'],
                    'fullenddate' => $tempvalueEnd['fulldate'],
                    'valuedate' => $tempvalueStart['valuedate'],
                    'valuetimestart' => $tempvalueStart['valuetime'],
                    'valuetimeend' => $tempvalueEnd['valuetime']
                );
            }
        }
//            if($finalArr==null && count($finalArr)==0){
//                $finalArr[] = array(
//                    'event' => $eventColVal                    
//                );
//            }            
        return $finalArr;
    }

    function getMissingPerciRecordData($tempArr, $event, $eventColVal, $sdate, $edate) {

        $sqlQuery = "select distinct date(datatime) datatime,
             DATE_FORMAT(datatime, '%m-%d-%Y') fulldate from channel_0098  
                where date(datatime) between :sdate and :edate 
                order by DATE_FORMAT(datatime, '%m-%d-%Y') asc ";

        $stmtPerciType = $this->conn->prepare($sqlQuery);
        $stmtPerciType->bindParam(":sdate", $sdate);
        $stmtPerciType->bindParam(":edate", $edate);
        $stmtPerciType->execute();
        $finalArr = array();
        while ($row = $stmtPerciType->fetch(PDO::FETCH_ASSOC)) {
            $datevalue = $row['datatime'];
            $fulldate = $row['fulldate'];
            $uacnt = count($tempArr);
            $flag = 1; //0 false 1 true
            for ($t = 0; $t < $uacnt - 1; $t++) {
                $tempvalue = $tempArr[$t];
                if ($tempvalue['valuedate'] == $datevalue) {
                    $flag = 0;
                    break;
                }
            }
            if ($flag == 1) {
                $finalArr[] = array(
                    'event' => $eventColVal,
                    'fullstartdate' => $fulldate . " 00:00",
                    'fullenddate' => $fulldate . " 00:00",
                    'valuedate' => $datevalue,
                    'valuetimestart' => '00:00',
                    'valuetimeend' => '00:00'
                );
            }
        }
        return $finalArr;
    }

    public function getPerciAmountByDays($startDate, $endDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciamount from channel_0101_values channelv
                    where channelv.channelid ='0101'and  
                    DATE_FORMAT(channelv.datatime, '%Y-%m-%d') between :startDate and  :endDate
                    group by  DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":startDate", $startDate);
        $stmt->bindParam(":endDate", $endDate);
        $stmt->execute();
        return $stmt;
    }

    public function getlastPerciAmountMaxDays($startDate, $endDate) {

        $sqlQuery = " select max(perciamount)maxperciamount from (select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciamount from channel_0101_values channelv
                    where channelv.channelid ='0101' and 
                    DATE_FORMAT(channelv.datatime, '%Y-%m-%d') between :startDate and  :endDate
                    group by  DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') order by channelv.valuedate)t ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":startDate", $startDate);
        $stmt->bindParam(":endDate", $endDate);
        $stmt->execute();
        return $stmt;
    }

    public function finPerciLengthByDays($startDate, $endDate) {
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d' )) valuedate from channel_0098_values channelv
                    where channelv.channelid ='0098' and 
                    DATE_FORMAT(channelv.datatime, '%Y-%m-%d') between :startDate and  :endDate
                    group by  DATE_FORMAT(channelv.valuedate, '%Y-%m-%d' ) order by channelv.valuedate asc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":startDate", $startDate);
        $stmt->bindParam(":endDate", $endDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate
                );
                $perciLength = $this->find1DaysPerciLength($e['valuedate']);
                array_push($tempArr, $perciLength);
            }

            return($tempArr);
        }
    }

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
            $diff=0;
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
                $totalTime=$totalTime - $diff;
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

    public function findLastPrecipitation() {
        $sqlQuery = "select  IFNULL(CAST((t.value) AS DECIMAL(10,2)),0) perci ,
                    DATE_FORMAT(t.datatime, '%m-%d-%Y %H:%i') lastpercitime,
                    DATE_FORMAT(k.datatime, '%m-%d-%Y %H:%i') valuedate ,
                    DATE_FORMAT(k.datatime, '%H:%i') datepersetime
                    , TIMESTAMPDIFF(SECOND,t.valuedate,k.datatime ) diff 
                      from channel_0101_values t left join 
                      (
                            select channelid, max(datatime) as datatime  from channel_0101
                      ) k
                            on t.channelid = k.channelid where t.channelid ='0101'  and t.value > 0                      
                                order by DATE_FORMAT(t.valuedate, '%m-%d-%Y %H:%i') desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function findPrecipitationThisWeek() {
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%v'))  perciweek , 
                        CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciTweek
                        from channel_0101_values channelv
                       where channelv.channelid ='0101' and 
                        DATE_FORMAT((channelv.datatime),'%v') = (select max(DATE_FORMAT((datatime),'%v')) 
                        from channel_0101_values where  channelid ='0101')";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    /**
     * 
     * @param type $days (7 Days or 31 days)
     * @return type
     */
    public function findPrecipitationByNoOfDays($days) {
        $sqlQuery = "select CAST((sum(channelv.value)) AS DECIMAL(10,2)) perciLastXDays ,
                        date( channelv.valuedate ) da
                        from channel_0101_values channelv
                        where channelv.channelid ='0101' 
                        and  ( date(channelv.datatime) >= date_add((select max(datatime) 
                    from channel_0101_values where  channelid ='0101'), interval :days DAY))";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        return $stmt;
    }

    public function findPrecipitationThisMonth() {
        $sqlQuery = "select  CAST(sum(channelv.value) AS DECIMAL(10,2))  perciThisMonth 
                     from channel_0101_values channelv
                       where channelv.channelid ='0101' and  
                       DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) 
                       from channel_0101_values where  channelid ='0101')";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function findPrecipitationThisYear() {
        $sqlQuery = "select  CAST(sum(channelv.value) AS DECIMAL(10,2))  perciThisYear 
                        from channel_0101_values channelv
                       where channelv.channelid ='0101' and  
                       DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) 
                       from channel_0101_values where  channelid ='0101')";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    /**
     * 'show date of last events where ID '0098 (at least one reading per day) 
     *  is positive (i.e. unequal 0)  and '0101 (daily sum) is equal to 0 
     * @param type $min
     * @param type $max
     * @return array    
     */
    public function findPrecipitationBucketsSpecial() {
        $sqlQuery = " select 'Rain' value, DATE_FORMAT(c1.valuedate, '%Y-%m-%d') valuedate
                        from 
                       (select CAST(sum(value) as DECIMAL(10,2))as value, DATE_FORMAT(valuedate, '%Y-%m-%d') valuedate 
                       from channel_0101_values
                       group by date(valuedate)
                       ) c1 
                       join (select  count(value) value, valuedate from
                       channel_0098_values where 
                       value > 0  
                       group by date(valuedate) ) c2
                       on date(c1.valuedate)= date(c2.valuedate)
                       and c2.value >0 and c1.value=0 order by date(c1.valuedate) desc";

        $stmt = $this->conn->prepare($sqlQuery);
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
//             echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
            return $tempArr;
        }
    }

    public function findPrecipitationBuckets($min, $max) {
        $sqlQuery = "select CAST(sum(value) AS DECIMAL(10,2)) as  value
                       , DATE_FORMAT(valuedate, '%Y-%m-%d') as valuedate
                    from channel_0101_values
                    where channelid ='0101' 
                    group by  DATE_FORMAT(valuedate, '%Y-%m-%d') 
                    having CAST(sum(value) AS DECIMAL(10,2)) >= :minvalue and CAST(sum(value) AS DECIMAL(10,2)) < :maxvalue
                    order by DATE_FORMAT(valuedate, '%Y-%m-%d') desc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":minvalue", $min);
        $stmt->bindParam(":maxvalue", $max);
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
//             echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
            return $tempArr;
        }
    }

    /**
     * 
     * @param type $days (7 Days or 31 days)
     * @return type
     */
    public function findPrecipitationMonthComapre($year) {
//        $sqlQuery = "select CAST((sum(channelv.value)) AS DECIMAL(10,2)) perciLastXDays ,
//                        date( channelv.valuedate ) da
//                        from channel_0101_values channelv
//                        where channelv.channelid ='0101' 
//                        and  ( date(channelv.datatime) >= date_add((select max(datatime) 
//                    from channel_0101_values where  channelid ='0101'), interval :days DAY))";
//
//        $stmt = $this->conn->prepare($sqlQuery);
//        $stmt->bindParam(":days", $days);
//        $stmt->execute();
//        return $stmt;        

        $data = Array(
            'data2020' => array(
                'year' => '2020',
                array(
                    'month' => 'January',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'February',
                    'perciValue' => 11,
                    'perciNormValue' => 6,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'March',
                    'perciValue' => 1.8,
                    'perciNormValue' => .6,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'April',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'May',
                    'perciValue' => 8,
                    'perciNormValue' => 3,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'June',
                    'perciValue' => 7,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'July',
                    'perciValue' => 15,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'August',
                    'perciValue' => 20,
                    'perciNormValue' => 15,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'September',
                    'perciValue' => 9,
                    'perciNormValue' => 6,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'October',
                    'perciValue' => 3,
                    'perciNormValue' => 1.5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'November',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'December',
                    'perciValue' => 6,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                )
            ),
            'data2019' => array(
                'year' => '2019',
                array(
                    'month' => 'January',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'February',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'March',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'April',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'May',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'June',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'July',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'August',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'September',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'October',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'November',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'December',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                )
            ),
            'data2018' => array(
                'year' => '2018',
                array(
                    'month' => 'January',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'February',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'March',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'April',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'May',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'June',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'July',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'August',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'September',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'October',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'November',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'December',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                )
            ),
            'data2017' => array(
                'year' => '2017',
                array(
                    'month' => 'January',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'February',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'March',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'April',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'May',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'June',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'July',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'August',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'September',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'October',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'November',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                ),
                array(
                    'month' => 'December',
                    'perciValue' => 10,
                    'perciNormValue' => 5,
                    'abwinPercent' => '10%',
                    'range' => 8
                )
            )
        );

        return $data['data' . $year];
    }

    public function findPrecipitationYearComapre() {

        $datayear = array(
            array(
                'year' => '2020',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            ),
            array(
                'year' => '2019',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            ),
            array(
                'year' => '2018',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            ),
            array(
                'year' => '2017',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            ),
            array(
                'year' => '2016',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            ),
            array(
                'year' => '2015',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2014',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2013',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2012',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2011',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2010',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2009',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
            ,
            array(
                'year' => '2008',
                'perciYear' => 204.44,
                'perciYearNorm' => 200.44,
                'diff' => 4.44
            )
        );

        return $datayear;
    }

    public function getPerciCompAginstNorm($year) {
        $sqlQuery = "select t.valuedate as valuedate,
                        fullmonth
                        ,CAST(t.value AS DECIMAL(10,1) ) as value, dense_rank() 
                        OVER ( partition by DATE_FORMAT(valuedate, '%m-%Y') order by value desc ) 
                                AS rankvalue ,
                         t.months ,
                         t.norm,
                         CAST((t.value/t.norm)*100 AS DECIMAL(10,1) ) abw
                            from    
                        (select                       
                           DATE_FORMAT(valuedate, '%m-%Y') valuedate,
                           SUM(value) value ,
                            (select value from norm_perci_values where monthvalue =  DATE_FORMAT(datatime, '%c')) norm ,
                            DATE_FORMAT(datatime, '%M') fullmonth,
                            DATE_FORMAT(datatime, '%m') months
                           from channel_0101_values
                           where channelid ='0101'
                           and  DATE_FORMAT(valuedate, '%Y') = :year  
                           group by DATE_FORMAT(valuedate, '%m-%Y') 
                           order by DATE_FORMAT(valuedate, '%m-%Y') asc )t 
                           order by valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate,
                    "fullmonth" => $fullmonth,
                    "value" => $value,
                    "rankvalue" => $rankvalue,
                    "months" => $months,
                    "norm" => $norm,
                    "abw" => $abw,
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciCompAginstNormYear($noOfYear) {
        $sqlQuery = "select                       
                    DATE_FORMAT(valuedate, '%Y') valuedate,
                    CAST(sum(value) AS DECIMAL(10,2) ) as value ,
                   (select CAST(sum(value) AS DECIMAL(10,2) ) as normvalue from norm_perci_values) as norm
                    from channel_0101_values
                    where channelid ='0101'                     
                    group by DATE_FORMAT(valuedate, '%Y') 
                    order by DATE_FORMAT(valuedate, '%Y')
                    limit $noOfYear  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "year" => $valuedate,
                    "perciyear" => $value,
                    "perciyearnorm" => $norm,
                    "diff" => number_format(($value - $norm), 2)
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciRainBalanceByDay($inputDate, $inputDay) {
        // Set timezone
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        $tempArr = $this->getMinAvailableDates('0101');
        $availableDate = date('Y-m-d', strtotime($tempArr['minimumdate']));
        if (strtotime($availableDate) >= strtotime($date)) {
            $date = $availableDate;
        }

        date_default_timezone_set('UTC');
        $end_date = $inputDate;
        $mainArr = array();
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getPerciRainBalanceValues($date, $inputDay);
            $mainArr[] = $tempArr;
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
//         echo "<pre>";
//         print_r($mainArr);
//         echo "<post>";
        return $mainArr;
    }

    public function InsertPerciRainBalanceByDay($inputDate, $inputDay) {
        // Set timezone
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        date_default_timezone_set('UTC');
        $end_date = $inputDate;
        $tablename;
        if ($inputDay == "30") {
            $tablename = "rainbalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "rainbalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "rainbalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "rainbalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "rainbalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "rainbalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "rainbalance365Days";
        }

        $sql = "TRUNCATE TABLE $tablename";
        $truncstmt = $this->conn->prepare($sql);
        $truncstmt->execute();

        $stmt = $this->conn->prepare("INSERT INTO $tablename(inputdate, sumvalue, avgnorm,percent)"
                . "                  VALUES (:inputdate, :sumvalue,:avgnorm, :percent)");

        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getPerciRainBalance($date, $inputDay);
            $tempvalue = $tempArr[0];
            $inputdate = $tempvalue['inputdate'];
            $sumvalue = $tempvalue['sumvalue'];
            $avgnorm = $tempvalue['avgnorm'];
            $percent = $tempvalue['percent'];
            $stmt->bindValue(":inputdate", $inputdate);
            $stmt->bindValue(":sumvalue", $sumvalue);
            $stmt->bindValue(":avgnorm", $avgnorm);
            $stmt->bindValue(":percent", $percent);
            $stmt->execute();
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        echo "successful inserted";
    }

    public function getPerciRainBalanceValues($inputDate, $inputDay) {

        $tablename;
        if ($inputDay == "30") {
            $tablename = "rainbalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "rainbalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "rainbalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "rainbalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "rainbalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "rainbalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "rainbalance365Days";
        }

        $sqlQuery = "select inputdate,sumvalue,avgnorm,percent from "
                . " $tablename where inputdate= :indate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvalue" => $sumvalue,
                    "avgnorm" => $avgnorm,
                    "percent" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciRainBalance($inputDate, $inputDay) {
        $sqlQuery = "select sum(t2.value) sumvalue ,CAST( sum(t1.norm) AS DECIMAL(10,3) ) avgnorm ,  
                    CAST( (sum(t2.value)/sum(t1.norm))*100 AS DECIMAL(10,2) ) percent ,
                    t1.datatime
                    from 
                    (select  distinct DATE_FORMAT(datatime, '%d-%m-%Y') datatime ,
                    (select CAST((value) AS DECIMAL(10,3) ) as normvalue
                    from norm_perci_values where 
                    monthvalue = DATE_FORMAT(datatime, '%c')
                    )/day(last_day(datatime)) norm 
                    from  channel_0101 
                    where  date(datatime) > 
                        date_add(:indate, interval -:inday day) and date(datatime) <= :indate
                     ) t1 left join
                     (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                        where channelv.channelid ='0101'  
                        and 
                        date(channelv.datatime) > 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') ) t2                    
                        on t1.datatime = t2.valuedate
                        order by DATE_FORMAT(t1.datatime, '%d-%m-%Y') asc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->bindParam(":inday", $inputDay);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvalue" => $sumvalue,
                    "avgnorm" => $avgnorm,
                    "percent" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciRainBalanceMonthAgoValue($inputDate, $inputDay) {

        $tablename;
        if ($inputDay == "30") {
            $tablename = "rainbalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "rainbalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "rainbalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "rainbalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "rainbalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "rainbalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "rainbalance365Days";
        }

        $sqlQuery = "select inputdate,sumvalue,avgnorm,percent from $tablename where inputdate= 
                        date_add( :indate, interval -1 MONTH) ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvaluemonth" => $sumvalue,
                    "avgnormmonth" => $avgnorm,
                    "percentmonth" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciCumulativeRainByYear($inputYearFrom, $inputYearTo) {
        $diff = $inputYearTo - $inputYearFrom;
        $yearFrom = $inputYearFrom;
        $finalArr = Array();
        for ($t = 0; $t < $diff + 1; $t++) {
            $tempArr = $this->getPerciCumulativeRain($yearFrom);            
            if ($tempArr == null) {
                $tempArr = [];
            } else {
                $mainArray = array(
                    'yearData' => $tempArr,
                    'years' => $yearFrom
                );
              array_push($finalArr, $mainArray);
            }
            $yearFrom = $yearFrom + 1;
           
        }

        $tempArr = $this->getPerciCumulativeNormValue();        
        $mainArray = array(
            'yearData' => $tempArr,
            'years' => 'norm'
        );

        array_push($finalArr, $mainArray);

        return $finalArr;
    }

    public function getPerciCumulativeRain($inputYear) {
        $sqlQuery = "select DATE_FORMAT(t.datatime, '%Y-%m-%d') datatime , 
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ) value from
                    (
                        select t1.datatime,t1.datayear, ifnull(value,0.00) value from 
                   (select distinct DATE_FORMAT(datatime, '%Y-%m-%d') datatime,
                   DATE_FORMAT(datatime, ' %Y') datayear   
                   from channel_0101) t1 left join 
                   (select DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,2) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and   DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0101_values where  channelid ='0101') 
                   and channelv.value >0
                   group by  DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') ) t2
                   on t1.datatime =t2.valuedate   
                   order by datatime   
                   ) t
                   where t.datayear  = $inputYear 
                   group by  datatime";

        $stmt = $this->conn->prepare($sqlQuery);
        //$stmt->bindParam(":inyear", $inputYear);        
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $datatime,
                    "datavalue" => $datavalue,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciCumulativeNormValue() {
        $sqlQuery = " select  datatime , CAST(t.norm AS DECIMAL(10,2)) datavalue, 
            CAST(SUM(t.norm) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ) value 
            from
            (select distinct  date(datatime) datatime ,
            (select CAST((value) AS DECIMAL(10,3) ) as normvalue
          from norm_perci_values where 
          monthvalue = DATE_FORMAT(datatime, '%c')
          )/day(last_day(datatime)) norm 
           from  channel_0101) t order by date(datatime) asc ";

        $stmt = $this->conn->prepare($sqlQuery);
        //$stmt->bindParam(":inyear", $inputYear);        
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $datatime,
                    "datavalue" => $datavalue,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getPerciFrequencyEventsByYear($inputYearFrom, $inputYearTo, $operator, $inputvalue) {
        $diff = $inputYearTo - $inputYearFrom;
        $yearFrom = $inputYearFrom;
        $finalArr = Array();
        for ($t = 0; $t < $diff + 1; $t++) {
            $tempArr = $this->getPerciFrequencyEvents($yearFrom, $operator, $inputvalue);
            if ($tempArr == null) {
                $tempArr = [];
                continue;
            }
            $yearFrom = $yearFrom + 1;
            array_push($finalArr, $tempArr);
        }
        return $finalArr;
    }

    public function getPerciFrequencyEvents($inputYear, $operator, $inputvalue) {
        $operate = '>';
        if ($operator == 'GT') {
            //GT > greate than sign
            $operate = '>=';
        } else if ($operator == 'LT') {
            //LT < greate than sign
            $operate = '<=';
        }

        $sqlQuery = "select count(*) days , t1.yearvalue from     
                    (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate, DATE_FORMAT(channelv.valuedate, '%Y') years,
                      CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                      where channelv.channelid ='0101'
                      and DATE_FORMAT((channelv.datatime),'%Y') = :inyear
                      group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y')  
                      )t join 
                      (select DATE_FORMAT(channelv.valuedate, '%Y') years, 
                      CAST(SUM(channelv.value) AS DECIMAL(10,3) ) yearvalue from channel_0101_values channelv
                      where channelv.channelid ='0101'
                      and DATE_FORMAT((channelv.datatime),'%Y') = :inyear )t1
                      on t.years=t1.years
                      where t.value $operate :invalue ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":inyear", $inputYear);
        $stmt->bindParam(":invalue", $inputvalue);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "year" => $inputYear,
                "days" => $row['days'],
                "perci" => $row['yearvalue'] ?? 0
            );
            return $tempArr;
        }
    }

    public function getPerciDryPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value
                        from channel_0101_values channelv
                        where channelv.channelid ='0101'  
                        and 
                        date(channelv.datatime) >= :infromdate and date(channelv.datatime) <= :intodate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') 
                        order by date(channelv.datatime)";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":infromdate", $inputFromDate);
        $stmt->bindParam(":intodate", $inputToDate);
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
        }
        $finalArr = Array();
        $calcarr = Array();
        $cnt = 1;
        $firstDate = null;
        $lastDate = null;
        $totalArrCnt = count($tempArr);
        for ($t = 0; $t < count($tempArr) - 1; $t++) {
            $prevItem = $tempArr[$t];
            $nextItem = $tempArr[$t + 1];
            $prevValue = $prevItem['value'];
            $nextValue = $nextItem['value'];
            if ($prevValue <= $inputValue && $nextValue <= $inputValue) {
                if ($cnt == 1) {
                    $firstDate = $prevItem['valuedate'];
                }
                $cnt = $cnt + 1;
                if ($totalArrCnt - 2 == $t) {
                    $lastDate = $nextItem['valuedate'];
                    $calcarr = array(
                        "firstdate" => $firstDate,
                        "lastdate" => $lastDate,
                        "countvalue" => $cnt
                    );
                    array_push($finalArr, $calcarr);
                }
            } else {
                if ($cnt > 1) {
                    $lastDate = $prevItem['valuedate'];
                    $calcarr = array(
                        "firstdate" => $firstDate,
                        "lastdate" => $lastDate,
                        "countvalue" => $cnt
                    );
                    array_push($finalArr, $calcarr);
                }
                $cnt = 1;
                $firstDate = null;
                $lastDate = null;
                continue;
            }
        }

        return $finalArr;
    }

    public function getPerciWetPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value
                        from channel_0101_values channelv
                        where channelv.channelid ='0101'  
                        and 
                        date(channelv.datatime) >= :infromdate and date(channelv.datatime) <= :intodate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') 
                        order by date(channelv.datatime)";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":infromdate", $inputFromDate);
        $stmt->bindParam(":intodate", $inputToDate);
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
        }
        $finalArr = Array();
        $calcarr = Array();
        $cnt = 1;
        $firstDate = null;
        $lastDate = null;
        $totalArrCnt = count($tempArr);
        for ($t = 0; $t < count($tempArr) - 1; $t++) {
            $prevItem = $tempArr[$t];
            $nextItem = $tempArr[$t + 1];
            $prevValue = $prevItem['value'];
            $nextValue = $nextItem['value'];
            if ($prevValue >= $inputValue && $nextValue >= $inputValue) {
                if ($cnt == 1) {
                    $firstDate = $prevItem['valuedate'];
                }
                $cnt = $cnt + 1;
                if ($totalArrCnt - 2 == $t) {
                    $lastDate = $nextItem['valuedate'];
                    $calcarr = array(
                        "firstdate" => $firstDate,
                        "lastdate" => $lastDate,
                        "countvalue" => $cnt
                    );
                    array_push($finalArr, $calcarr);
                }
            } else {
                if ($cnt > 1) {
                    $lastDate = $prevItem['valuedate'];
                    $calcarr = array(
                        "firstdate" => $firstDate,
                        "lastdate" => $lastDate,
                        "countvalue" => $cnt
                    );
                    array_push($finalArr, $calcarr);
                }
                $cnt = 1;
                $firstDate = null;
                $lastDate = null;
                continue;
            }
        }
        return $finalArr;
    }

    public function getPreciDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getPreciDailyOverviewByDate($date);
                $mainArr[] = $tempArr;
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectYandM) {
            date_default_timezone_set('UTC');
            $end_date = date("Y-m-t", strtotime($input1));
            $date = date("Y-m-d", strtotime($input1));
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->getPreciDailyOverviewByDate($date);
                $mainArr[] = $tempArr;
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectXtoY) {
            //$date = date('Y-m-d', strtotime('-31 days', strtotime($input1)));
            date_default_timezone_set('UTC');
            $date = $input1;
            $end_date = $input2;
            while (strtotime($date) <= strtotime($end_date)) {
                $tempArr = $this->getPreciDailyOverviewByDate($date);
                $mainArr[] = $tempArr;
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = $str_arr[$t];
                $tempArr = $this->getPreciDailyOverviewByDate($tempdate);
                $mainArr[] = $tempArr;
            }
        }



//        echo "<pre>";
//        print_r($mainArr);
//        echo "<post>";
        return $mainArr;
    }

    public function getPreciDailyOverviewByDate($sdate) {

        $sqlQuery = "select 
                        distinct IF(value=60,1,0) rain  , 
                        value,
                        IF(value=70,1,0) snow, 
                        IF(value=69,1,0) freezRain,
                        IF(value=67,1,0) sleet,
                        IF(value=90,1,0) hail,
                        IF(value=40,1,0) unspeci,
                        (DATE_FORMAT(valuedate, '%H:%i')) valuetime,
                        (DATE_FORMAT(valuedate, '%Y')) valueyear,
                        (DATE_FORMAT(valuedate, '%c')) valuemonth,
                        (DATE_FORMAT(valuedate, '%e')) valueday,
                        (DATE_FORMAT(valuedate, '%k')) valuehr,
                        (DATE_FORMAT(valuedate, '%i')) valuemin,
                        date(datatime) valuedate,
                        DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i') fulldate
                    from channel_0098_values
                    where channelid ='0098'
                    and  date(datatime) between :sdate and :edate 
                    order by datatime asc";

        $stmtPerciType = $this->conn->prepare($sqlQuery);
        $stmtPerciType->bindParam(":sdate", $sdate);
        $stmtPerciType->bindParam(":edate", $sdate);
        $stmtPerciType->execute();
        $itemCount = $stmtPerciType->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmtPerciType->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'rain' => $row['rain'],
                    'snow' => $row['snow'],
                    'freezRain' => $row['freezRain'],
                    'sleet' => $row['sleet'],
                    'hail' => $row['hail'],
                    'unspeci' => $row['unspeci'],
                    'valuetime' => $row['valuetime'],
                    'valueyear' => $row['valueyear'],
                    'valuemonth' => $row['valuemonth'],
                    'valueday' => $row['valueday'],
                    'valuehr' => $row['valuehr'],
                    'valuemin' => $row['valuemin'],
                    'valuedate' => $row['valuedate'],
                    'fulldate' => $row['fulldate'],
                );
            }


            $finalArr = Array();
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'rain', 'Rain', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'snow', 'Snow', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'freezRain', 'Freez Rain', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'sleet', 'Sleet', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'hail', 'Hail', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);
            $eventArray = $this->getPerciTimeRecordData($tempArr, 'unspeci', 'Unspecified', $sdate);
            $finalArr = array_merge($finalArr, $eventArray);

            $perciAmountStmt = $this->getPerciAmountByDays($sdate, $sdate);
            $row = $perciAmountStmt->fetch(PDO::FETCH_ASSOC);
            $perciAmountArr[] = array(
                'percidate' => $row['valuedate'] ?? '-',
                'perciamount' => $row['perciamount'] ?? 0
            );
            $finalArr = array_merge($finalArr, $perciAmountArr);
            $percilengthArr[] = $this->find1DaysPerciLength($sdate);
            $finalArr = array_merge($finalArr, $percilengthArr);

            $perciFirst = $this->findFirstLastPrecipitationByDay($sdate, 'first');
            $finalArr = array_merge($finalArr, $perciFirst);
            $perciLast = $this->findFirstLastPrecipitationByDay($sdate, 'last');
            $finalArr = array_merge($finalArr, $perciLast);
            $percimax = $this->findMaxPrecipitationByDay($sdate);
            $finalArr = array_merge($finalArr, $percimax);


//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";

            return $finalArr;
        }
    }

    function getPerciTimeRecordData($tempArr, $event, $eventColVal, $sdate) {
        $uacnt = count($tempArr);
        $finalArr = Array();
        $totalTime = 0;
        $step = 0;
        $tempval = 0;
        $lasttmptime = 0;
        $skipflag = 0;
        $diff = 0;
        for ($t = 0; $t < $uacnt - 1; $t++) {
            $step = $step + 1;
            $tempvalue = $tempArr[$t];
            $tempval = $tempvalue[$event];
            $lasttmptime = $tempvalue['fulldate'];
            $tempvalueNext = $tempArr[$t + 1];
            $tempvalNext = $tempvalueNext[$event];
            $lasttmptimeNext = $tempvalueNext['fulldate'];

            //echo' valueFirst '.$tempvalue[$event].' valueNext '.$tempvalNext;

            if (($tempvalue[$event] == 0 && $tempvalNext == 0) || ($tempvalue[$event] == !0 && $tempvalNext == 0)) {
                continue;
            } else {

                if (($tempvalue[$event] != 0 && $tempvalNext != 0 ) ||
                        ($tempvalue[$event] == 0 && $tempvalNext != 0 )) {
                    $skipflag = 1;
                    $diff = abs(strtotime($lasttmptimeNext) - strtotime($lasttmptime));
                }
                $totalTime = $totalTime + $diff;
            }
        }
        if ($step + 1 == $uacnt && $tempvalNext != 0) {
            $totalTime = $totalTime - $diff;
            //check if day is reached to next day >
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

//                        echo "<pre>";
//                        echo $sdate.' valueFirst *** '.$tempvalue[$event].' valueNext '.$tempvalNext;
//                        echo "<post>";
            }
        }

        $util = new UtilCommon;
        $totaleventtime = $util->secondsToMinsHrs($totalTime);
        $finalArr[] = array(
            'event' => $eventColVal,
            'valuedate' => $sdate,
            'totaleventtime' => $totaleventtime ?? ''
        );

        return $finalArr;
    }

    public function findFirstLastPrecipitationByDay($sdate, $perciflag) {

        $perciname = 'firstperci';
        $queryorder = 'asc';
        if ($perciflag != 'first') {
            $perciname = 'lastperci';
            $queryorder = 'desc';
        }


        $sqlQuery = " select date(datatime) valuedate, 
                    DATE_FORMAT(valuedate, '%H:%i') perci from channel_0098_values
                    where channelid ='0098' 
                    and date(datatime) =:datevalue
                    and value > 0
                    order by datatime $queryorder
                    limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":datevalue", $sdate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $perciAmountArr[] = array(
            'percidate' => $sdate,
            $perciname => $row['perci'] ?? '-'
        );

        return $perciAmountArr;
    }

    public function findMaxPrecipitationByDay($sdate) {

        $sqlQuery10Mins = "select  date(datatime) valueDate, max(value) max10Minutesval 
                    , DATE_FORMAT(valuedate, '%H:%i') maxtime10Min
                     from 
                     channel_0103_values 
                     where date(datatime) =:datevalue ";
        $stmt10Mins = $this->conn->prepare($sqlQuery10Mins);
        $stmt10Mins->bindParam(":datevalue", $sdate);
        $stmt10Mins->execute();
        $row10Mins = $stmt10Mins->fetch(PDO::FETCH_ASSOC);
        $max10MinVal = $row10Mins['max10Minutesval'] ?? 0;
        $max10MinTime = $row10Mins['maxtime10Min'] ?? '-';

        $sqlQuery1Hr = " select t.datatime valueDate , CAST((max(t.value)) AS DECIMAL(10,2) )  max1Hrval,
                        hour( datatime ) maxtime1Hr
                        from                      
                        (SELECT date(datatime) datatime, sum(value) value
                        FROM channel_0103_values
                        where date(datatime) =:datevalue
                        GROUP BY hour( datatime ) , date(datatime) )t ";
        
        $stmt1Hr = $this->conn->prepare($sqlQuery1Hr);
        $stmt1Hr->bindParam(":datevalue", $sdate);
        $stmt1Hr->execute();
        $row1Hr = $stmt1Hr->fetch(PDO::FETCH_ASSOC);
        $max1Hrval = $row1Hr['max1Hrval'] ?? 0;
        $maxtime1Hr = $row1Hr['maxtime1Hr'] ?? '-';

        $sqlQueryMaxIntensity = "select  date(datatime) valueDate, max(value) maxintensity,
                                DATE_FORMAT(valuedate, '%H:%i') maxtimeinten
                                from channel_0100_values 
                                where date(datatime) =:datevalue ";
        $stmtMaxIntensity = $this->conn->prepare($sqlQueryMaxIntensity);
        $stmtMaxIntensity->bindParam(":datevalue", $sdate);
        $stmtMaxIntensity->execute();
        $rowMaxIntensity = $stmtMaxIntensity->fetch(PDO::FETCH_ASSOC);
        $maxintensity = $rowMaxIntensity['maxintensity'] ?? 0;
        $maxtimeinten = $rowMaxIntensity['maxtimeinten'] ?? '-';

        $perciMaxPerciArr[] = array(
            'percidate' => $sdate,
            'max10MinVal' => $max10MinVal,
            'max10MinTime' => $max10MinTime,
            'max1Hrval' => $max1Hrval,
            'maxtime1Hr' => $maxtime1Hr,
            'maxintensity' => $maxintensity,
            'maxtimeinten' => $maxtimeinten
        );

        return $perciMaxPerciArr;
    }

    public function getPreciDailyOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getPreciDailyOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getPreciDailyOverviewByMonth($monthYear);
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
                $tempArr = $this->getPreciDailyOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getPreciDailyOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

//        echo "<pre>";
//        print_r($mainArr);
//        echo "<post>";
        return $mainArr;
    }

    public function getPreciDailyOverviewByMonth($sMonth) {

        $startDate = "01-" . $sMonth;
        $date = (new DateTime($startDate))->format('Y-m-d');
        $end_date = date("Y-m-t", strtotime($date));

        $eventRain = 0;
        $eventSnow = 0;
        $eventFrain = 0;
        $eventSleet = 0;
        $eventHail = 0;
        $eventUnspeci = 0;
        $percilength = 0;
        while (strtotime($date) <= strtotime($end_date)) {

            $sqlQuery = "select 
                        distinct IF(value=60,1,0) rain  , 
                        value,
                        IF(value=70,1,0) snow, 
                        IF(value=69,1,0) freezRain,
                        IF(value=67,1,0) sleet,
                        IF(value=90,1,0) hail,
                        IF(value=40,1,0) unspeci,
                        (DATE_FORMAT(valuedate, '%H:%i')) valuetime,
                        (DATE_FORMAT(valuedate, '%Y')) valueyear,
                        (DATE_FORMAT(valuedate, '%c')) valuemonth,
                        (DATE_FORMAT(valuedate, '%e')) valueday,
                        (DATE_FORMAT(valuedate, '%k')) valuehr,
                        (DATE_FORMAT(valuedate, '%i')) valuemin,
                        date(datatime) valuedate,
                        DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i') fulldate
                    from channel_0098_values
                    where channelid ='0098'
                    and  date(datatime) = :sdate and :edate 
                    order by datatime asc";

            $stmtPerciType = $this->conn->prepare($sqlQuery);
            $stmtPerciType->bindParam(":sdate", $date);
            $stmtPerciType->bindParam(":edate", $date);
            $stmtPerciType->execute();
            $itemCount = $stmtPerciType->rowCount();

            if ($itemCount > 0) {
                $tempArr = array();
                while ($row = $stmtPerciType->fetch(PDO::FETCH_ASSOC)) {
                    $tempArr[] = array(
                        'rain' => $row['rain'],
                        'snow' => $row['snow'],
                        'freezRain' => $row['freezRain'],
                        'sleet' => $row['sleet'],
                        'hail' => $row['hail'],
                        'unspeci' => $row['unspeci'],
                        'valuetime' => $row['valuetime'],
                        'valueyear' => $row['valueyear'],
                        'valuemonth' => $row['valuemonth'],
                        'valueday' => $row['valueday'],
                        'valuehr' => $row['valuehr'],
                        'valuemin' => $row['valuemin'],
                        'valuedate' => $row['valuedate'],
                        'fulldate' => $row['fulldate'],
                    );
                }

                $eventRain = $eventRain + $this->getPerciMonthRecordData($tempArr, 'rain', 'Rain', $date);
                $eventSnow = $eventSnow + $this->getPerciMonthRecordData($tempArr, 'snow', 'Snow', $date);
                $eventFrain = $eventFrain + $this->getPerciMonthRecordData($tempArr, 'freezRain', 'Freez Rain', $date);
                $eventSleet = $eventSleet + $this->getPerciMonthRecordData($tempArr, 'sleet', 'Sleet', $date);
                $eventHail = $eventHail + $this->getPerciMonthRecordData($tempArr, 'hail', 'Hail', $date);
                $eventUnspeci = $eventUnspeci + $this->getPerciMonthRecordData($tempArr, 'unspeci', 'Unspecified', $date);
                $percilength = $percilength + $this->find1MonthPerciLength($date);
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }

        $finalArr = Array();
        $util = new UtilCommon;
        $totaleventtime = $util->secondsToDayMinsHrs($eventRain);
        $finalArr[] = array(
            'event' => 'Rain',
            'totaleventtime' => $totaleventtime ?? ''
        );

        $totaleventtime = $util->secondsToDayMinsHrs($eventSnow);
        $finalArr[] = array(
            'event' => 'Snow',
            'totaleventtime' => $totaleventtime ?? ''
        );
        $totaleventtime = $util->secondsToDayMinsHrs($eventFrain);
        $finalArr[] = array(
            'event' => 'Freez Rain',
            'totaleventtime' => $totaleventtime ?? ''
        );
        $totaleventtime = $util->secondsToDayMinsHrs($eventSleet);
        $finalArr[] = array(
            'event' => 'Sleet',
            'totaleventtime' => $totaleventtime ?? ''
        );
        $totaleventtime = $util->secondsToDayMinsHrs($eventHail);
        $finalArr[] = array(
            'event' => 'Hail',
            'totaleventtime' => $totaleventtime ?? ''
        );
        $totaleventtime = $util->secondsToDayMinsHrs($eventUnspeci);
        $finalArr[] = array(
            'event' => 'Unspecified',
            'totaleventtime' => $totaleventtime ?? ''
        );


        $perciAmountStmt = $this->getPerciAmountByMonth($sMonth);
        $row = $perciAmountStmt->fetch(PDO::FETCH_ASSOC);
        $perciAmountArr[] = array(
            'percidate' => $row['valuedate'] ?? '-',
            'perciamount' => $row['perciamount'] ?? 0
        );
        $finalArr = array_merge($finalArr, $perciAmountArr);

        $totalperciLength = $util->secondsToDayMinsHrs($percilength);
        $perciLengthArr[] = array(
            'total' => $totalperciLength,
            'totalvalue' => 0
        );

        $finalArr = array_merge($finalArr, $perciLengthArr);

        $perciFirst = $this->findFirstLastPrecipitationByMonth($sMonth, 'first');
        $finalArr = array_merge($finalArr, $perciFirst);
        $perciLast = $this->findFirstLastPrecipitationByMonth($sMonth, 'last');
        $finalArr = array_merge($finalArr, $perciLast);

        $percimax = $this->findMaxPrecipitationByMonth($sMonth);
        $finalArr = array_merge($finalArr, $percimax);

        return $finalArr;

//        echo "<pre>";
//        print_r($finalArr);
//        echo "<post>";
    }

    function getPerciMonthRecordData($tempArr, $event, $eventColVal, $sdate) {
        $uacnt = count($tempArr);
        $finalArr = Array();
        $totalTime = 0;
        $step = 0;
        $tempval = 0;
        $lasttmptime = 0;
        $skipflag = 0;
        $diff = 0;

        for ($t = 0; $t < $uacnt - 1; $t++) {
            $step = $step + 1;
            $tempvalue = $tempArr[$t];
            $tempval = $tempvalue[$event];
            $lasttmptime = $tempvalue['fulldate'];
            $tempvalueNext = $tempArr[$t + 1];
            $tempvalNext = $tempvalueNext[$event];
            $lasttmptimeNext = $tempvalueNext['fulldate'];

            //echo' valueFirst '.$tempvalue[$event].' valueNext '.$tempvalNext;

            if (($tempvalue[$event] == 0 && $tempvalNext == 0) || ($tempvalue[$event] == !0 && $tempvalNext == 0)) {
                continue;
            } else {

                if (($tempvalue[$event] != 0 && $tempvalNext != 0 ) ||
                        ($tempvalue[$event] == 0 && $tempvalNext != 0 )) {
                    $skipflag = 1;
                    $diff = abs(strtotime($lasttmptimeNext) - strtotime($lasttmptime));
                }
                $totalTime = $totalTime + $diff;
            }
        }

//        echo "<pre>";
//          print_r($totalTime);

        if ($step + 1 == $uacnt && $tempvalNext != 0) {
            //check if day is reached to next day >
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

        return $totalTime;
    }

    public function getPerciAmountByMonth($sMonth) {
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciamount from channel_0101_values channelv
                    where channelv.channelid ='0101'and  
                    DATE_FORMAT(channelv.datatime, '%m-%Y') = :smonth                    
                    group by  DATE_FORMAT(channelv.valuedate, '%m-%Y') order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":smonth", $sMonth);
        $stmt->execute();
        return $stmt;
    }

    public function find1MonthPerciLength($tdate) {
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

                    $totalTime = $totalTime + $diff;
                }
            }

            if ($step + 1 == $uacnt && $tempvalNext != 0) {
                //check if day is reached to next day >
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
//            $totalValue = number_format(($totalTime / (60 * 60)), 1);
//            $util = new UtilCommon;
//            $total = array(
//                'total' => $util->secondsToMinsHrs($totalTime),
//                'totalvalue' => $totalValue,
//                'date' => $tdate,
//            );
//            echo "<pre>";
//            print_r($totalValue);
//            echo "<post>";
            return $totalTime;
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
    }

    public function findFirstLastPrecipitationByMonth($sdate, $perciflag) {

        $perciname = 'firstperci';
        $queryorder = 'asc';
        if ($perciflag != 'first') {
            $perciname = 'lastperci';
            $queryorder = 'desc';
        }


        $sqlQuery = " select DATE_FORMAT(datatime, '%m-%Y') valuedate, 
                    DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i')  perci from channel_0098_values
                    where channelid ='0098' 
                    and DATE_FORMAT(datatime, '%m-%Y') = :datevalue
                    and value > 0
                    order by datatime $queryorder
                    limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":datevalue", $sdate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $perciAmountArr[] = array(
            'percidate' => $sdate,
            $perciname => $row['perci'] ?? '-'
        );

        return $perciAmountArr;
    }

    public function findMaxPrecipitationByMonth($sdate) {

        $sqlQuery10Mins = "select  DATE_FORMAT(datatime, '%m-%Y') valueDate, 
                            max(value) max10Minutesval  , 
                            DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i')  maxtime10Min
                            from 
                            channel_0103_values 
                            where DATE_FORMAT(datatime, '%m-%Y') = :datevalue ";
        $stmt10Mins = $this->conn->prepare($sqlQuery10Mins);
        $stmt10Mins->bindParam(":datevalue", $sdate);
        $stmt10Mins->execute();
        $row10Mins = $stmt10Mins->fetch(PDO::FETCH_ASSOC);
        $max10MinVal = $row10Mins['max10Minutesval'] ?? 0;
        $max10MinTime = $row10Mins['maxtime10Min'] ?? '-';

        $sqlQuery1Hr = "select  DATE_FORMAT(datatime, '%m-%Y') valueDate, max(value) max1Hrval,
                        DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i') maxtime1Hr
                        from 
                        channel_0102_values 
                        where DATE_FORMAT(datatime, '%m-%Y') = :datevalue ";
        $stmt1Hr = $this->conn->prepare($sqlQuery1Hr);
        $stmt1Hr->bindParam(":datevalue", $sdate);
        $stmt1Hr->execute();
        $row1Hr = $stmt1Hr->fetch(PDO::FETCH_ASSOC);
        $max1Hrval = $row1Hr['max1Hrval'] ?? 0;
        $maxtime1Hr = $row1Hr['maxtime1Hr'] ?? '-';

        $sqlQueryMaxIntensity = "select  DATE_FORMAT(datatime, '%m-%Y') valueDate, 
                                  max(value) maxintensity,
                                DATE_FORMAT(valuedate, '%m-%d-%Y %H:%i') maxtimeinten
                                from channel_0100_values 
                                where  DATE_FORMAT(datatime, '%m-%Y') = :datevalue ";
        $stmtMaxIntensity = $this->conn->prepare($sqlQueryMaxIntensity);
        $stmtMaxIntensity->bindParam(":datevalue", $sdate);
        $stmtMaxIntensity->execute();
        $rowMaxIntensity = $stmtMaxIntensity->fetch(PDO::FETCH_ASSOC);
        $maxintensity = $rowMaxIntensity['maxintensity'] ?? 0;
        $maxtimeinten = $rowMaxIntensity['maxtimeinten'] ?? '-';

        $perciMaxPerciArr[] = array(
            'percidate' => $sdate,
            'max10MinVal' => $max10MinVal,
            'max10MinTime' => $max10MinTime,
            'max1Hrval' => $max1Hrval,
            'maxtime1Hr' => $maxtime1Hr,
            'maxintensity' => $maxintensity,
            'maxtimeinten' => $maxtimeinten
        );

        return $perciMaxPerciArr;
    }

}
