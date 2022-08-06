<?php

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelGblRadData {

    // Connection
    private $conn;
  
    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }
//'0120'
    public function getlast7DaysAvgGblRad($channelId) {
         $tablechannel= 'channel_'.$channelId.'_values';
         
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((avg(channelv.value)) AS DECIMAL(10,2))  avggblradiation from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannel where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getlast7DaysMaxGblRad($channelId) {
        $tablechannel= 'channel_'.$channelId.'_values';
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((max(channelv.value)) AS DECIMAL(10,2))  maxgblradiation from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannel where  channelid =:channelid), interval -7 DAY))
                     group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);        
        $stmt->execute();
        return $stmt;
    }
    

}
