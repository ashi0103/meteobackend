<?php

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelData {

    // Connection
    private $conn;
    // Columns
    public $days;
    public $curtemp;
    public $mintemp;
    public $maxtemp;
    public $avgtemp;
    public $valuedate;
    public $valuetime;
    public $value;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getlast7daysData($channelId) {
        
        $tableChannel =  'channel_'.$channelId.'_values';        
        $sqlQuery = "select date(channelv.datatime) days, CAST((value) AS DECIMAL(10,2)) curtemp, CAST((min_temp) AS DECIMAL(10,1)) mintemp, CAST((max_temp) AS DECIMAL(10,1)) maxtemp, CAST((avg_temp) AS DECIMAL(10,2)) avgtemp from $tableChannel as channelv
			Inner Join(
			select max(datatime) as maxdate, channelid, max(value) max_temp,min(value) min_temp, avg(value) avg_temp, datatime from $tableChannel
			where channelid =:channelid group by date(datatime)) t
			ON t.maxdate = channelv.datatime and channelv.channelid = t.channelid
			where t.datatime > date_add( (select date(max(datatime)) from $tableChannel where  channelid =:channelid)
            , interval -7 day) 
            and channelv.channelid =:channelid order by date(datatime) asc";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }
    
    public function getlast7daysAllMinMaxData($channelId) {        
        $tableChannel =  'channel_'.$channelId.'_values';  
        
        $sqlQuery = "select max(max_temp) allmax, min(min_temp) allmin, max(avgtemp)allmaxavg from (
                        select date(datatime) as maxdate, CAST(max(value) AS DECIMAL(10,1)) max_temp, 
                                  CAST(min(value) AS DECIMAL(10,1)) min_temp,
                                  CAST(avg(value) AS DECIMAL(10,2)) avgtemp
                                    from $tableChannel where channelid =:channelid 
                                    and   datatime > date_add( (select date(max(datatime)) from $tableChannel
                                                        where  channelid =:channelid ) , interval -6 day) 			           
                                    group by DATE_FORMAT(valuedate, '%m-%d-%Y')) t ";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }
    
     public function getlast7daysAllWindMinMaxData($channelId) {        
        $tableChannel =  'channel_'.$channelId.'_values';  
        
        $sqlQuery = "select max(max_temp) allmax, min(min_temp) allmin, max(avgtemp)allmaxavg from (
                        select date(datatime) as maxdate, CAST(max(value*3.6) AS DECIMAL(10,2)) max_temp, 
                                  CAST(min(value*3.6) AS DECIMAL(10,2)) min_temp,
                                  CAST(avg(value*3.6) AS DECIMAL(10,1)) avgtemp
                                    from $tableChannel where channelid =:channelid 
                                    and   datatime > date_add( (select date(max(datatime)) from $tableChannel
                                                        where  channelid =:channelid ) , interval -6 day) 			           
                                    group by DATE_FORMAT(valuedate, '%m-%d-%Y')) t ";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }
    
     public function getlast7daysDataWind($channelId) {
        
        $tableChannel =  'channel_'.$channelId.'_values';        
        $sqlQuery = "select date(channelv.datatime) days, CAST((value*3.6) AS DECIMAL(10,2)) curtemp, CAST((min_temp*3.6) AS DECIMAL(10,2)) mintemp, CAST((max_temp*3.6) AS DECIMAL(10,2)) maxtemp, CAST((avg_temp*3.6) AS DECIMAL(10,2)) avgtemp from $tableChannel as channelv
			Inner Join(
			select max(datatime) as maxdate, channelid, max(value) max_temp,min(value) min_temp, avg(value) avg_temp, datatime from $tableChannel
			where channelid =:channelid group by date(datatime)) t
			ON t.maxdate = channelv.datatime and channelv.channelid = t.channelid
			where t.datatime > date_add( (select date(max(datatime)) from $tableChannel where  channelid =:channelid)
            , interval -7 day) 
            and channelv.channelid =:channelid order by date(datatime) asc";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getChartTodayAllValue($channelId, $tdate) {
        
        $tableChannel =  'channel_'.$channelId.'_values';
        
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i') valuedate, DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i') valuetime , channelv.value value from $tableChannel channelv 
			 where channelid =:channelid and date(channelv.datatime)=:tdate order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function get24HrAllData($channelId, $tdate, $hrvalue) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i')  valuedate , channelv.value value from $tableChannel channelv
			where channelv.channelid =:channelid  and  
                        (channelv.datatime >= date_add((select max(datatime) as maxdate from $tableChannel), interval -:hrvalue HOUR)) order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":hrvalue", $hrvalue);
        $stmt->execute();
        return $stmt;
    }
    
       public function get24HrAllDataCustom($channelId, $startdate, $enddate) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i')  valuedate , channelv.value value from $tableChannel channelv
			where channelv.channelid =:channelid  and  
                        channelv.datatime between :startdate and :enddate
                         order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":startdate", $startdate);
        $stmt->bindParam(":enddate", $enddate);
        $stmt->execute();
        return $stmt;
    }

    public function getTodayAvgHrData($channelId, $tdate) {
        $tableChannel =  'channel_'.$channelId.'_values';
        $mainTableChannel ='channel_'.$channelId;
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        
//        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate , CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
//			where channelv.channelid =:channelid and date(channelv.datatime)=:tdate  
//			group by  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) order by valuedate";
        
        
        $sqlQuery = "select hr as valuedate , IFNULL(value, 0) as value  from 
                        (select (DATE_FORMAT(datatime, '%m-%d-%Y %H')) hr from $mainTableChannel where date(datatime)=:tdate
                        group by  (DATE_FORMAT(datatime, '%m-%d-%Y %H'))) t1 left join
                        (select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate , CAST($aggregate(channelv.value) AS DECIMAL(10,2))  value from $tableChannel channelv
                                                where channelv.channelid =:channelid and date(channelv.datatime)=:tdate  
                                    group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H'))t2
                        on t1.hr = t2.valuedate";
        
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function get24HrAvgHrData($channelId, $tdate, $hrvalue) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
       /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  (channelv.datatime> date_add((select max(datatime) from $tableChannel where  channelid =:channelid), interval - :hrvalue HOUR))
			group by hour( channelv.valuedate ) , day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":hrvalue", $hrvalue);
        $stmt->execute();
        return $stmt;
    }

    
    public function getAvgHrDataCustom($channelId, $fromdate, $todate) {
        $tableChannel =  'channel_'.$channelId.'_values';
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and 
                        channelv.datatime between :fromdate and :todate
			group by hour( channelv.valuedate ) , day( channelv.valuedate ) order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":fromdate", $fromdate);
        $stmt->bindParam(":todate", $todate);
        $stmt->execute();
        return $stmt;
    }
    
    public function get1WeekAvgHrData($channelId) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tableChannel where  channelid =:channelid), interval -7 DAY))
			group by  day( channelv.valuedate ),hour( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getThisMonthAvgHrData($channelId) {
        
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from $tableChannel where  channelid =:channelid)
			group by  day( channelv.valuedate ),hour( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getLast1MonthAvgDayData($channelId) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  ( date(channelv.datatime) >= date_add((select max(datatime) from $tableChannel where  channelid =:channelid), interval -1 Month))
			group by  day( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getThisMonthAvgDayData($channelId) {
        $tableChannel =  'channel_'.$channelId.'_values';
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv 
			where channelv.channelid =:channelid and  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from $tableChannel where  channelid =:channelid)
			group by  day( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }
//
    public function getThisYearAvgDayData($channelId) {        
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select  (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from $tableChannel where  channelid =:channelid)
			group by  DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getYearAvgDayData($channelId,$dayvalue) {        
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv 
			where channelv.channelid =:channelid and  date(channelv.datatime) > date_add((select max(date(datatime)) from $tableChannel where  channelid =:channelid), interval -:dayvalue day)
			group by  day( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":dayvalue", $dayvalue);
        $stmt->execute();
        return $stmt;
    }

    public function getYearAvgDayDataCustom($channelId,$fromDate,$toDate) {        
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv 
			where channelv.channelid =:channelid and 
                        date(channelv.datatime) between :fromDate and :toDate
			group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y')  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        return $stmt;
    }
    
    public function getThisYearAvgMonthData($channelId) {        
        $tableChannel =  'channel_'.$channelId.'_values';
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select  (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv
			where channelv.channelid =:channelid and  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from $tableChannel where  channelid =:channelid)
			group by  Month( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        return $stmt;
    }

    public function getYearsAvgMonthsData($channelId,$monthvalue) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv 
			where channelv.channelid =:channelid and  date(channelv.datatime) > date_add((select max(date(datatime)) from $tableChannel where  channelid =:channelid), interval -:monthvalue Month)
			group by  Month( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":monthvalue", $monthvalue);
        $stmt->execute();
        return $stmt;
    }

    public function getYearsAvgMonthsDataCustom($channelId,$fromDate, $toDate) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        /**
         * This is special case of sum of percipitation and sunshine
         * 0101
         */
        $aggregate = 'avg';
        if($channelId=='0101' || $channelId=='0124'){
          $aggregate = 'sum'; 
        }
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,  CAST(($aggregate(channelv.value)) AS DECIMAL(10,2))  value from $tableChannel channelv 
			where channelv.channelid =:channelid and  
                        date(channelv.datatime) between :fromDate and :toDate
			group by  Month( channelv.valuedate )  order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        return $stmt;
    }
    
}
