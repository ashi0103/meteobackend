<?php

//include_once('UtilCommon.php');
/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelPerciData {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getlast7DaysperciAmount($channelId) {

        $tablechannel = 'channel_' . $channelId . '_values';
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciamount from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannelmain where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getlast7DaysperciAmountMax($channelId) {

        $tablechannel = 'channel_' . $channelId . '_values';
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = " select max(perciamount)maxperciamount from (select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  perciamount from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannelmain where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate)t";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function find7DaysPerciLength() {
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d' )) valuedate from channel_0098_values channelv
                    where channelv.channelid ='0098' and  ( date(channelv.datatime) >= date_add((select max(datatime) from channel_0098 where  channelid ='0098'), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate asc";

        $stmt = $this->conn->prepare($sqlQuery);
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

//    public function find1DaysPerciLength($tdate) {
//        $sqlQuery = "select  distinct valuedate,value from channel_0098_values
//			where channelid ='0098' and  date(datatime) =:tdate  order by valuedate asc";
//        
//        $stmt = $this->conn->prepare($sqlQuery);
//        $stmt->bindParam(":tdate", $tdate);
//        $stmt->execute();
//
//        $itemCount = $stmt->rowCount();
//        if ($itemCount > 0) {
//            $tempArr = array();
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                extract($row);
//                $e = array(
//                    "valuedate" => $valuedate,
//                    "value" => $value
//                );
//                array_push($tempArr, $e);
//            }
//            $uacnt = count($tempArr);
//            $totalTime = 0;
//            $time=0;
//            for ($t = 0; $t < $uacnt; $t++) {
//                $tempvalue = $tempArr[$t];
//                if ($tempvalue['value'] == 0) {
//                    $time = 0;
//                    continue;
//                } else {
//                    if ($time == 0) {
//                        $time = $tempvalue['valuedate'];
//                    }
//                    $diff = abs(strtotime($time) - strtotime($tempvalue['valuedate']));
//                    $totalTime = $totalTime + $diff;
//                    $time = $tempvalue['valuedate'];
//                }
//            }
//            //Converting to minutes
//           // $totalTime = floor($totalTime / (60));
//            //$total = gmdate("H:i:s", $totalTime);
//            $totalValue =  number_format(($totalTime / (60*60)),1);
//             $util = new UtilCommon;     
//             $total = array(
//                 'total' =>  $util->secondsToMinsHrs($totalTime),
//                 'totalvalue' =>  $totalValue,
//                 'date' =>  $tdate,
//                 
//             );            
//                          
//           // echo $total;
////            echo "<pre>";
////            print_r($totalTime);
////            echo "<post>";
//            return $total;
//        } else {
//            http_response_code(404);
//            echo json_encode(
//                    array("message" => "No record found.")
//            );
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

    public function getlast7DaysperciIntensity($channelId) {
        $sqlQuery = "select  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((max(channelv.value)) AS DECIMAL(10,2))  perciIntensityMax from channel_0100_values channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from channel_0100 where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getlast7DaysperciIntensityMax($channelId) {
        $sqlQuery = "select max(perciIntensityMax) maxperciintensity from(
                        select  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((max(channelv.value)) AS DECIMAL(10,2))  perciIntensityMax from channel_0100_values channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from channel_0100 where  channelid =:channelid), interval -7 DAY))
                    group by  DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') order by channelv.valuedate)t";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

//0103
    public function getlast7Daysperci10min($channelId) {

        $tablechannel = 'channel_' . $channelId . '_values';
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(((channelv.value)) AS DECIMAL(10,2))  perci10mins from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannelmain  where channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    //0102
    public function getlast7Daysperci1Hrs($channelId) {

        $tablechannel = 'channel_' . $channelId . '_values';
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(((channelv.value)) AS DECIMAL(10,2))  perci1Hrs from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannelmain where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    //0098
    public function getlast7DaysperciType($channelId) {

        $tablechannel = 'channel_' . $channelId . '_values';
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(((channelv.value)) AS DECIMAL(10,2))  perciType from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) > date_add((select max(datatime) from $tablechannelmain where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ),value order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    //*********************************************
    //        Perciptation cumulative queries 
    //*********************************************
    // 
    // 0101 : Today and Yesterday Values
    public function getCumulativePerciTodayAllData($channelId, $tdate) {

        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i') valuedate,  
                    channelv.value datavalue ,CAST(SUM(channelv.value) OVER(ORDER BY DATE_FORMAT(channelv.valuedate, '%H:%i'))  AS DECIMAL(10,3) )  value from channel_0101_values channelv
                    where channelid ='0101' 
                    and channelv.value >0
                    and date(channelv.datatime)=:tdate order by valuedate ;
                        ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getPerciJasonData($stmt) {
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate,
                    "datavalue" => $datavalue,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    // 24 hr and custom hours all values
    public function getCumulativePerciLast24HrAllData($channelId, $hrValue) {

        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i')  valuedate , 
            channelv.value datavalue, 
            CAST(SUM(channelv.value) OVER(ORDER BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i'))  AS DECIMAL(10,3) ) value 
            from channel_0101_values channelv
                     where channelv.channelid ='0101' 
                     and channelv.value >0 and  
                     (channelv.valuedate >= date_add((select max(datatime) as 
                     maxdate from channel_0101_values), interval -:hrValue HOUR))
                     order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":hrValue", $hrValue);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciAllDataCustom($fromDate, $toDate) {

        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i')  valuedate , 
            channelv.value datavalue, 
            CAST(SUM(channelv.value) OVER(ORDER BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i'))  AS DECIMAL(10,3) ) value 
            from channel_0101_values channelv
                     where channelv.channelid ='0101' 
                     and channelv.value >0 and  
                     (channelv.valuedate between :fromDate and :toDate)
                     order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    // start
    public function getCumulativePerciTodayAvgHrData($channelId, $tdate) {

//        /**
//         * This is special case of sum of percipitation 
//         * 0101
//         */
//        $aggregate = 'avg';
//        if($channelId=='0101'){
//          $aggregate = 'sum'; 
//        }

        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,2) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedate ,
                         CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                         where channelv.channelid ='0101' and date(channelv.datatime)=:tdate  
                         and channelv.value >0
                         group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') order by valuedate) t
                         group by  valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciLast24AvgHrData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                          where channelv.channelid ='0101'  
                          and  (channelv.datatime > date_add((select max(datatime) from channel_0101_values 					 where  channelid ='0101'), interval -24 HOUR))
                          and channelv.value >0
                          group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') order by valuedate) t
                          group by  valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciAvgHrCustom($fromDate, $toDate) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                          where channelv.channelid ='0101'  
                          and channelv.datatime between :fromDate and :toDate
                          and channelv.value >0
                          group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') order by valuedate) t
                          group by  valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciLast1WeekAvgHrData($channelId, $days) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                          where channelv.channelid ='0101'  
                          and  (channelv.datatime > date_add((select max(datatime) from channel_0101_values 
                          where  channelid ='0101'), interval -:days Day))
                          and channelv.value >0
                          group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') order by valuedate) t
                          group by  valuedate  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciThisMonthAvgHrData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
        $aggregate = 'avg';
        if ($channelId == '0101') {
            $aggregate = 'sum';
        }

        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                     where channelv.channelid ='0101'  					
                    and  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from channel_0101 where  channelid ='0101')
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') order by valuedate) t
                    group by  valuedate ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerci1WeekAvgDayData($channelId, $days) {

//        /**
//         * This is special case of sum of percipitation 
//         * 0101
//         */
//        $aggregate = 'avg';
//        if($channelId=='0101'){
//          $aggregate = 'sum'; 
//        }

        $sqlQuery = "select t.valuedate ,
                    t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                    (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and  (channelv.datatime > date_add((select max(datatime) from channel_0101_values 
                    where  channelid ='0101'), interval -:days Day))
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') order by valuedate) t
                    group by  valuedate  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciLast1MonthAvgDayData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
//        $aggregate = 'avg';
//        if($channelId=='0101'){
//          $aggregate = 'sum'; 
//        }

        $sqlQuery = "select t.valuedate ,
                        t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                        (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedate ,
                        CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                          where channelv.channelid ='0101'  
                          and  (channelv.datatime > date_add((select max(datatime) from channel_0101_values 
                          where  channelid ='0101'), interval -1 MONTH))
                          and channelv.value >0
                          group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') order by valuedate) t
                          group by  valuedate   ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciThisMonthAvgDayData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
//        $aggregate = 'avg';
//        if($channelId=='0101'){
//          $aggregate = 'sum'; 
//        }

        $sqlQuery = "select t.valuedate ,
                    t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                    (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101' 
                    and   DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from channel_0101_values where  channelid ='0101') 
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') order by valuedate) t
                    group by  valuedate ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciThisYearAvgDayData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
//        $aggregate = 'avg';
//        if($channelId=='0101'){
//          $aggregate = 'sum'; 
//        }

        $sqlQuery = "select t.valuedate ,
                     t.value datavalue, CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                    (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedate ,
                     CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and   DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0101_values where  channelid ='0101') 
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') order by valuedate) t
                    group by  valuedate  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciAvgCustomDayData($fromDate, $toDate) {

        $sqlQuery = "select t.valuedate ,t.value datavalue, 
                    CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                    (select DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and  date(channelv.datatime) between :fromDate and :toDate                    
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') order by valuedate) t
                    group by  valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciThisYearAvgMonthData($channelId) {

        /**
         * This is special case of sum of percipitation 
         * 0101
         */
        $aggregate = 'avg';
        if ($channelId == '0101') {
            $aggregate = 'sum';
        }

        $sqlQuery = "select t.valuedate , t.value datavalue,
                        CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                       (select DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0101_values where  channelid ='0101') 
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%Y') order by valuedate) t
                    group by  valuedate ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

    public function getCumulativePerciYearCustomAvgMonthData($fromDate, $toDate) {

        $sqlQuery = "select t.valuedate , t.value datavalue,
                    CAST(SUM(t.value) OVER(ORDER BY t.valuedate) AS DECIMAL(10,3) ) value from
                   (select DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedate ,
                    CAST(SUM(channelv.value) AS DECIMAL(10,3) ) value from channel_0101_values channelv
                    where channelv.channelid ='0101'  
                    and  date(channelv.datatime) between :fromDate and :toDate
                    and channelv.value >0
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%Y') order by valuedate) t
                    group by  valuedate  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        $this->getPerciJasonData($stmt);
    }

}
