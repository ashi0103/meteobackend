<?php

include_once('UtilCommon.php');

/**
 * Description of ChannelDataDashComp
 *
 * @author USER
 */
class ChannelDataDashComp {

    // Connection
    private $conn;
    // Columns
    public $dataPer;
    public $dataPerTime;
    public $currentTemp;
    public $avgTemplast24hr;
    public $tempDailyMax;
    public $tempDailyMaxTime;
    public $tempDailyMin;
    public $tempDailyMinTime;
    public $apparentTemp;
    public $avgTempThisMonth;
    public $avgTempThisMonthNorm;
    public $diffToNorm;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCurrentTemperaturePerTDate($param2, $tdate) {
        $sqlQuery = " select 
                        DATE_FORMAT(datatime, '%m-%d-%Y') as  calcdate, 
                                     DATE_FORMAT(datatime, '%H:%i') as calctime,
                        CAST((value) AS DECIMAL(10,1))  curtemp 
                        from channel_0130_values where channelid ='0130'
                        and date(datatime)= :tdate
                        order by datatime desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getAvgTemperaturePerTDate($param2, $tdate) {
        $sqlQuery = "select 
                    CAST((avg(value)) AS DECIMAL(10,1))  avgtemp 
                    from channel_0130_values where channelid ='0130'
                    and date(datatime)= :tdate 
                    group by  datatime";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getMinTemperaturePerTDate($param2, $tdate) {
        $sqlQuery = "SELECT
                        DATE_FORMAT(datatime , '%H:%i') mintemptime ,
                        CAST((value) AS DECIMAL(10,1)) AS mintemp
                        FROM channel_0130_values  where channelid ='0130'
                                    and date(datatime)= :tdate
                        order by value asc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getMaxTemperaturePerTDate($param2, $tdate) {
        $sqlQuery = "SELECT
                        DATE_FORMAT(datatime , '%H:%i') maxtemptime , 
                        CAST((value) AS DECIMAL(10,1)) AS maxtemp
                    FROM
                        channel_0130_values  where channelid ='0130'
                                    and date(datatime)= :tdate
                        order by value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getAppTemperaturePerTDate($tdate) {
        $sqlQuery = "select  CAST((value) AS DECIMAL(10,1)) apptemp   from channel_0133_values as channelv
			Inner Join(
			select max(datatime) as maxdate, channelid, max(value) max_temp,min(value) min_temp, avg(value) avg_temp from channel_0133_values
			where channelid ='0133' group by date(datatime))t 
			ON t.maxdate = channelv.datatime and channelv.channelid = t.channelid
			and channelv.channelid ='0133' and date(channelv.datatime)=:tdate order by date(datatime) desc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getLast24HourTemperature() {
        $sqlQuery = "select CAST(AVG(value) AS DECIMAL(10,1)) as avg24hrtemp, DATE_SUB(datatime, INTERVAL 24 HOUR) 24hr,
			datatime  from channel_0130_values where channelid ='0130' and  datatime >= 
			DATE_SUB((select max(datatime) from channel_0130_values where  channelid ='0130'), INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlyAvgTemperature() {
        $sqlQuery = "SELECT   CAST(AVG(value) AS DECIMAL(10,2)) avgmonthlytemp, 
                            month(datatime) monthavgtemp, year(datatime) yearavgtemp
                    FROM  channel_0130_values where channelid ='0130' 
                         and  DATE_FORMAT((datatime),'%m-%Y') = (select (DATE_FORMAT(max(datatime),'%m-%Y')) from 			   channel_0130_values where  channelid ='0130') 
                           group by  DATE_FORMAT((datatime),'%m-%Y') ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlyNormTemperature($monthValue) {
        $sqlQuery = "select value from norm_temp_values where monthvalue = :monthvalue ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":monthvalue", $monthValue);
        $stmt->execute();
        return $stmt;
    }

    // Percipitation


    public function getPerciLast10Mins($tdate) {
        $sqlQuery = "select IFNULL(CAST(sum(value) AS DECIMAL(10,3)),0) as perci10mins , IFNULL(DATE_FORMAT(DATE_SUB(datatime, INTERVAL 10 MINUTE), '%H:%i'),'' ) as last10mins,
                        IFNULL(DATE_FORMAT(datatime, '%H:%i'),'---' ) as  datepersetime , IFNULL(date(datatime), DATE_FORMAT(SYSDATE(), '%m-%d-%Y')) dateperse
                     from channel_0103_values 
			where channelid ='0103' and  date(datatime) = :tdate and
			datatime >= 
			DATE_SUB((select max(datatime) from channel_0103 where  channelid ='0103' and  date(datatime) = :tdate), INTERVAL 10 MINUTE)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getPerciLast1Hour($tdate) {
        $sqlQuery = "select IFNULL(CAST(sum(value) AS DECIMAL(10,3)),0) as perci1Hr , DATE_FORMAT(DATE_SUB(datatime, INTERVAL 1 HOUR), '%H:%i') as last1Hr, DATE_FORMAT(datatime, '%H:%i') as  curr_time  from channel_0103_values
			where channelid ='0103' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0103 where  channelid ='0103' and  date(datatime) = :tdate), INTERVAL 1 HOUR) ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findPerciToday($tdate) {
        $sqlQuery = "select  IFNULL(CAST(sum(value) AS DECIMAL(10,3)),0) perciToday   from channel_0101_values
			where channelid ='0101' and  date(datatime) = :tdate and value > 0";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findPerciLenghtToday($tdate) {
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

            $util = new UtilCommon;
            $total = $util->secondsToMinsHrs($totalTime);
            return $total;
        } else {
            return('NA');
        }
    }

    public function findPerciIntensityToday($tdate) {
        $sqlQuery = "select  IFNULL(CAST(value AS DECIMAL(10,2)),0)  perciIntensityMax , DATE_FORMAT(datatime, '%H:%i') perciIntensityMaxTime from channel_0100_values
                    where channelid ='0100' and  date(datatime) = :tdate 
                    order by value desc limit 1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findLastPrecipitation() {
        $sqlQuery = " select  IFNULL(CAST((t.value) AS DECIMAL(10,3)),0) perci ,
                    DATE_FORMAT(t.datatime, '%m-%d-%Y %H:%i') lastpercitime,
                    DATE_FORMAT(k.datatime, '%m-%d-%Y %H:%i') valuedate ,
                    DATE_FORMAT(k.datatime, '%H:%i') datepersetime
                    , TIMESTAMPDIFF(SECOND,t.valuedate,k.datatime ) diff 
                      from channel_0101_values t left join 
                      (
                            select channelid, max(datatime) as datatime  from channel_0101
                      ) k
                            on t.channelid = k.channelid where t.channelid ='0101'  and t.value > 0                      
                                order by t.valuedate  desc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }

    public function findPerciThisMonth($tdate) {
        $sqlQuery = "SELECT    IFNULL(CAST((sum(value)) AS DECIMAL(10,3)),0) perciMonthly, 
			          month(datatime) months, 
			          year(datatime) years
			FROM      channel_0101_values where channelid ='0101'
			GROUP BY  channelid,
			          month(datatime),
			          year(datatime) having 
			           (months = MONTH(:tdate)
					  AND years = YEAR(:tdate)) ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlyNormPercipitation($monthValue) {
        $sqlQuery = "select value from norm_perci_values where monthvalue = :monthvalue ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":monthvalue", $monthValue);
        $stmt->execute();
        return $stmt;
    }

}
