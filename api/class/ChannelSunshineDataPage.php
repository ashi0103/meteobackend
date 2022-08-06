<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelSunshineDataPage {

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

    public function getAllSunshineByDate($sdate, $edate) {

        $sqlQuery = "select date(channelv.valuedate) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d %H:%i:%s')) fulldate,
                    CAST((channelv.value*60) AS DECIMAL(10,0) )    sunshineDuration,
                    IF(value >0 and value<=10,CAST((10*60-value*60) AS DECIMAL(10,0) ),0) nosunshine,
                    (DATE_FORMAT(valuedate, '%H:%i')) valuetime,
					(DATE_FORMAT(valuedate, '%Y')) valueyear,
                    (DATE_FORMAT(valuedate, '%c')) valuemonth,
                    (DATE_FORMAT(valuedate, '%e')) valueday,
                    (DATE_FORMAT(valuedate, '%k')) valuehr,
                    (DATE_FORMAT(valuedate, '%i')) valuemin
                    from channel_0124_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                    order by channelv.valuedate";

        $stmtPerciType = $this->conn->prepare($sqlQuery);
        $stmtPerciType->bindParam(":sdate", $sdate);
        $stmtPerciType->bindParam(":edate", $edate);
        $stmtPerciType->execute();
        $itemCount = $stmtPerciType->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmtPerciType->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'fulldate' => $row['fulldate'],
                    'sunshineDuration' => $row['sunshineDuration'],
                    'nosunshine' => $row['nosunshine']
                );
            }

            $eventArray = $this->getFinalSunshineRecordData($tempArr);

            usort($eventArray, function ($item1, $item2) {
                return $item1['fullstartdate'] <=> $item2['fullstartdate'];
            });

        

            return $eventArray;
        }
    }

    function getFinalSunshineRecordData($tempArr) {
        $uacnt = count($tempArr);
        $finalArr = array();

        for ($t = 0; $t < $uacnt ; $t++) {
            $tempvalue = $tempArr[$t];
            $tempvalueStart = $tempArr[$t];
            if ($tempvalue['sunshineDuration'] == 0 && $tempvalue['nosunshine'] == 0) {

                $fulledate = $tempvalueStart['fulldate'];
                $fulledatetime = DateTime::createFromFormat('Y-m-d H:i:s', $fulledate);
                $sunshineEnddateTime = $fulledatetime->modify("+599 seconds")->format('Y-m-d H:i:s');
                
                $finalArr[] = array(
                    'event' => "nosunshine",
                    'fullstartdate' => $tempvalueStart['fulldate'],
                    'fullenddate' => $sunshineEnddateTime,
                    'valuedate' => $tempvalueStart['valuedate'],
                    'startandend'=> $tempvalueStart['fulldate'].'#'.$sunshineEnddateTime
                );
            } else if ($tempvalue['sunshineDuration'] == 600) {
                
                $fulledate = $tempvalueStart['fulldate'];
                $fulldatetime = DateTime::createFromFormat('Y-m-d H:i:s', $fulledate);
                $sunshineEndTime = $fulldatetime->modify("+599 seconds")->format('Y-m-d H:i:s');

                $finalArr[] = array(
                    'event' => "sunshine",
                    'fullstartdate' => $tempvalueStart['fulldate'],
                    'fullenddate' => $sunshineEndTime,
                    'valuedate' => $tempvalueStart['valuedate'],
                    'startandend'=> $tempvalueStart['fulldate'].'#'.$sunshineEndTime
                );
            } else if ($tempvalue['nosunshine'] != 0) {

                $fulldate = $tempvalueStart['fulldate'];                
                $fulldatetime = DateTime::createFromFormat('Y-m-d H:i:s', $fulldate);
                $sunshine = $tempvalueStart['sunshineDuration'] - 1;                
                $sunshineStartTime = $fulldate;
                $sunshineEndTime = $fulldatetime->modify("+{$sunshine} seconds")->format('Y-m-d H:i:s');

//                echo('<pre> ');
//                echo $sunshine;
//                echo('<pre> ');
//                print_r($sunshineStartTime);
//                echo('<pre> ');
//                print_r($sunshineEndTime);


                $finalArr[] = array(
                    'event' => "sunshine",
                    'fullstartdate' => $sunshineStartTime,
                    'fullenddate' => $sunshineEndTime,
                    'valuedate' => $tempvalueStart['valuedate'],
                    'startandend'=> $tempvalueStart['fulldate'].'#'.$sunshineEndTime
                );


                $nosunshine = $tempvalueStart['nosunshine'] - 1;

                $fullEdatetime = DateTime::createFromFormat('Y-m-d H:i:s', $sunshineEndTime);
                $fullFinalEdate = $fullEdatetime->modify("+1 seconds")->format('Y-m-d H:i:s');
                $nosunshineStime = $fullFinalEdate;                
                $fullsdatetime = DateTime::createFromFormat('Y-m-d H:i:s', $fullFinalEdate);                
                $nosunshineEtime = $fullsdatetime->modify("+{$nosunshine} seconds")->format('Y-m-d H:i:s');

//                echo('<pre> ');
//                echo $nosunshine;
//                echo('<pre> ');
//                print_r($nosunshineStime);
//                echo('<pre> ');
//                print_r($nosunshineEtime);

                $finalArr[] = array(
                    'event' => "nosunshine",
                    'fullstartdate' => $nosunshineStime,
                    'fullenddate' => $nosunshineEtime,
                    'valuedate' => $tempvalueStart['valuedate'],
                    'startandend'=> $nosunshineStime.'#'.$nosunshineEtime
                );
            }
        }

        return $this->my_array_unique($finalArr);
    }
    
    
    function my_array_unique($array, $keep_key_assoc = false){
    $duplicate_keys = array();
    $tmp = array();       

    foreach ($array as $key => $val){
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val)){
            $val = (array)$val;
        }
        if (!in_array($val, $tmp)){
            $tmp[] = $val;
            } else{
            $duplicate_keys[] = $key;
        }
    }

    foreach ($duplicate_keys as $key){
        unset($array[$key]);
    }

    return $keep_key_assoc ? $array : array_values($array);
}

    public function getSunhineDurationPerDays($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((sum(channelv.value)) AS DECIMAL(10,2))  sunshineDuration 
                    from channel_0124_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                    group by  date( channelv.valuedate ) order by channelv.valuedate";

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
                $util = new UtilCommon;
                $sunFirstLastValues = $this->getlast1DayLastSunshine($row['paramdate']);
                $i = $i + 1;
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'] ?? '',
                    'sunshineDurationHrMin' => $util->MinsToHrsMin($row['sunshineDuration'] ?? 0),
                    'sunshineDuration' => number_format($row['sunshineDuration'] / 60, 2),
                    'firstSunshine' => $sunFirstLastValues['firstSunshine'],
                    'lastSunshine' => $sunFirstLastValues['lastSunshine']
                );
            }
        }
        return $tempArr;
    }

