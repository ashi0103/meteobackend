<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelWindDataPage {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMinAvailableDates($channelId) {
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select min(datatime) mindate, max(datatime) maxdate  from $tablechannelmain where  channelid =:channelid";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr;
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "minimumdate" => $row['mindate'],
                "maximumdate" => $row['maxdate']
            );
        }
//             echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
        return $tempArr;
    }

    public function getWindDurationPerDays($sDate, $eDate) {

        $sqlQuery = " select  date(t.datatime) valuedate, CAST(avg(t.value) AS DECIMAL(10,2)) avgwind,
                        (select  value from channel_1112_values p where 
                        date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxwind,
                        (select DATE_FORMAT(valuedate, '%H:%i' ) from channel_1112_values p 
                     where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxwindtime,
                        (select value from channel_1110_values p where date(p.datatime) = date(t.datatime) 
                        order by p.value desc limit 1) maxgust,
                        (select DATE_FORMAT(valuedate, '%H:%i' )  from channel_1110_values p where 
                     date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxgusttime
                        from channel_1112_values t  
                        where date(t.datatime) between  :sdate and :edate
                        group by  date( t.datatime ) order by t.valuedate desc ";

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
                $i = $i + 1;
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'avgwind' => number_format($row['avgwind'] * 3.6, 2),
                    'maxwind' => number_format($row['maxwind'] * 3.6, 2),
                    'maxwindtime' => $row['maxwindtime'],
                    'maxgust' => number_format($row['maxgust'] * 3.6, 2),
                    'maxgusttime' => $row['maxgusttime']
                );
            }
        }
        return $tempArr;
    }

    public function getWindDailyAll($sDate, $eDate) {

        $sqlQuery = "select  t.valuedate valuedate ,  
                        CAST(t.value AS DECIMAL(10,2)) wind                    
                        from channel_1112_values t  
                        where date(t.datatime) between  :sdate and :edate
                        order by t.valuedate ";

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
                $i = $i + 1;
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'wind' => number_format($row['wind'] * 3.6, 2)
                );
            }
        }
        return $tempArr;
    }

    function getWindAllValue($param2, $param3) {

        $stmt = $this->getWindAllValues($param2, $param3);
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "valuedate" => $valuedate,
                    "valuetime" => $valuetime,
                    "value" => $value,
                    "avgDirection" => $this->getWindirection($value, "0114")
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
    }

    public function getRadarChartValues($sdate, $edate) {
        $allWindDirections = Array();
        $temp = $this->getWindAllValues('range0to2',$sdate, $edate, 0, 7.5);
        array_push($allWindDirections, $temp);
        $temp = $this->getWindAllValues('range2to4',$sdate, $edate, 7.51, 15);
        array_push($allWindDirections, $temp);
        $temp = $this->getWindAllValues('range4to6',$sdate, $edate, 15.1, 20);
        array_push($allWindDirections, $temp);
        $temp = $this->getWindAllValues('range6to8',$sdate, $edate, 20, 100);
        array_push($allWindDirections, $temp);

        return $allWindDirections;
    }

    public function getWindAllValues($range ,$sdate, $edate, $rangeFrom, $rangeTo) {

        $sqlQuery = " select 
                        ws.valuedate,
                         CAST((( ws.value*3.6)) AS DECIMAL(10,2)) wspeed, wd.value wdirection
                         from channel_1112_values ws join
                        channel_0114_values wd on ws.valuedate = wd.valuedate
                        where (ws.value*3.6  >=:rfrom and ws.value*3.6 <= :rto)
                        and  date(ws.valuedate) between :sdate and :edate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sdate);
        $stmt->bindParam(":edate", $edate);
        $stmt->bindParam(":rfrom", $rangeFrom);
        $stmt->bindParam(":rto", $rangeTo);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            $N = 0;
            $NNO = 0;
            $NO = 0;
            $ONO = 0;
            $O = 0;
            $OSO = 0;
            $SO = 0;
            $SSO = 0;
            $S = 0;
            $SSW = 0;
            $SW = 0;
            $WSW = 0;
            $W = 0;
            $WNW = 0;
            $NW = 0;
            $NNW = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $directionvalue = $row['wdirection'];
                $direction = $this->getWindirection($directionvalue, '0114');
                if ($direction == 'N') {
                    $N++;
                } else if ($direction == 'NNO') {
                    $NNO++;
                } else if ($direction == 'NO') {
                    $NO++;
                } else if ($direction == 'ONO') {
                    $ONO++;
                } else if ($direction == 'O') {
                    $O++;
                } else if ($direction == 'OSO') {
                    $OSO++;
                } else if ($direction == 'SO') {
                    $SO++;
                } else if ($direction == 'SSO') {
                    $SSO++;
                } else if ($direction == 'S') {
                    $S++;
                } else if ($direction == 'SSW') {
                    $SSW++;
                } else if ($direction == 'SW') {
                    $SW++;
                } else if ($direction == 'WSW') {
                    $WSW++;
                } else if ($direction == 'W') {
                    $W++;
                } else if ($direction == 'WNW') {
                    $WNW++;
                } else if ($direction == 'NW') {
                    $NW++;
                } else if ($direction == 'NNW') {
                    $NNW++;
                }
            }
            $tempArr = array($range => [$N == 0 ? null : $N, $NNO == 0 ? null : $NNO,
                    $NO == 0 ? null : $NO, $ONO == 0 ? null : $ONO,
                    $O == 0 ? null : $O, $OSO == 0 ? null : $OSO,
                    $SO == 0 ? null : $SO,
                    $SSO == 0 ? null : $SSO, $S == 0 ? null : $S,
                    $SSW == 0 ? null : $SSW, $SW == 0 ? null : $SW,
                    $WSW == 0 ? null : $WSW, $W == 0 ? null : $W,
                    $WNW == 0 ? null : $WNW, $NW == 0 ? null : $NW,
                    $NNW == 0 ? null : $NNW]);
        } else {
            $tempArr = array($range => [null, null, null, null, null, null, null, null,
                    null, null, null, null, null, null, null, null]);
        }
        return $tempArr;
    }

    function getWindirection($degree, $param2) {
        $direction = '';
        if ($param2 == '0114') {
            $direction = $this->getWindDirection($degree);
        }
        return $direction;
    }

