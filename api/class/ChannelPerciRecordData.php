<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelPerciRecordData {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getlast7Days($channelId) {
        $tablechannel = 'channel_' . $channelId;
        $sqlQuery = "select DATE_FORMAT(datatime, '%m-%d-%Y') datatime from $tablechannel  
                        group by date(datatime)
                        order by date(datatime)  desc 
                        limit 7 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dateArr = array();
        $i = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i = $i + 1;
            $dateArr[$i] = $row['datatime'];
        }
        return(array_reverse($dateArr));
    }

//0103
    public function getlast7Daysperci10min() {
        $arr7days = $this->getlast7Days('0103');
        $arr7dayvalue = array();
        foreach ($arr7days as $tdate) {
            $sqlQuery = "select CAST( max(value) AS DECIMAL(10,2)) as percimax10mins , DATE_FORMAT(valuedate, '%m-%d-%Y') valuedate from channel_0103_values 
                        where channelid ='0103'
                        and DATE_FORMAT(datatime, '%m-%d-%Y') = :tdate";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->bindParam(":tdate", $tdate);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $t = array(
                'percimax10mins' => $row['percimax10mins'] ?? 0.00,
                'tdate' => $tdate);
            array_push($arr7dayvalue, $t);
        }
//        print_r($arr7dayvalue);
        return $arr7dayvalue;
    }

    //0103 , 102 hourly data thats reason not cosidered
    public function getlast7Daysperci1Hrs() {
        $arr7days = $this->getlast7Days('0102');
        $arr7dayvalue = array();
        foreach ($arr7days as $tdate) {
            $sqlQuery = "select CAST( max(value) AS DECIMAL(10,2)) as percimax1hr , 
                        DATE_FORMAT(valuedate, '%m-%d-%Y') valuedate from channel_0102_values 
                        where channelid ='0102'
                        and DATE_FORMAT(datatime, '%m-%d-%Y') = :tdate";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->bindParam(":tdate", $tdate);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $t = array(
                'percimax1hr' => $row['percimax1hr'] ?? 0.00,
                'tdate' => $tdate);
            array_push($arr7dayvalue, $t);
        }
