<?php

/**
 * Description of ChannelDataDashComp
 *
 * @author USER
 */
class ChannelDataDashWindComp {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function findWindDirection10Mins($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10)) as windDirlast10mins, DATE_FORMAT(DATE_SUB(datatime, INTERVAL 10 Minute), '%m-%d-%Y %H:%i')  last10mins , 
                        DATE_FORMAT(datatime , '%m-%d-%Y') dataPer ,
                        DATE_FORMAT(datatime , '%H:%i') dataPerTime
                     from channel_0114_values
                     where channelid ='0114' and  date(datatime) = :tdate and datatime > 
			 DATE_SUB((select max(datatime) from channel_0114_values where  channelid ='0114' and  date(datatime) = :tdate), INTERVAL 10 Minute)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findWindDirection1Hour($tdate) {
        
        $sqlQuery = "select 
                        ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedateHr,
                        CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                                            where channelv.channelid ='0114' and  (channelv.datatime >= date_add((select max(datatime) from channel_0114_values where  channelid ='0114'), interval -1 HOUR))
                        order by DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') asc";
        $stmt = $this->conn->prepare($sqlQuery);        
        $stmt->execute();
        $finalResultArr;
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(                    
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }            
            $finalResultArr = $this->splitArrayWithNumValues1Hr($tempArr);             
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $finalResultArr;
       
    }

    public function findAvgWindSpeed10Mins($tdate) {
        $sqlQuery = "select CAST((value) AS DECIMAL(10,2)) as windspeedlast10Mins, DATE_SUB(datatime, INTERVAL 10 Minute) wind_speed_10_Mins, datatime  from channel_1112_values
			where channelid ='1112' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_1112_values where  channelid ='1112' and  date(datatime) = :tdate), INTERVAL 10 Minute)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findAvgWindSpeed1Hour($tdate) {
        $sqlQuery = "select CAST(AVG(value) AS DECIMAL(10,2)) as windspeedlast1hr from channel_1112_values
			where channelid ='1112' and  date(datatime) = :tdate and datatime > 
			DATE_SUB((select max(datatime) from channel_1112_values where  channelid ='1112' and  date(datatime) = :tdate), INTERVAL 1 Hour)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findMaxWindSpeedToday($tdate) {
        $sqlQuery = "select CAST(AVG(value) AS DECIMAL(10,2)) as avgWindspeedToday from channel_1112_values
			where channelid ='1112' and  date(datatime) = :tdate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findMaxGustLastHr($tdate) {
        $sqlQuery = "select CAST(value AS DECIMAL(10,2)) as maxwindgustlasthr,
				DATE_FORMAT(valuedate, '%H:%i') as gustmaxtimelasthr,
                DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i') as gustmaxtimelasthrdegree
                    from channel_1110_values
                       where channelid ='1110' and  date(datatime) = :tdate and datatime >= 
                       DATE_SUB((select max(datatime) from channel_1110_values where  channelid ='1110' and  date(datatime) =:tdate), INTERVAL 1 Hour)
                  order by value desc  limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }
    
     public function findMaxGustLastHrDegree($tdate) {
        $sqlQuery = "select CAST(value AS DECIMAL(10)) as maxwindgusthrdegree 
                      from channel_0114_values
                      where channelid ='0114' and  
                      DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i')  = :tdate ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    public function findMaxGustToday($tdate) {
        $sqlQuery = "select CAST(value AS DECIMAL(10,2)) as maxwindgusttoday ,
                        DATE_FORMAT(valuedate, '%H:%i') as maxwindgusttimetoday,
                        DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i') maxwindgusttimetodaydegree
                      from channel_1110_values
                      where channelid ='1110' and  date(datatime) = :tdate
                        order by value desc limit 1  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }
    
    public function findMaxGustTodayDegree($tdate) {
        $sqlQuery = "select CAST(value AS DECIMAL(10,0)) as maxwindgusttodaydegree                         
                      from channel_0114_values
                      where channelid ='0114' and  
                      DATE_FORMAT(valuedate, '%Y-%m-%d %H:%i')  = :tdate  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        return $stmt;
    }

    // Wind direction Query to find average data
    // 0114
    public function getWindDirectionTodayAvgHrData($channelId, $tdate) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                        where channelv.channelid ='0114' and date(channelv.datatime)=:tdate order by channelv.valuedate ;
                        ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirection24HrAvgHrData($channelId, $tdate, $hrvalue,$isCustom) {        
        $sqlQuery = null;
        if($isCustom==false){            
        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and  (channelv.datatime > date_add((select max(datatime) from channel_0114_values where  channelid ='0114'), interval -:hrvalue HOUR))
                    ";
        }else{            
            $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' 
                    and  channelv.datatime between :fromDate and :toDate
                    ";
        }
        
        $stmt = $this->conn->prepare($sqlQuery);       
        if($isCustom==false){
         $stmt->bindParam(":hrvalue", $hrvalue);         
        }else{
             $stmt->bindParam(":fromDate", $tdate);
             $stmt->bindParam(":toDate", $hrvalue);
        }
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirection1WeekAvgHrData($channelId, $tdate, $dayvalue) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and  (channelv.datatime > date_add((select max(datatime) from channel_0114_values where  channelid ='0114'), interval -:dayvalue DAY))
                    order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":dayvalue", $dayvalue);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionThisMonthAvgHrData($channelId) {

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2)) value from channel_0114_values channelv
                        where channelv.channelid ='0114' and  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from channel_0114_values where  channelid ='0114')     
                        order by channelv.valuedate";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirection1WeekAvgDayData($channelId) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and  (channelv.datatime > date_add((select max(datatime) from channel_0114_values where  channelid ='0114'), interval -7 DAY))
                    order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionLast1MonthAvgDayData($channelId) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                        where channelv.channelid ='0114' and  (channelv.datatime > date_add((select max(datatime) from channel_0114_values where  channelid ='0114'), interval -1 Month))
                        order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionThisMonthAvgDayData($channelId) {

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                        where channelv.channelid ='0114' and  DATE_FORMAT((channelv.datatime),'%m-%Y') = (select max(DATE_FORMAT((datatime),'%m-%Y')) from channel_0114_values where  channelid ='0114')     
                         order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

//
    public function getWindDirectionThisYearAvgDayData($channelId) {

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                        where channelv.channelid ='0114' and  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0114_values where  channelid ='0114')     
                        order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionYearAvgDayData($channelId, $fromDate, $toDate) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and 
                    date(channelv.datatime) between :fromDate and :toDate
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y')  order by channelv.valuedate";

        //date_add((select max(date(datatime)) from channel_0114_values where  channelid ='0114'), interval -:dayvalue day)
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionThisYearAvgMonthData($channelId) {

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and  DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) from channel_0114_values where  channelid ='0114')     
                    order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    public function getWindDirectionYearsAvgMonthsData($channelId, $fromDate, $toDate) {

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                        where channelv.channelid ='0114' and
                        date(channelv.datatime) between :fromDate and :toDate
                        order by channelv.valuedate";
        
        //> date_add((select max(date(datatime)) from channel_0114_values where  channelid ='0114'), interval -:monthvalue Month)

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $toDate);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "num" => $num,
                    "valuedate10Min" => $valuedate10Min,
                    "valuedateHr" => $valuedateHr,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $tempArr;
    }

    /**
     * Split the Array per 10 minutes for each hour
     * @param type $directionArray
     */
    function splitArrayWithNumValues($directionArray) {
        $results = array();
        $resultsValue = array();
        $valuedateHr = 0;
        $keyValue = 0;
        $array = array_chunk($directionArray, 1);
        foreach ($array as $chunk) {
            $key = $chunk;
            $arrayKey = $key[0];
            $keyValue = $arrayKey['num'];
            $valuedateHr = $arrayKey['valuedateHr'];
            foreach ($chunk as $subarray) {
                if ($subarray['valuedateHr'] == $valuedateHr) {
                    $results[$valuedateHr][] = $subarray;
                    $resultsValue[$valuedateHr][] = $subarray['value'];
                }
            }
        }

        $cnt = count($resultsValue);
        $arrayKeys = array_keys($resultsValue);
        $finalResultArr;
        for ($i = 0; $i < $cnt; $i++) {
            $arraykeyValue = $arrayKeys[$i];
            $resultsArr = $results[$arraykeyValue];
            $resultArr = $resultsArr[0];
            $avgDirectionDegree = $this->avgWindDirection($resultsValue[$arraykeyValue]);
            $avgDirection = $this->getWindDirection($avgDirectionDegree);
            $finalResultArr[] = array(
                'num' => $resultArr['num'],
                'valuedate' => $resultArr['valuedateHr'],
                'value' => $avgDirectionDegree,
                'avgDirection' => $avgDirection
            );
        }
        echo json_encode($finalResultArr);        
    }
    
    
    /**
     * Split the Array per 10 minutes for each hour
     * @param type $directionArray
     */
    function splitArrayWithNumValues1Hr($directionArray) {
        $results = array();
        $resultsValue = array();
        $valuedateHr = 0;
        $keyValue = 0;
        $array = array_chunk($directionArray, 1);
        foreach ($array as $chunk) {
            $key = $chunk;
            $arrayKey = $key[0];
            $keyValue = $arrayKey['num'];
            $valuedateHr = $arrayKey['valuedateHr'];
            foreach ($chunk as $subarray) {
                if ($subarray['valuedateHr'] == $valuedateHr) {
                    $results[$valuedateHr][] = $subarray;
                    $resultsValue[$valuedateHr][] = $subarray['value'];
                }
            }
        }

        $cnt = count($resultsValue);
        $arrayKeys = array_keys($resultsValue);
        $finalResultArr;
        for ($i = 0; $i < $cnt; $i++) {
            $arraykeyValue = $arrayKeys[$i];
            $resultsArr = $results[$arraykeyValue];
            $resultArr = $resultsArr[0];
            $avgDirectionDegree = $this->avgWindDirection($resultsValue[$arraykeyValue]);
            $avgDirection = $this->getWindDirection($avgDirectionDegree);
            $finalResultArr[] = array(
                'num' => $resultArr['num'],
                'valuedate' => $resultArr['valuedateHr'],
                'value' => $avgDirectionDegree,
                'avgDirection' => $avgDirection
            );
        }        
        return $finalResultArr;
    }
    
    function avgWindDirection($directionArr) {
        //Expects: An array containing wind direction in degrees
        //Returns: Average wind direction
        $directionArray = array_filter($directionArr);
        if (0 == count($directionArray)) {
            return("");
        }
        $sinsum = 0;
        $cossum = 0;

        foreach ($directionArray as $value) {
            $sinsum += sin(deg2rad($value));
            $cossum += cos(deg2rad($value));
        }
        return ((rad2deg(atan2($sinsum, $cossum)) + 360) % 360);
    }

    function getWindDirection($avgDegree) {

        $avgWindDirection = '';
        if (($avgDegree > 348.76 && $avgDegree <= 360) or ($avgDegree >= 0 && $avgDegree <= 11.25)) {
            $avgWindDirection = 'N';
        } else if ($avgDegree > 11.25 && $avgDegree <= 33.75) {
            $avgWindDirection = 'NNO';
        } else if ($avgDegree > 33.75 && $avgDegree <= 56.25) {
            $avgWindDirection = 'NO';
        } else if ($avgDegree > 56.25 && $avgDegree <= 78.75) {
            $avgWindDirection = 'ONO';
        } else if ($avgDegree > 78.75 && $avgDegree <= 101.25) {
            $avgWindDirection = 'O';
        } else if ($avgDegree > 101.25 && $avgDegree <= 123.75) {
            $avgWindDirection = 'OSO';
        } else if ($avgDegree > 123.75 && $avgDegree <= 146.25) {
            $avgWindDirection = 'SO';
        } else if ($avgDegree > 146.25 && $avgDegree <= 168.75) {
            $avgWindDirection = 'SSO';
        } else if ($avgDegree > 168.75 && $avgDegree <= 191.25) {
            $avgWindDirection = 'S';
        } else if ($avgDegree > 191.25 && $avgDegree <= 213.75) {
            $avgWindDirection = 'SSW';
        } else if ($avgDegree > 213.75 && $avgDegree <= 236.25) {
            $avgWindDirection = 'SW';
        } else if ($avgDegree > 236.25 && $avgDegree <= 256.75) {
            $avgWindDirection = 'WSW';
        } else if ($avgDegree > 256.75 && $avgDegree <= 281.25) {
            $avgWindDirection = 'W';
        } else if ($avgDegree > 281.25 && $avgDegree <= 303.75) {
            $avgWindDirection = 'WNW';
        } else if ($avgDegree > 303.75 && $avgDegree <= 326.25) {
            $avgWindDirection = 'NW';
        } else if ($avgDegree > 326.25 && $avgDegree <= 348.75) {
            $avgWindDirection = 'NNW';
        }
        return $avgWindDirection;
    }

}