// Wind direction Query to find average data
    // 0114
    public function getWindDirectionYearAvgDayData($sdate) {

        $sqlQuery = "select ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %i') ORDER BY channelv.valuedate) AS num,
                    DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y') valuedateHr, CAST(((channelv.value)) AS DECIMAL(10,2))  value from channel_0114_values channelv
                    where channelv.channelid ='0114' and 
                    date(channelv.datatime) between :fromDate and :toDate
                    group by  DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i')  order by channelv.valuedate";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $sdate);
        $stmt->bindParam(":toDate", $sdate);
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
            $finalResultArr = $this->splitArrayWithNumValues($tempArr);
            // echo json_encode($tempArr);
        } else {
            http_response_code(404);
            echo json_encode(
                    array("message" => "No record found.")
            );
        }
        return $finalResultArr;
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

//0130
    public function get1DayTemperature($tdate, $prefix) {

        $sqlQuery = "select  date(t.datatime) valuedate, CAST(avg(t.value) AS DECIMAL(10,2)) avgtemp,
                        (select value from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxtemp,
                        (select DATE_FORMAT(valuedate, '%H:%i' ) from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxtemptime,
                        (select value from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) mintemp,
                        (select DATE_FORMAT(valuedate, '%H:%i' )  from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) mintemptime
                        from channel_0130_values t  
                        where date(t.datatime) = :tdate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    $prefix . 'valuedate' => $tdate,
                    $prefix . 'avgtemp' => $row['avgtemp'],
                    $prefix . 'maxtemp' => $row['maxtemp'],
                    $prefix . 'maxtemptime' => $row['maxtemptime'],
                    $prefix . 'mintemp' => $row['mintemp'],
                    $prefix . 'mintemptime' => $row['mintemptime']
                );
                $tempArr = $e;
            }
            return($tempArr);
        } else {
            $tempArr = array(
                $prefix . "valuedate" => $tdate,
                $prefix . "avgtemp" => '-',
                $prefix . "maxtemp" => '-',
                $prefix . "maxtemptime" => '-',
                $prefix . "mintemp" => '-',
                $prefix . "mintemptime" => '-'
            );
            return($tempArr);
        }
    }

    /** Temperature of different periods Start * */
    public function findTemperatureThisWeek() {
        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%v'))  temperatureweek ,                     
                    CAST((AVG(channelv.value)) AS DECIMAL(10,2))  temperatureAvgTweek
                    from channel_0130_values channelv
                    where channelv.channelid ='0130' and 
                    DATE_FORMAT((channelv.datatime),'%v') = (select max(DATE_FORMAT((datatime),'%v')) 
                    from channel_0130_values where  channelid ='0130') ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureweek' => $row['temperatureweek'],
                    'temperatureAvgTweek' => $row['temperatureAvgTweek']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    /**
     * 
     * @param type $days (7 Days or 31 days)
     * @return type
     */
    public function findTemperatureByNoOfDays($days) {
        $sqlQuery = "select CAST((sum(channelv.value)) AS DECIMAL(10,2)) temperatureLastXDays ,
                    CAST((AVG(channelv.value)) AS DECIMAL(10,2)) temperatureAvgLastXDays                         
                    from channel_0130_values channelv
                    where channelv.channelid ='0130' 
                     and  ( date(channelv.datatime) >= date_add((select max(datatime) 
                    from channel_0130_values where  channelid ='0130'), interval -:days DAY))";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureLast' . $days . 'Days' => $row['temperatureLastXDays'],
                    'temperatureAvgLast' . $days . 'Days' => $row['temperatureAvgLastXDays']
                );
                $tempArr = $e;
            }
            return $tempArr;
        }
    }

    public function findTemperatureThisMonth() {
        $sqlQuery = "select  CAST((sum(channelv.value)) AS DECIMAL(10,2)) temperatureTMonth ,
                        CAST((AVG(channelv.value)) AS DECIMAL(10,2)) temperatureAvgTMonth                        
                        from channel_0130_values channelv
                        where channelv.channelid ='0130' 
                        and  ( DATE_FORMAT(channelv.datatime, '%m-%Y') = DATE_FORMAT((select max(datatime) 
                        from channel_0130_values where  channelid ='0130'),'%m-%Y' ))";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureTMonth' => $row['temperatureTMonth'],
                    'temperatureAvgTMonth' => $row['temperatureAvgTMonth']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureThisYear() {
        $sqlQuery = "select  CAST(sum(channelv.value) AS DECIMAL(10,2))  temperatureThisYear,
                     CAST(AVG(channelv.value) AS DECIMAL(10,2))  temperatureAvgThisYear  
                     from channel_0130_values channelv
                     where channelv.channelid ='0130' and  
                      DATE_FORMAT((channelv.datatime),'%Y') = (select max(DATE_FORMAT((datatime),'%Y')) 
                      from channel_0130_values where  channelid ='0130') ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureThisYear' => $row['temperatureThisYear'],
                    'temperatureAvgThisYear' => $row['temperatureAvgThisYear']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureLastFrost() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( min(t.value) AS DECIMAL(10,2)) mintemp
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having mintemp <0
                        order by t.datatime   desc
                        limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureLastFrostdate' => $row['valuedate'],
                    'temperatureLastFrostvalue' => $row['mintemp']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureFirstFrost() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( min(t.value) AS DECIMAL(10,2)) min
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having min <0
                        order by t.datatime   asc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureFirstFrostdate' => $row['valuedate'],
                    'temperatureFirstFrostvalue' => $row['min']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureLastIcyDay() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( max(t.value) AS DECIMAL(10,2)) icytemp
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having icytemp <0
                        order by t.datatime   desc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureIcydate' => $row['valuedate'],
                    'temperatureIcyvalue' => $row['icytemp']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureLastSummerDay() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( max(t.value) AS DECIMAL(10,2)) summertemp
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having summertemp >25
                        order by t.datatime   desc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureSummerdate' => $row['valuedate'],
                    'temperatureSummervalue' => $row['summertemp']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureLastHeatDay() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( max(t.value) AS DECIMAL(10,2)) heattemp
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having heattemp >30
                        order by t.datatime   desc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureheattempdate' => $row['valuedate'],
                    'temperatureheattempvalue' => $row['heattemp']
                );
                $tempArr = $e;
            }
            return($tempArr);
        }
    }

    public function findTemperatureLastHeatNight() {
        $sqlQuery = "select  date(t.datatime) valuedate, CAST( min(t.value) AS DECIMAL(10,2)) heatnighttemp
                        from channel_0130_values t  
                        where 
                        DATE_FORMAT((t.datatime),'%Y') = (DATE_FORMAT(('2021-02-15'),'%Y'))
                        group by date(datatime) having heatnighttemp >20
                        order by t.datatime   desc
                        limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    'temperatureheatnighttempdate' => $row['valuedate'],
                    'temperatureheatnighttempvalue' => $row['heatnighttemp']
                );
                $tempArr = $e;
            }
            return($tempArr);
        } else {
            $tempArr = array(
                "temperatureheatnighttempdate" => '-',
                "temperatureheatnighttempvalue" => '-'
            );
            return($tempArr);
        }
    }

    public function findLastTemperature() {
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

    /** Temperature of different periods End * */

    /** Temperature of Compare against Norm Month  Start */
    public function getWindCompAginstNorm($year) {

        $sqlQuery = " select t.valuedate as valuedate,
                        fullmonth
                   ,CAST(t.value AS DECIMAL(10,2) ) as value, dense_rank() 
                        OVER ( partition by DATE_FORMAT(valuedate, '%m-%Y') order by value desc ) 
                        AS rankvalue ,
                        t.months ,
                        t.norm,
                        CAST((t.value-t.norm) AS DECIMAL(10,2) ) abw
                        from    
                        (select                       
                        DATE_FORMAT(valuedate, '%m-%Y') valuedate,
                        AVG(value) value ,
                        (select value from norm_wind_values where monthvalue =  DATE_FORMAT(datatime, '%c')) norm ,
                         DATE_FORMAT(datatime, '%M') fullmonth,
                        DATE_FORMAT(datatime, '%m') months
                        from channel_1112_values
                        where channelid ='1112'
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
                    "value" => number_format($value * 3.6, 2),
                    "rankvalue" => $rankvalue,
                    "months" => $months,
                    "norm" => number_format($norm * 3.6, 2),
                    "abw" => number_format($abw * 3.6, 2),
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    /** Temperature of Compare against Norm Month  end */

    /** Temperature of Compare against  Year  Start */
    public function getWindCompAginstNormYear($noOfYear) {
        $sqlQuery = "select                       
                    DATE_FORMAT(valuedate, '%Y') valuedate,
                    CAST(avg(value) AS DECIMAL(10,2) ) as value ,
                   (select CAST(avg(value) AS DECIMAL(10,2) ) as normvalue from norm_wind_values)
					as norm
                    from channel_1112_values
                    where channelid ='1112'                     
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
                    "windyear" => number_format($value * 3.6, 2),
                    "windyearnorm" => number_format($norm * 3.6, 2),
                    "diff" => number_format(($value - $norm) * 3.6, 2)
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    /** Temperature balance End */
    public function getWindCalmPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(avg(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_1112_values channelv
                        where channelv.channelid ='1112'  
                        and 
                        date(channelv.datatime) >= :infromdate and date(channelv.datatime) <=:intodate
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
                    "value" => $value*3.6
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

    public function getWindWindyPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(AVG(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_1112_values channelv
                        where channelv.channelid ='1112'   
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
                    "value" => $value*3.6
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

    public function getWindHeatMap($input1, $input2) {

        date_default_timezone_set('UTC');
        $date = $input1;
        $end_date = $input2;
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getWindHeatMapByDay($date);
            if ($tempArr != null) {
                $mainArr[] = $tempArr;
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }

//        echo "<pre>";
//        print_r($mainArr);
//        echo "<post>";

        return $mainArr;
    }

    public function getWindHeatMapByDay($tDate) {

        $sqlQuery = " select alldate.mydate valuedate,ifnull(CAST(avgdate.avgtemp*3.6 AS DECIMAL(10,2) ) ,'-') avgwind  from
		(WITH RECURSIVE seq AS (SELECT 0 AS value UNION ALL SELECT value + 1 FROM seq WHERE value < 47) 
		SELECT DATE(:tdate) + INTERVAL (value * 30) MINUTE AS mydate 
		FROM seq AS parameter ORDER BY value) alldate left join
		(SELECT 
			FROM_UNIXTIME((UNIX_TIMESTAMP(`datatime`) DIV (30* 60) ) * (30*60)) thirtyHourInterval,
			CAST(((value)) AS DECIMAL(10,2)) avgtemp
			FROM channel_1112_values
			where date(datatime) = :tdate 
			GROUP BY UNIX_TIMESTAMP(`datatime`) DIV (30* 60)) avgdate
		on alldate.mydate = avgdate.thirtyHourInterval  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'valuedate' => $row['valuedate'],
                    'avgwind' => $row['avgwind']
                );
            }
        }
        return $tempArr;
    }

    public function getWindDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getWindDailyOverviewByDate($date);
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
                $tempArr = $this->getWindDailyOverviewByDate($date);
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
                $tempArr = $this->getWindDailyOverviewByDate($date);
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
                $tempArr = $this->getWindDailyOverviewByDate($tempdate);
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

    public function getWindDailyOverviewByDate($sdate) {

        $finalArr = Array();
        $avgTempArr = $this->getWindDailyAvg($sdate, $sdate);
        if ($avgTempArr != null) {
            $finalArr = array_merge($finalArr, $avgTempArr);
            $minTempArr = $this->getMinWindByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $minTempArr);
            $maxTempArr = $this->getMaxWindByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $maxTempArr);
            $maxGustArr = $this->getMaxWindGustByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $maxGustArr);
            $windDirectionArr = $this->getWindDirectionYearAvgDayData($sdate);
            $finalArr = array_merge($finalArr, $windDirectionArr);
//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";
        }
        return $finalArr;
        //       }
    }

    public function getWindDailyAvg($sDate, $eDate) {
        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgWind                    
                    from channel_1112_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                    group by  day( channelv.valuedate ) order by channelv.valuedate";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr = array(
                    'valuedate' => $row['valuedate'] ?? '',
                    'paramdate' => $row['paramdate'],
                    'avgWind' => number_format($row['avgWind'] * 3.6, 2)
                );
            }
        }
        return $tempArr;
    }

    public function getMinWindByDay($sDate, $eDate) {


        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minWind                    
                    from channel_1112_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value asc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $minTemp = array(
            'minwindtime' => $row['valuedate'],
            'minWind' => number_format($row['minWind'] * 3.6, 2)
        );

        return $minTemp;
    }

    public function getMaxWindByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxWind                    
                    from channel_1112_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array(
            'maxwindtime' => $row['valuedate'],
            'maxWind' => number_format($row['maxWind'] * 3.6, 2)
        );

        return $maxTemp;
    }

    public function getMaxWindGustByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxWind                    
                    from channel_1110_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array(
            'maxwindgusttime' => $row['valuedate'],
            'maxwindgust' => number_format($row['maxWind'] * 3.6, 2)
        );

        return $maxTemp;
    }

    public function getWindOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getWindOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getWindOverviewByMonth($monthYear);
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
                $tempArr = $this->getWindOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getWindOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

        return $mainArr;
    }

    public function getWindOverviewByMonth($sdate) {

        $finalArr = Array();
        $AvgTempArr = $this->getAverageWindPerMonth($sdate);
        $finalArr = array_merge($finalArr, $AvgTempArr);
        $minTempArr = $this->getMinWindByMonth($sdate);
        $finalArr = array_merge($finalArr, $minTempArr);
        $maxTempArr = $this->getMaxWindByMonth($sdate);
        $finalArr = array_merge($finalArr, $maxTempArr);
        $maxGustArr = $this->getMaxGustByMonth($sdate);
        $finalArr = array_merge($finalArr, $maxGustArr);
        $windDirectionArr = $this->getWindDirectionThisMonthAvgDayData($sdate);

        $finalArr = array_merge($finalArr, $windDirectionArr);

        return $finalArr;
    }

    public function getMinWindByMonth($tDate) {

        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minWind                    
                    from channel_1112_values channelv		
                    where    DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                    and channelv.value > 0
                     order by channelv.value asc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array();
        if ($row != null && $row['paramdate'] != null) {
            $globalRadAvg = array(
                'minwindtime' => $row['valuedate'],
                'minWind' => number_format($row['minWind'] * 3.6, 2)
            );
        }
        return $globalRadAvg;
    }

    public function getMaxWindByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxWind                    
                    from channel_1112_values channelv		
                    where   DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value desc limit 1  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array();
        if ($row != null && $row['paramdate'] != null) {
            $maxTemp = array(
                'maxwindtime' => $row['valuedate'],
                'maxWind' => number_format($row['maxWind'] * 3.6, 2)
            );
        }
        return $maxTemp;
    }

    public function getAverageWindPerMonth($sDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%m-%Y')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgWind 
                    from channel_1112_values channelv		
                    where  DATE_FORMAT(channelv.valuedate, '%m-%Y') =:sdate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row != null && $row['paramdate'] != null) {
                $tempArr = array(
                    'valuedate' => $row['valuedate'],
                    'paramdate' => $row['paramdate'],
                    'avgWind' => number_format($row['avgWind'] * 3.6, 2)
                );
            }
        }
        return $tempArr;
    }

    public function getMaxGustByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxGust                    
                    from channel_1110_values channelv		
                    where   DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value desc limit 1  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array();
        if ($row != null && $row['paramdate'] != null) {
            $maxTemp = array(
                'maxwindgusttime' => $row['valuedate'],
                'maxwindgust' => number_format($row['maxGust'] * 3.6, 2)
            );
        }
        return $maxTemp;
    }

    
    
    public function getWindDirectionThisMonthAvgDayData($sdate) {        
       
        $strDate = '01-'.$sdate;
        $d = new DateTime( $strDate ); 
        $todate = $d->format( 'Y-m-t' );
        $fromDate = $d->format( 'Y-m-d' );

        $sqlQuery = "select  ROW_NUMBER() OVER (PARTITION BY DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') ORDER BY channelv.valuedate) AS num,
                        DATE_FORMAT(channelv.valuedate, ' %m-%d-%Y %H:%i') valuedate10Min ,
                        DATE_FORMAT(channelv.valuedate, ' %m-%Y') valuedateHr,
                        CAST(((channelv.value)) AS DECIMAL(10,2))  value 
                        from channel_0114_values channelv
                        where channelv.channelid ='0114' and
                        date(channelv.datatime) between :fromDate and :toDate
                        order by channelv.valuedate";       
       

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":fromDate", $fromDate);
        $stmt->bindParam(":toDate", $todate);
        $stmt->execute();

        $itemCount = $stmt->rowCount();
        $tempArrValue = array();
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
            $tempArrValue = $this->splitArrayWithNumValues($tempArr);            
        }             
        return $tempArrValue;
    }
    


// ************************ *******************************************************
}