//         echo "<pre>";
//           print_r($arr7dayvalue);
//         echo "<post>";

        return $arr7dayvalue;
    }

    //0098
    public function getlast7DaysperciType($channelId) {
        $arr7days = $this->getlast7Days($channelId);
        $arr7dayvalue = array();
        foreach ($arr7days as $tdate) {
            $sqlQuery = "select distinct (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate, 
                        channelv.value  perciType from channel_0098_values channelv
                        where channelv.channelid ='0098' 
                        and channelv.value>0
                        and  DATE_FORMAT(channelv.datatime, '%Y-%m-%d') = :tdate";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->bindParam(":tdate", $tdate);
            $stmt->execute();
            $percitypeText = '';
            $percivalue = Array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $text = $this->getPerciTypeText($row['perciType'])??'';
                $percitypeText = ($percitypeText=='') ? $text : $percitypeText . '.' . $text;
                array_push($percivalue,$row['perciType']);
            }
            
            $e = array(
                "valuedate" => $tdate,
                "valuetxt" => ($percitypeText=='')?'--':$percitypeText,
                "value" => $percivalue
            );
            array_push($arr7dayvalue, $e);
        }        
        
        return $arr7dayvalue;
    }

    function getPerciTypeText($perciTypeValue) {
        $perciTypeText = '';
        if ($perciTypeValue == '60') {
            $perciTypeText = 'Rain';
        } else if ($perciTypeValue == '70') {
            $perciTypeText = 'Snow';
        } else if ($perciTypeValue == '67') {
            $perciTypeText = 'Freezing Rain';
        } else if ($perciTypeValue == '69') {
            $perciTypeText = 'Sleet';
        } else if ($perciTypeValue == '90') {
            $perciTypeText = 'Hail';
        } else if ($perciTypeValue == '40') {
            $perciTypeText = 'Unspecified';
        } else if ($perciTypeValue == '0') {
            $perciTypeText = 'Nothing';
        }
        return $perciTypeText;
    }

    public function getChartTodayAllValue($channelId, $tdate) {

        $tableChannel = 'channel_' . $channelId . '_values';

        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i') valuedate, "
                . "DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i') valuetime , "
                . "channelv.value value from $tableChannel channelv 
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
    
    
    public function getAllDataCustom($channelId, $fromDate, $toDate) {
        $tableChannel =  'channel_'.$channelId.'_values';
        
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H:%i')  valuedate , channelv.value value from $tableChannel channelv
			where channelv.channelid =:channelid  and  
                        channelv.datatime between :fromDate and :toDate  order by valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        return $stmt;
    }

        public function getHourlyTodayMaxData($tdate) {
        $sqlQuery = "  select distinct (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate, 
                        CAST((max(channelv.value)) AS DECIMAL(10,2)) maxperciInten  
                        from channel_0100_values channelv
                        where channelv.channelid ='0100'                         
                        and  DATE_FORMAT(channelv.datatime, '%Y-%m-%d') = :tdate
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H') 
                        order by valuedate asc ";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);        
        $stmt->execute();
        return $stmt;
    }
    
    public function getHourly24HrMaxData($tdate , $hr) {
        $sqlQuery = "  select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  ( date(channelv.datatime) >= 
                                date_add((select max(datatime) from channel_0100 ), interval -:hr HOUR))
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H') 
                        order by valuedate asc  ";        
        $stmt = $this->conn->prepare($sqlQuery);            
        $stmt->bindParam(":hr", $hr);
        $stmt->execute();
        return $stmt;
    }
    
     public function getHourlyMaxDataCustom($fromDate , $toDate) {
        $sqlQuery = "  select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where date(channelv.datatime) between :fromDate and :toDate
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H') 
                        order by valuedate asc  ";        
        $stmt = $this->conn->prepare($sqlQuery);            
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();
        return $stmt;
    }
    
    
    
    public function getHourly1WeekMaxData( ) {
        $sqlQuery = "  select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  ( date(channelv.datatime) >= 
                                date_add((select max(datatime) from channel_0100 ), interval -7 DAY))
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H') 
                        order by valuedate asc   ";        
        $stmt = $this->conn->prepare($sqlQuery);                      
        $stmt->execute();
        return $stmt;
    }
    
     
    public function getHourlyTmonthMaxData() {        
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  DATE_FORMAT((channelv.datatime),'%m-%Y') =
                        (select max(DATE_FORMAT((datatime),'%m-%Y')) from 
                        channel_0101_values where  channelid ='0101') 
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y %H') 
                        order by valuedate asc ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }
    
    
      public function getDaily1WeekMaxData() {        
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  ( date(channelv.datatime) >= 
                                date_add((select max(datatime) from channel_0100 ), interval -7 DAY))
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') 
                        order by valuedate asc   ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }
    
        public function getDaily1MonthMaxData() {        
        
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  ( date(channelv.datatime) >= 
                                date_add((select max(datatime) from channel_0100 ), interval -1 Month))
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') 
                        order by valuedate asc  ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }
    
        public function getDailyTMonthMaxData() { 
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select (DATE_FORMAT(max(datatime),'%m-%Y')) from channel_0101_values where  channelid ='0101') 
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') 
                        order by valuedate asc ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }
    
    public function getDailyTYearMaxData() { 
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0101_values where  channelid ='0101') 
                        group by DATE_FORMAT(channelv.valuedate, '%m-%d-%Y') 
                        order by valuedate asc  ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }
    
       public function getMonthlyTYearMaxData() { 
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,  
                        CAST((max(channelv.value)) AS DECIMAL(10,2))  maxperciInten 
                        from channel_0100_values channelv
                        where  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0101_values where  channelid ='0101') 
                        group by DATE_FORMAT(channelv.valuedate, '%m-%Y') 
                        order by valuedate asc ";
        $stmt = $this->conn->prepare($sqlQuery);                
        $stmt->execute();
        return $stmt;
    }

}