//0103
    public function getlast1DayLastSunshine($tdate) {

        $sqlQuery = "select firstSunshine,firstSunDuration,lastSunshine,lastSunDuration from 
                        (select (DATE_FORMAT(channelv.valuedate, '%H:%i')) firstSunshine,  CAST(((channelv.value)) AS DECIMAL(10,2))  firstSunDuration , date(channelv.datatime) datatime from channel_0124_values channelv  where
                        date(channelv.datatime) = :tdate   and channelv.value > 0  
                        order by  channelv.datatime  asc limit 1) t1 join (
                        select (DATE_FORMAT(channelv.valuedate, '%H:%i')) lastSunshine,  CAST(((channelv.value)) AS DECIMAL(10,2))  lastSunDuration, date(channelv.datatime) datatime  from channel_0124_values channelv  where
                        date(channelv.datatime) = :tdate   and channelv.value > 0  
                        order by  channelv.datatime  desc limit 1) t2
                        on t1.datatime = t2.datatime";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $tdate,
                    "firstSunshine" => $firstSunshine,
                    "firstSunDuration" => $firstSunDuration,
                    "lastSunshine" => $lastSunshine,
                    "lastSunDuration" => $lastSunDuration
                );
                $tempArr = $e;
            }
            return($tempArr);
        } else {
            $tempArr = array(
                "valuedate" => $tdate,
                "firstSunshine" => '-',
                "firstSunDuration" => '-',
                "lastSunshine" => '-',
                "lastSunDuration" => '-'
            );
            return($tempArr);
        }
    }

    /** Sunshine of different periods Start * */
    public function findSunshineThisWeek() {
        $sqlQuery = " select sum(sunshineTweek) sunshineTweek , CAST(sum(sunshineTweek)/count(da) as decimal(10,2)) sunshineAvgTweek,sunshineweek from 
                        (select (DATE_FORMAT(channelv.valuedate, '%v'))  sunshineweek , 
                    CAST((SUM(channelv.value*60)) AS DECIMAL(10,2))  sunshineTweek,                    
                    DATE_FORMAT(channelv.datatime, '%d-%m-%Y') da 
                    from channel_0124_values channelv
                    where channelv.channelid ='0124' and 
                    DATE_FORMAT((channelv.datatime),'%v') = (select max(DATE_FORMAT((datatime),'%v')) 
                    from channel_0124_values where  channelid ='0124') 
                    group by DATE_FORMAT(channelv.datatime, '%d-%m-%Y') ) t";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    /**
     * 
     * @param type $days (7 Days or 31 days)
     * @return type
     */
    public function findSunshineByNoOfDays($days) {
        $sqlQuery = "select CAST((sum(channelv.value*60)) AS DECIMAL(10,2)) sunshineLastXDays ,
                      CAST((sum(channelv.value*60)/$days) AS DECIMAL(10,2)) sunshineAvgLastXDays ,
                        date( channelv.valuedate ) da
                        from channel_0124_values channelv
                        where channelv.channelid ='0124' 
                        and  ( date(channelv.datatime) >= date_add((select max(datatime) 
                        from channel_0124_values where  channelid ='0124'), interval -:days DAY)) ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        return $stmt;
    }

    public function findSunshineThisMonth() {
        $sqlQuery = "select sum(sunshineTMonth) sunshineTMonth ,
			CAST(sum(sunshineTMonth)/count(da) as decimal(10,0)) sunshineAvgTMonth, dm
                        from
                        (select   CAST((sum(channelv.value*60)) AS DECIMAL(10,0)) sunshineTMonth ,
                        DATE_FORMAT(channelv.datatime, '%d-%m-%Y') da ,
                        DATE_FORMAT(channelv.datatime, '%m-%Y') dm
                        from channel_0124_values channelv
                        where channelv.channelid ='0124' 
                        and  ( DATE_FORMAT(channelv.datatime, '%m-%Y') = DATE_FORMAT((select max(datatime) 
                    from channel_0124_values where  channelid ='0124'),'%m-%Y' ))
                    group by DATE_FORMAT(channelv.datatime, '%d-%m-%Y') ) t ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function findSunshineThisYear() {
        $sqlQuery = "select sum(sunshineThisYear) sunshineThisYear , CAST(sum(sunshineThisYear)/count(da) as decimal(10,2)) sunshineAvgThisYear from                    
                    (select  CAST(sum(channelv.value*60) AS DECIMAL(10,2))  sunshineThisYear,                     
                     DATE_FORMAT(channelv.datatime, '%d-%m-%Y') da 
                     from channel_0124_values channelv
                     where channelv.channelid ='0124' and  
                      DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) 
                      from channel_0124_values where  channelid ='0124') 
                      group by DATE_FORMAT(channelv.datatime, '%d-%m-%Y') )t";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function findLastSunshine() {
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

    /** Sunshine of different periods End * */

    /** Sunshine of different buckets Start * */
    public function findSunshineBuckets($min, $max) {

        $op = '>';
        if ($min == 0 && $max == 0) {
            $op = '>=';
        }
        $sqlQuery = "select CAST(sum(value) AS DECIMAL(10,2)) as  value
                    , DATE_FORMAT(valuedate, '%Y-%m-%d') as valuedate
                        from channel_0124_values
                        where channelid ='0124' 
                        group by  DATE_FORMAT(valuedate, '%Y-%m-%d') 
                        having CAST(sum(value) AS DECIMAL(10,2)) $op :minvalue
                                        and CAST(sum(value) AS DECIMAL(10,2)) <= :maxvalue
                         order by DATE_FORMAT(valuedate, '%Y-%m-%d') desc ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":minvalue", $min);
        $stmt->bindParam(":maxvalue", $max);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $util = new UtilCommon;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate,
                    "value" => $util->MinsToHrsMin($value)
                );
                array_push($tempArr, $e);
            }

            return $tempArr;
        }
    }

    /** Sunshine of different buckets End * */

    /** Sunshine of Compare against Norm Month  Start */
    public function getSunshineCompAginstNorm($year) {

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
                    (select value*60 from norm_sunshine_values where monthvalue =  DATE_FORMAT(datatime, '%c')) norm ,
                     DATE_FORMAT(datatime, '%M') fullmonth,
                    DATE_FORMAT(datatime, '%m') months
                    from channel_0124_values
                    where channelid ='0124'
                    and  DATE_FORMAT(valuedate, '%Y') = :year  
                     group by DATE_FORMAT(valuedate, '%m-%Y') 
                     order by DATE_FORMAT(valuedate, '%m-%Y') asc )t 
                     order by valuedate  ";

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

    /** Sunshine of Compare against Norm Month  end */

    /** Sunshine of Compare against  Year  Start */
    public function getSunshineCompAginstNormYear($noOfYear) {
        $sqlQuery = "select                       
                    DATE_FORMAT(valuedate, '%Y') valuedate,
                    CAST(sum(value)/60 AS DECIMAL(10,2) ) as value ,
                   (select CAST(sum(value*60)/60 AS DECIMAL(10,2) ) as normvalue from norm_sunshine_values)
					as norm
                    from channel_0124_values
                    where channelid ='0124'                     
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
                    "diff" => number_format($value - $norm,2)
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    /** Sunshine of Compare against  Year  End */

    /** Sunshine balance Start */
    public function InsertSunshineBalanceByDay($inputDate, $inputDay) {
        // Set timezone
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        date_default_timezone_set('UTC');
        $end_date = $inputDate;
        $tablename;
        if ($inputDay == "30") {
            $tablename = "sunshinebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "sunshinebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "sunshinebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "sunshinebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "sunshinebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "sunshinebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "sunshinebalance365Days";
        }

        $sql = "TRUNCATE TABLE $tablename";
        $truncstmt = $this->conn->prepare($sql);
        $truncstmt->execute();

        $stmt = $this->conn->prepare("INSERT INTO $tablename(inputdate, sumvalue, avgnorm,percent)"
                . "                  VALUES (:inputdate, :sumvalue,:avgnorm, :percent)");

        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getSunshineBalance($date, $inputDay);
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
        echo " successful inserted " . $inputDay;
    }

    public function getSunshineBalance($inputDate, $inputDay) {
        $sqlQuery = "select sum(t2.value) sumvalue ,CAST( sum(t1.norm) AS DECIMAL(10,3) ) avgnorm ,  
             CAST( (sum(t2.value)/sum(t1.norm))*100 AS DECIMAL(10,2) ) percent ,
             t1.datatime
             from 
             (select  distinct DATE_FORMAT(datatime, '%d-%m-%Y') datatime ,
             (select CAST((value*60) AS DECIMAL(10,3) ) as normvalue
            from norm_sunshine_values where 
             monthvalue = DATE_FORMAT(datatime, '%c')
             )/day(last_day(datatime)) norm 
              from  channel_0124 
              where  date(datatime) > 
              date_add(:indate, interval -:inday day) and date(datatime) <= :indate
                ) t1 left join
               (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0124_values channelv
               where channelv.channelid ='0124' and 
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

    public function getSunshineBalanceValues($inputDate, $inputDay) {

        $tablename;
        if ($inputDay == "30") {
            $tablename = "sunshinebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "sunshinebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "sunshinebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "sunshinebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "sunshinebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "sunshinebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "sunshinebalance365Days";
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

    public function getSunshineBalanceMonthAgoValue($inputDate, $inputDay) {

        $tablename;
       if ($inputDay == "30") {
            $tablename = "sunshinebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "sunshinebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "sunshinebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "sunshinebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "sunshinebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "sunshinebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "sunshinebalance365Days";
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

    public function getSunshineBalanceByDay($inputDate, $inputDay) {
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
            $tempArr = $this->getSunshineBalanceValues($date, $inputDay);
            $mainArr[] = $tempArr;
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
//         echo "<pre>";
//         print_r($mainArr);
//         echo "<post>";
        return $mainArr;
    }

    /** Sunshine balance End */
    public function getSunshineCloudyPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_0124_values channelv
                        where channelv.channelid ='0124'  
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

    public function getSunshineSunnyPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_0124_values channelv
                        where channelv.channelid ='0124'   
                        and 
                        date(channelv.datatime) >= :infromdate and date(channelv.datatime) <= :intodate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') 
                        order by date(channelv.datatime) ";

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

    public function getSunshineDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getSunshineDailyOverviewByDate($date);
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
                $tempArr = $this->getSunshineDailyOverviewByDate($date);
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
                $tempArr = $this->getSunshineDailyOverviewByDate($date);
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
                $tempArr = $this->getSunshineDailyOverviewByDate($tempdate);
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

    public function getSunshineDailyOverviewByDate($sdate) {

        $finalArr = Array();
        $sunshineDurArr = $this->getSunhineDurationDaily($sdate, $sdate);
        if ($sunshineDurArr != null) {
            $finalArr = array_merge($finalArr, $sunshineDurArr);
            $gblRadAvgArr = $this->getAvgGblRadiationByDay($sdate);
            $finalArr = array_merge($finalArr, $gblRadAvgArr);
            $gblRadMaxArr = $this->getMaxGblRadiationByDay($sdate);
            $finalArr = array_merge($finalArr, $gblRadMaxArr);
//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";
        }
        return $finalArr;
        //       }
    }

    public function getSunhineDurationDaily($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((sum(channelv.value)) AS DECIMAL(10,2))  sunshineDuration 
                    from channel_0124_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                    group by  day( channelv.valuedate ) order by channelv.valuedate";

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
                $util = new UtilCommon;
                $sunFirstLastValues = $this->getlast1DayLastSunshine($row['paramdate']);
                $i = $i + 1;
                $tempArr = array(
                    'valuedate' => $row['valuedate'] ?? '',
                    'sunshineDurationHrMin' => $util->MinsToHrsMin($row['sunshineDuration'] ?? 0),
                    'sunshineDuration' => number_format($row['sunshineDuration'] / 60, 2),
                    'firstSunshine' => $sunFirstLastValues['firstSunshine'],
                    'lastSunshine' => $sunFirstLastValues['lastSunshine']
                );
            }
        }
        return $tempArr;
    }

    public function getAvgGblRadiationByDay($tDate) {

        $sqlQuery = "select CAST( AVG(value) AS DECIMAL(10,2)) as globalradTodayAvg 
                         from channel_0120_values 
                        where channelid ='0120' and  date(datatime) = :tdate ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array(
            'avgGblRadDate' => $tDate,
            'globalradTodayAvg' => $row['globalradTodayAvg'] ?? '-'
        );

        return $globalRadAvg;
    }

    public function getMaxGblRadiationByDay($tDate) {

        $sqlQuery = "select CAST( (value) AS DECIMAL(10,2)) as globalradMax,
                         DATE_FORMAT(valuedate, '%H:%i') globalradMaxtime
                         from channel_0120_values 
                        where channelid ='0120' and  date(datatime) = :tdate
                        order by value desc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadMax = array(
            'avgGblRadDate' => $tDate,
            'globalradMax' => $row['globalradMax'] ?? '-',
            'globalradMaxtime' => $row['globalradMaxtime'] ?? '-'
        );

        return $globalRadMax;
    }

    public function getSunshineOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getSunshineOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getSunshineOverviewByMonth($monthYear);
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
                $tempArr = $this->getSunshineOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getSunshineOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

        return $mainArr;
    }

    public function getSunshineOverviewByMonth($sdate) {

        $finalArr = Array();
        $sunshineDurArr = $this->getSunhineDurationPerMonth($sdate);
        $finalArr = array_merge($finalArr, $sunshineDurArr);
        $gblRadAvgArr = $this->getAvgGblRadiationByMonth($sdate);
        $finalArr = array_merge($finalArr, $gblRadAvgArr);
        $gblRadMaxArr = $this->getMaxGblRadiationByMonth($sdate);
        $finalArr = array_merge($finalArr, $gblRadMaxArr);

        return $finalArr;
    }

    public function getAvgGblRadiationByMonth($tDate) {

        $sqlQuery = "select CAST( avg(value) AS DECIMAL(10,2)) as globalradTodayAvg 
                         from channel_0120_values 
                        where channelid ='0120' and  DATE_FORMAT(valuedate, '%m-%Y') =:tdate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array();
        if ($row != null && $row['globalradTodayAvg'] != null) {
            $globalRadAvg = array(
                'avgGblRadDate' => $tDate,
                'globalradTodayAvg' => $row['globalradTodayAvg'] ?? '-'
            );
        }
        return $globalRadAvg;
    }

    public function getMaxGblRadiationByMonth($tDate) {

        $sqlQuery = "   select CAST( max(value) AS DECIMAL(10,2)) as globalradMax ,
                        date(datatime) globalradMaxtime
                         from channel_0120_values 
                        where channelid ='0120' and  DATE_FORMAT(valuedate, '%m-%Y') = :tdate
                        order by value";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadMax = array();
        if ($row != null && $row['globalradMax'] != null) {
            $globalRadMax = array(
                'avgGblRadDate' => $tDate,
                'globalradMax' => $row['globalradMax'] ?? '-',
                'globalradMaxtime' => $row['globalradMaxtime'] ?? '-'
            );
        }
        return $globalRadMax;
    }

    public function getSunhineDurationPerMonth($sDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%m-%Y')) paramdate,
                    CAST((sum(channelv.value)) AS DECIMAL(10,2))  sunshineDuration 
                    from channel_0124_values channelv		
                    where  DATE_FORMAT(channelv.valuedate, '%m-%Y') =:sdate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row != null && $row['paramdate'] != null) {
                $util = new UtilCommon;
                $sunFirstLastValues = $this->get1MonthFirstLastSunshine($row['paramdate']);
                $i = $i + 1;
                $tempArr = array(
                    'valuedate' => $row['valuedate'],
                    'sunshineDurationHrMin' => $util->secondsToDayMinsHrs($row['sunshineDuration'] * 60),
                    'sunshineDuration' => number_format($row['sunshineDuration'] / 60, 2),
                    'firstSunshine' => $sunFirstLastValues['firstSunshine'],
                    'lastSunshine' => $sunFirstLastValues['lastSunshine']
                );
            }
        }
        return $tempArr;
    }

    public function get1MonthFirstLastSunshine($tdate) {

        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d %H:%i')) firstSunshine,  
                        CAST(((channelv.value)) AS DECIMAL(10,2))  firstSunDuration , 
                        date(channelv.datatime) datatime 
                        from channel_0124_values channelv  where
                        (DATE_FORMAT(channelv.datatime, '%m-%Y')) = :tdate and value > 0   
                        order by  channelv.datatime  asc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();

        $sqlQuerylastSunshine = " select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d %H:%i')) lastSunshine,  
                        CAST(((channelv.value)) AS DECIMAL(10,2))  lastSunDuration , 
                        date(channelv.datatime) datatime 
                        from channel_0124_values channelv  where
                        (DATE_FORMAT(channelv.datatime, '%m-%Y')) = :tdate and value > 0   
                        order by  channelv.datatime  desc limit 1";

        $stmtLastSun = $this->conn->prepare($sqlQuerylastSunshine);
        $stmtLastSun->bindParam(":tdate", $tdate);
        $stmtLastSun->execute();


        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rowLastSun = $stmtLastSun->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "valuedate" => $tdate,
                "firstSunshine" => $row['firstSunshine'],
                "firstSunDuration" => $row['firstSunDuration'],
                "lastSunshine" => $rowLastSun['lastSunshine'],
                "lastSunDuration" => $rowLastSun['lastSunDuration'],
            );
            return($tempArr);
        } else {
            $tempArr = array(
                "valuedate" => $tdate,
                "firstSunshine" => '',
                "firstSunDuration" => '',
                "lastSunshine" => ' ',
                "lastSunDuration" => ' '
            );
            return($tempArr);
        }
    }

// ************************ *******************************************************
}
