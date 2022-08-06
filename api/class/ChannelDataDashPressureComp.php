<?php

/**
 * Description of ChannelDataDashComp
 *
 * @author USER
 */
class ChannelDataDashPressureComp {

    // Connection
    private $conn;
    
    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function findPressure10Mins($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as pressurelast10Mins, DATE_SUB(datatime, INTERVAL 10 Minute) pressure10Mins, DATE_FORMAT(datatime, '%H:%i') datepersetime, date(datatime) dateperse  from channel_0150_values
			where channelid ='0150' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0150_values where  channelid ='0150' and  date(datatime) = :tdate), INTERVAL 10 Minute)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findPressureChange3Hrs($tdate) {
        $sqlQuery = "select  CAST((pressure_10Mins - pressure_3hour) AS DECIMAL(10,1)) pressureChangelst3hr from
			(select CAST((value) AS DECIMAL(10,2)) as pressure_10Mins, valuedate,channelid  from channel_0150_values
			where channelid ='0150' and  date(datatime) =:tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0150_values where  channelid ='0150' 
			and  date(datatime) =:tdate ), INTERVAL 10 Minute)) t1 INNER JOIN
			(select CAST((value) AS DECIMAL(10,2)) as pressure_3hour ,  valuedate,channelid  from channel_0150_values
			where channelid ='0150' and  date(datatime) =:tdate  and valuedate >
			DATE_SUB((select max(valuedate) from channel_0150_values where  channelid ='0150' and  date(datatime) = :tdate ), INTERVAL 3 Hour)
			order by valuedate asc limit 1) t2 ON t1.channelid = t2.channelid";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findPressureChange12Hrs($tdate) {
        $sqlQuery = "select  CAST((pressure_10Mins - pressure_12hour) AS DECIMAL(10,1)) pressureChangelst12hr from
			(select CAST((value) AS DECIMAL(10,1)) as pressure_10Mins, valuedate,channelid  from channel_0150_values
			where channelid ='0150' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0150_values where  channelid ='0150' 
			and  date(datatime) = :tdate), INTERVAL 10 Minute)) t1 INNER JOIN
			(select CAST((value) AS DECIMAL(10,2)) as pressure_12hour ,  valuedate,channelid  from channel_0150_values
			where channelid ='0150' and  date(datatime) = :tdate and valuedate >
			DATE_SUB((select max(valuedate) from channel_0150_values where  channelid ='0150' and  date(datatime) = :tdate), INTERVAL 12 Hour)
			order by valuedate asc limit 1) t2 ON t1.channelid = t2.channelid";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findHumidity10Mins($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as humidity10Mins, DATE_SUB(datatime, INTERVAL 10 Minute) humidity10Minstime, DATE_FORMAT(valuedate, '%H:%i')  from channel_0140_values
			where channelid ='0140' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0140_values where  channelid ='0140' and  date(datatime) = :tdate), INTERVAL 10 Minute)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findHumidityDailyMin($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as humiditydailymin, DATE_FORMAT(valuedate, '%H:%i') humiditydailyminTime  from channel_0140_values
			where channelid ='0140' and  date(datatime) = :tdate order by value asc Limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findHumidityDailyMax($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as humiditydailyMax, DATE_FORMAT(valuedate, '%H:%i') humiditydailyMaxtime from channel_0140_values
			where channelid ='0140' and  date(datatime) = :tdate order by value desc Limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findDewpoint10Mins($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as dewpoint10Mins  from channel_1132_values
			where channelid ='1132' and  date(datatime) = :tdate order by valuedate desc Limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findWetBulbTemp($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,1)) as wetBulb10Mins  from channel_0137_values
			where channelid ='0137' and  date(datatime) = :tdate order by valuedate desc Limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }
    
}
