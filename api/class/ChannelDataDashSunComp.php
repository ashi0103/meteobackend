<?php

/**
 * Description of ChannelDataDashComp
 *
 * @author USER
 */
class ChannelDataDashSunComp {

    // Connection
    private $conn;
    
    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function findSunshine10Mins($tdate) {
        $sqlQuery = "select  CAST(value AS DECIMAL(10,2)) as sunduration10mins , DATE_FORMAT(datatime, '%H:%i') as  datepersetime , date(datatime) dateperse from channel_0124_values
			where channelid ='0124' and  date(datatime) =:tdate order by valuedate desc limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findSunshine1Hr($tdate) {
        $sqlQuery = "select CAST(sum(value) AS DECIMAL(10,2)) as  sundurationlast1hr 				
                    from channel_0124_values
                       where channelid ='0124' and  date(datatime) = :tdate and datatime > 
                       DATE_SUB((select max(datatime) from channel_0124_values 
                       where  channelid ='0124' and  date(datatime) =:tdate), INTERVAL 1 Hour)
                       order by datatime asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findSunshineToday($tdate) {
        $sqlQuery = "select CAST( sum(value) AS DECIMAL(10,0))*60 as sundurationtoday  from channel_0124_values
			where channelid ='0124' and  date(datatime) = :tdate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findGlobalRadiation10Min($tdate) {
        $sqlQuery = "select CAST( sum(value) AS DECIMAL(10,2)) as globalradiation10mins from channel_0120_values
			where channelid ='0120' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0120_values where  channelid ='0120' and  date(datatime) =:tdate), INTERVAL 10 MINUTE)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findDiffuseRadiation10Min($tdate) {
        $sqlQuery = "select CAST( sum(value) AS DECIMAL(10,2)) as diffuseradiation ,valuedate from channel_0122_values
			where channelid ='0122' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_0122_values where  channelid ='0122' and  date(datatime) = :tdate), INTERVAL 10 MINUTE)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findGlobalRadiation1Hr($tdate) {
        $sqlQuery = "select CAST( avg(value) AS DECIMAL(10,2)) as globalradiationlasthr ,valuedate from channel_0120_values 
			where channelid ='0120' and  date(datatime) = :tdate and  datatime >=
			DATE_SUB((select max(datatime) from channel_0120_values where  channelid ='0120' and  date(datatime) = :tdate), INTERVAL 1 HOUR )";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findGlobalRadiationToday($tdate) {
        $sqlQuery = "select CAST( (value) AS DECIMAL(10,2)) as globalradTodaymax, 
                        DATE_FORMAT(valuedate, '%H:%i') globalradTodaymaxtime  from channel_0120_values 
                        where channelid ='0120' and  date(datatime) = :tdate 
                        order by value desc limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

}
