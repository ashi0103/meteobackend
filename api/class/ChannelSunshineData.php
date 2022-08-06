<?php

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelSunshineData {

    // Connection
    private $conn;
  
    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    //0124
    public function getlast7DaySunhineDuration($channelId) {
        
         $tablechannel= 'channel_'.$channelId.'_values';
         
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST((sum(channelv.value)) AS DECIMAL(10,2))  sunshineDuration from $tablechannel channelv
                    where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tablechannel where  channelid =:channelid), interval -7 DAY))
                    group by  day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    
     public function getlast7DaysFirstLastSunshine() {        
        
        $sqlQuery = "select  (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) valuedate from channel_0124 channelv
                    where channelv.channelid ='0124' and  ( date(channelv.datatime) >= date_add((select max(datatime) from channel_0124  where  channelid ='0124'), interval -7 DAY))
                    group by  day( channelv.datatime ) order by channelv.datatime";
        
        $stmt = $this->conn->prepare($sqlQuery);  
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {            
            $tempArr = array();
            $i=0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate ,
                    "last7DayData" => $this->getlast1DayLastSunshine($valuedate)
                );                   
                $tempArr[$i] = $e['last7DayData'];
                $i = $i+1;
                
            } 
//                echo "<pre>";           
//                print_r($tempArr[0]);
//                echo "<post>";
                
                return $tempArr;
        }
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
                    "firstSunshine" => '',                   
                    "firstSunDuration" => '',
                    "lastSunshine" => ' ',
                    "lastSunDuration" => ' '
                );
            return($tempArr);
        }
    }
    
     
    

}
