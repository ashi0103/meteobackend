<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelTemperatureDataPage {

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

    public function getTemperatureDurationPerDays($sDate, $eDate) {

        $sqlQuery = "select  date(t.datatime) valuedate, CAST(avg(t.value) AS DECIMAL(10,2)) avgtemp,
                    (select value from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxtemp,
                    (select DATE_FORMAT(valuedate, '%H:%i' ) from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value desc limit 1) maxtemptime,
                    (select value from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) mintemp,
                    (select DATE_FORMAT(valuedate, '%H:%i' )  from channel_0130_values p where date(p.datatime) = date(t.datatime) order by p.value asc limit 1) mintemptime
                    from channel_0130_values t  
                    where date(t.datatime) between  :sdate and :edate
                    group by  date( t.datatime ) order by t.valuedate desc";

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
                    'avgtemp' => $row['avgtemp'],
                    'maxtemp' => $row['maxtemp'],
                    'maxtemptime' => $row['maxtemptime'],
                    'mintemp' => $row['mintemp'],
                    'mintemptime' => $row['mintemptime']
                );
            }
        }
        return $tempArr;
    }

    public function getTemperaturedailyAll($sDate, $eDate) {

        $sqlQuery = "select  t.valuedate valuedate ,  
                        CAST(t.value AS DECIMAL(10,2)) temp                    
                        from channel_0130_values t  
                        where date(t.datatime) between  :sdate and :edate
                        order by t.valuedate";

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
                    'temp' => $row['temp']
                );
            }
        }
        return $tempArr;
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
                    $prefix . 'avgtemp' => $row['avgtemp'] ?? '-',
                    $prefix . 'maxtemp' => $row['maxtemp'] ?? '-',
                    $prefix . 'maxtemptime' => $row['maxtemptime'] ?? '-',
                    $prefix . 'mintemp' => $row['mintemp'] ?? '-',
                    $prefix . 'mintemptime' => $row['mintemptime'] ?? '-'
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
                    'temperatureweek' => $row['temperatureweek'] ?? '-',
                    'temperatureAvgTweek' => $row['temperatureAvgTweek'] ?? '-'
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

    /** Temperature of different buckets Start * */
    public function findTemperatureBuckets($min, $max, $year) {

        $sqlQuery = "select count(value)*10 totaltime , DATE_FORMAT(valuedate, '%Y') as valueyear
                        from channel_0130_values
                        where channelid ='0130' and (value > :minvalue and value <= :maxvalue)
                        and DATE_FORMAT(valuedate, '%Y') = $year ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":minvalue", $min);
        $stmt->bindParam(":maxvalue", $max);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $util = new UtilCommon;
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_null($row['valueyear'])) {
                $tempArr = array(
                    "valuedate" => $year,
                    "value" => '-'
                );
            } else {
                $tempArr = array(
                    "valuedate" => $row['valueyear'],
                    "value" => $util->secondsToDayMinsHrs($row['totaltime'] * 60),
                    "valuetime" => $row['totaltime'],
                );
            }
            return $tempArr;
        }
    }

    /** Temperature of different buckets End * */

    /** Temperature of Compare against Norm Month  Start */
    public function getTemperatureCompAginstNorm($year) {

        $sqlQuery = "select t.valuedate as valuedate,
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
                    (select value from norm_temp_values where monthvalue =  DATE_FORMAT(datatime, '%c')) norm ,
                     DATE_FORMAT(datatime, '%M') fullmonth,
                    DATE_FORMAT(datatime, '%m') months
                    from channel_0130_values
                    where channelid ='0130'
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
                    "value" => $value,
                    "rankvalue" => $rankvalue,
                    "months" => $months,
                    "norm" => $norm,
                    "abw" => $abw,
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    /** Temperature of Compare against Norm Month  end */

    /** Temperature of Compare against  Year  Start */
    public function getTemperatureCompAginstNormYear($noOfYear) {

        $year = date("Y");
        $syear = $year - $noOfYear;
        /* check if start year is less than 2021, as we don't have data before 2021 setting
          start year as 2021 */
        if ($syear < 2021) {
            $syear = 2021;
        }

        for ($t = $syear; $t < $year + 1; $t++) {

            $sqlQueryTemp = " select   t.datatime ,   t.value datavalue, 
                        if( isnull(t.value)=0 , CAST(avg(t.value) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ), 0 ) as value 
                             from
                                        (
                                                select t1.datatime,t1.datayear, value from 
                                   (select distinct DATE_FORMAT(date, '%Y-%m-%d') datatime,
                                   DATE_FORMAT(date, ' %Y') datayear   
                                   from yeardays where  Year(date) = $t order by date	
                                   ) t1 left join 
                                   (select DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') valuedate ,
                                        CAST(avg(channelv.value) AS DECIMAL(10,2) ) as value from channel_0130_values channelv
                                        where channelv.channelid ='0130'  
                                        and   DATE_FORMAT((channelv.datatime),'%Y') = $t
                                   group by  DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') ) t2
                                   on t1.datatime =t2.valuedate   
                                   order by datatime   
                                   ) t
                                   where t.datayear  = $t
                                   group by  datatime having t.value is not null 
                                   order by datatime desc limit 1 ";

            $stmtTemp = $this->conn->prepare($sqlQueryTemp);
            $stmtTemp->execute();
//            if( $stmtTemp->rowCount()<1){
//                continue;
//            }

            $rowTemp = $stmtTemp->fetch(PDO::FETCH_ASSOC);
            $tempvalue = $rowTemp['value'] ?? null;
            $tempdatatime = $rowTemp['datatime'] ?? null;

            $sqlQueryNorm = " select  t1.datatime,t1.datavalue,t1.value from
                        (select  t.datatime, t.norm datavalue, 
                        CAST(avg(t.norm) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ) value from
                        (select  distinct  date(date) datatime ,DAY(LAST_DAY(date(date))),
                                   (select CAST((value) AS DECIMAL(10,2) ) as normvalue
                                         from norm_temp_values where 
                                          monthvalue = DATE_FORMAT(date, '%c')
                                          ) norm
			   from  yeardays where  Year(date) = $t	                     
			)t  order by t.datatime ) t1 where t1.datatime =  :valdate";

            $stmtNorm = $this->conn->prepare($sqlQueryNorm);
            $stmtNorm->bindParam(":valdate", $tempdatatime);
            $stmtNorm->execute();

            $rowNorm = $stmtNorm->fetch(PDO::FETCH_ASSOC);
            $normvalue = $rowNorm['value'] ?? null;

            $valuedate = $t;
            //$finalArr = Array();
            $tempArr[] = array(
                "year" => $valuedate,
                "perciyear" => $tempvalue,
                "perciyearnorm" => $normvalue,
                "diff" => number_format(($tempvalue - $normvalue), 2)
            );
            // array_push($finalArr, $tempArr);
        }
        return $tempArr;
    }

    public function getCumulativeTempByYear($inputYearFrom, $inputYearTo) {
        $diff = $inputYearTo - $inputYearFrom;
        $yearFrom = $inputYearFrom;
        $finalArr = Array();

        for ($t = 0; $t < $diff + 1; $t++) {
            $dateArray = $this->getMinAvailableDates('0130');
            $dateminvalue = new DateTime($dateArray['minimumdate']);
            $datemaxvalue = new DateTime($dateArray['maximumdate']);
            $minDate = $dateminvalue->format("Y");
            $maxDate = $datemaxvalue->format("Y");
            if ($yearFrom < $minDate || $yearFrom > $maxDate) {                
                $yearFrom = $yearFrom + 1;
                continue;
            }
            $tempArr = $this->getCumulativeTemperature($yearFrom);
            if ($tempArr == null) {
                $tempArr = [];
            } else {
                $mainArray = array(
                    'yearData' => $tempArr,
                    'years' => $yearFrom
                );
                array_push($finalArr, $mainArray);
            }
            $yearFrom = $yearFrom + 1;
        }

        $tempArr = $this->getTempCumulativeNormValue();
        $mainArray = array(
            'yearData' => $tempArr,
            'years' => 'norm'
        );

        array_push($finalArr, $mainArray);

        return $finalArr;
    }

    public function getCumulativeTemperature($inputYear) {


        $sqlQuery = "  select DATE_FORMAT(t.datatime, '%Y-%m-%d') datatime , 
                        t.value datavalue, 
                        if( isnull(t.value)=0 , CAST(avg(t.value) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ), null ) as value 
                        from
                    (
                        select t1.datatime,t1.datayear, value from 
                   (select distinct DATE_FORMAT(date, '%Y-%m-%d') datatime,
                   DATE_FORMAT(date, ' %Y') datayear   
                   from yeardays where  Year(date) = $inputYear order by date	
                   ) t1 left join 
                   (select DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') valuedate ,
                    CAST(avg(channelv.value) AS DECIMAL(10,2) ) value from channel_0130_values channelv
                    where channelv.channelid ='0130'  
                    and   DATE_FORMAT((channelv.datatime),'%Y') =$inputYear                    
                   group by  DATE_FORMAT(channelv.valuedate, '%Y-%m-%d') ) t2
                   on t1.datatime =t2.valuedate   
                   order by datatime   
                   ) t
                   where t.datayear  = $inputYear 
                   group by  datatime ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $datatime,
                    "datavalue" => $datavalue,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getTempCumulativeNormValue() {
        $sqlQuery = " select  datatime , 
			t.norm datavalue, 
                        CAST(avg(t.norm) OVER(ORDER BY t.datatime) AS DECIMAL(10,2) ) value from
                        (select  distinct  date(date) datatime ,DAY(LAST_DAY(date(date))),
                               (select CAST((value) AS DECIMAL(10,2) ) as normvalue
                                 from norm_temp_values where 
                                  monthvalue = DATE_FORMAT(date, '%c')
                                  ) norm
                               from  yeardays where  Year(date) = 2021	                     
                            )t order by date(datatime) asc ";

        $stmt = $this->conn->prepare($sqlQuery);
        //$stmt->bindParam(":inyear", $inputYear);        
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $datatime,
                    "datavalue" => $datavalue,
                    "value" => $value
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    /** Temperature of Compare against  Year  End */

    /** Temperature balance Start */
    public function InsertTemperatureBalanceByDay($inputDate, $inputDay) {
        // Set timezone
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        date_default_timezone_set('UTC');
        $end_date = $inputDate;
        $tablename;
        if ($inputDay == "30") {
            $tablename = "temperaturebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "temperaturebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "temperaturebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "temperaturebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "temperaturebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "temperaturebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "temperaturebalance365Days";
        }

        $sql = "TRUNCATE TABLE $tablename";
        $truncstmt = $this->conn->prepare($sql);
        $truncstmt->execute();

        $stmt = $this->conn->prepare("INSERT INTO $tablename(inputdate, sumvalue, avgnorm,percent)"
                . "                  VALUES (:inputdate, :sumvalue,:avgnorm, :percent)");

        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getTemperatureBalance($date, $inputDay);
            $tempvalue = $tempArr[0];
            $inputdate = $tempvalue['inputdate'];
            $sumvalue = $tempvalue['sumvalue'];
            $avgnorm = $tempvalue['avgnorm'];
            $percent = $tempvalue['percent'];
            $stmt->bindValue(":inputdate", $inputdate);
            $stmt->bindValue(":sumvalue", $sumvalue);
            $stmt->bindValue(":avgnorm", $avgnorm);
            $stmt->bindValue(":percent", $percent);
            $stmt->execute();
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        echo " successful inserted " . $inputDay;
    }

    public function getTemperatureBalance($inputDate, $inputDay) {
        $sqlQuery = "select CAST( avg(t2.value) AS DECIMAL(10,2) ) sumvalue ,CAST( avg(t1.norm) AS DECIMAL(10,2) ) avgnorm ,  
                    CAST((avg(t2.value)-avg(t1.norm)) AS DECIMAL(10,2) ) percent ,
                    t1.datatime
                    from 
                    (select  distinct DATE_FORMAT(datatime, '%d-%m-%Y') datatime ,
                    (select CAST((value) AS DECIMAL(10,3) ) as normvalue
                   from norm_temp_values where 
                    monthvalue = DATE_FORMAT(datatime, '%c')
                    ) norm 
                     from  channel_0130 
                     where  date(datatime) > 
                     date_add(:indate, interval -:inday day) and date(datatime) <= :indate
                       ) t1 left join
                      (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                       CAST(AVG(channelv.value) AS DECIMAL(10,2) ) value from channel_0130_values channelv
                      where channelv.channelid ='0130' and 
                       date(channelv.datatime) > 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') order by channelv.valuedate desc) t2                    
                        on t1.datatime = t2.valuedate
                      order by DATE_FORMAT(t1.datatime, '%d-%m-%Y') asc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->bindParam(":inday", $inputDay);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvalue" => $sumvalue,
                    "avgnorm" => $avgnorm,
                    "percent" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getTemperatureBalanceValues($inputDate, $inputDay) {

        $tablename;
        if ($inputDay == "30") {
            $tablename = "temperaturebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "temperaturebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "temperaturebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "temperaturebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "temperaturebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "temperaturebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "temperaturebalance365Days";
        }

        $sqlQuery = "select inputdate,sumvalue,avgnorm,percent from "
                . " $tablename where inputdate= :indate ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvalue" => $sumvalue,
                    "avgnorm" => $avgnorm,
                    "percent" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getTemperatureBalanceMonthAgoValue($inputDate, $inputDay) {

        $tablename;
        if ($inputDay == "30") {
            $tablename = "temperaturebalance30Days";
        } else if ($inputDay == "60") {
            $tablename = "temperaturebalance60Days";
        } else if ($inputDay == "90") {
            $tablename = "temperaturebalance90Days";
        } else if ($inputDay == "121") {
            $tablename = "temperaturebalance121Days";
        } else if ($inputDay == "182") {
            $tablename = "temperaturebalance182Days";
        } else if ($inputDay == "273") {
            $tablename = "temperaturebalance273Days";
        } else if ($inputDay == "365") {
            $tablename = "temperaturebalance365Days";
        }

        $sqlQuery = "select inputdate,sumvalue,avgnorm,percent from $tablename where inputdate= 
                        date_add( :indate, interval -1 MONTH) ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "inputdate" => $inputDate,
                    "sumvaluemonth" => $sumvalue,
                    "avgnormmonth" => $avgnorm,
                    "percentmonth" => $percent
                );
                array_push($tempArr, $e);
            }
            return $tempArr;
        }
    }

    public function getTemperatureBalanceByDay($inputDate, $inputDay) {
        // Set timezone
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        $tempArr = $this->getMinAvailableDates('0130');
        $availableDate = date('Y-m-d', strtotime($tempArr['minimumdate']));
        if (strtotime($availableDate) >= strtotime($date)) {
            $date = $availableDate;
        }

        date_default_timezone_set('UTC');
        $end_date = $inputDate;
        $mainArr = array();
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getTemperatureBalanceValues($date, $inputDay);
            $mainArr[] = $tempArr;
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
//         echo "<pre>";
//         print_r($mainArr);
//         echo "<post>";
        return $mainArr;
    }

    /** Temperature balance End */
    public function getTemperatureColdPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(avg(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_0130_values channelv
                        where channelv.channelid ='0130'  
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
                    "value" => $value
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

    public function getTemperatureWarmPeriod($inputFromDate, $inputToDate, $inputValue) {
        $sqlQuery = "select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        CAST(AVG(channelv.value) AS DECIMAL(10,2) ) value
                        from channel_0130_values channelv
                        where channelv.channelid ='0130'   
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
                    "value" => $value
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

    public function getTemperatureHeatMap($input1, $input2) {

        date_default_timezone_set('UTC');
        $date = $input1;
        $end_date = $input2;
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->getTemperatureHeatMapByDay($date);
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

    public function getTemperatureHeatMapByDay($tDate) {

        $sqlQuery = " select alldate.mydate valuedate,ifnull(avgdate.avgtemp,'-') avgtemp  from
		(WITH RECURSIVE seq AS (SELECT 0 AS value UNION ALL SELECT value + 1 FROM seq WHERE value < 47) 
		SELECT DATE(:tdate) + INTERVAL (value * 30) MINUTE AS mydate 
		FROM seq AS parameter ORDER BY value) alldate left join
		(SELECT 
			FROM_UNIXTIME((UNIX_TIMESTAMP(`datatime`) DIV (30* 60) ) * (30*60)) thirtyHourInterval,
			CAST(((value)) AS DECIMAL(10,2)) avgtemp
			FROM channel_0130_values
			where date(datatime) = :tdate 
			GROUP BY UNIX_TIMESTAMP(`datatime`) DIV (30* 60)) avgdate
		on alldate.mydate = avgdate.thirtyHourInterval ";

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
                    'avgtemp' => $row['avgtemp']
                );
            }
        }
        return $tempArr;
    }

    public function getTemperatureDailyOverviewByDatesParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getTemperatureDailyOverviewByDate($date);
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
                $tempArr = $this->getTemperatureDailyOverviewByDate($date);
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
                $tempArr = $this->getTemperatureDailyOverviewByDate($date);
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
                $tempArr = $this->getTemperatureDailyOverviewByDate($tempdate);
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

    public function getTemperatureDailyOverviewByDate($sdate) {

        $finalArr = Array();
        $avgTempArr = $this->getTempDailyAvg($sdate, $sdate);
        if ($avgTempArr != null) {
            $finalArr = array_merge($finalArr, $avgTempArr);
            $minTempArr = $this->getMinTempByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $minTempArr);
            $maxTempArr = $this->getMaxTempByDay($sdate, $sdate);
            $finalArr = array_merge($finalArr, $maxTempArr);
//            echo "<pre>";
//            print_r($finalArr);
//            echo "<post>";
        }
        return $finalArr;
        //       }
    }

    public function getTempDailyAvg($sDate, $eDate) {
        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%m-%d-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgTemp                    
                    from channel_0130_values channelv		
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
                    'avgTemp' => $row['avgTemp']
                );
            }
        }
        return $tempArr;
    }

    public function getMinTempByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minTemp                    
                    from channel_0130_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value asc limit 1 ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $minTemp = array(
            'mintemptime' => $row['valuedate'],
            'mintemp' => $row['minTemp']
        );

        return $minTemp;
    }

    public function getMaxTempByDay($sDate, $eDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxTemp                    
                    from channel_0130_values channelv		
                    where  date(channelv.datatime) 
                    between  :sdate and :edate
                     order by channelv.value desc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":sdate", $sDate);
        $stmt->bindParam(":edate", $eDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array(
            'maxtemptime' => $row['valuedate'],
            'maxtemp' => $row['maxTemp']
        );

        return $maxTemp;
    }

    public function getTemperatureOverviewByMonthParam($inputSelection, $input1, $input2, $datelist) {

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
                $tempArr = $this->getTemperatureOverviewByMonth($monthYear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectYandM) {
            $str_arr = explode(",", $datelist);
            for ($t = 0; $t < count($str_arr); $t++) {
                $monthYear = trim($str_arr[$t]);
                $tempArr = $this->getTemperatureOverviewByMonth($monthYear);
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
                $tempArr = $this->getTemperatureOverviewByMonth($monthyear);
                if ($tempArr != null) {
                    $mainArr[] = $tempArr;
                }
            }
        } else if ($inputSelection == $selectCompare) {
            $str_arr = explode(",", $datelist);
            $uacnt = count($str_arr);
            for ($t = 0; $t < $uacnt; $t++) {
                $tempdate = trim($str_arr[$t]);
                $tempArr = $this->getTemperatureOverviewByMonth($tempdate);
                $mainArr[] = $tempArr;
            }
        }

        return $mainArr;
    }

    public function getTemperatureOverviewByMonth($sdate) {

        $finalArr = Array();
        $AvgTempArr = $this->getAverageTempPerMonth($sdate);
        $finalArr = array_merge($finalArr, $AvgTempArr);
        $minTempArr = $this->getMinTempByMonth($sdate);
        $finalArr = array_merge($finalArr, $minTempArr);
        $maxTempArr = $this->getMaxTempByMonth($sdate);
        $finalArr = array_merge($finalArr, $maxTempArr);

        return $finalArr;
    }

    public function getMinTempByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  minTemp                    
                    from channel_0130_values channelv		
                    where    DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value asc limit 1;";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $globalRadAvg = array();
        if ($row != null && $row['paramdate'] != null) {
            $globalRadAvg = array(
                'mintemptime' => $row['valuedate'] ?? '-',
                'mintemp' => $row['minTemp'] ?? '-'
            );
        }
        return $globalRadAvg;
    }

    public function getMaxTempByMonth($tDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%d-%m-%Y %H:%i')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%Y-%m-%d')) paramdate,                    
                    CAST(((channelv.value)) AS DECIMAL(10,2))  maxTemp                    
                    from channel_0130_values channelv		
                    where   DATE_FORMAT(valuedate, '%m-%Y') =:tdate
                     order by channelv.value desc limit 1  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tDate);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTemp = array();
        if ($row != null && $row['paramdate'] != null) {
            $maxTemp = array(
                'maxtemptime' => $row['valuedate'],
                'maxtemp' => $row['maxTemp']
            );
        }
        return $maxTemp;
    }

    public function getAverageTempPerMonth($sDate) {

        $sqlQuery = "select (DATE_FORMAT(channelv.valuedate, '%m-%Y')) valuedate,
                    (DATE_FORMAT(channelv.datatime, '%m-%Y')) paramdate,
                    CAST((avg(channelv.value)) AS DECIMAL(10,2))  avgTemp 
                    from channel_0130_values channelv		
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
                    'avgTemp' => $row['avgTemp']
                );
            }
        }
        return $tempArr;
    }

    public function get1MonthFirstLastTemperature($tdate) {

        $sqlQuery = " select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d %H:%i')) firstSunshine,  
                        CAST(((channelv.value)) AS DECIMAL(10,2))  firstSunDuration , 
                        date(channelv.datatime) datatime 
                        from channel_0124_values channelv  where
                        (DATE_FORMAT(channelv.datatime, '%m-%Y')) = :tdate and value > 0   
                        order by  channelv.datatime  asc limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":tdate", $tdate);
        $stmt->execute();

        $sqlQuerylastSunshine = " select (DATE_FORMAT(channelv.valuedate, '%Y-%m-%d %H:%i')) lastSunshine,  
                        CAST(((channelv.value)) AS DECIMAL(10,2))  lastSunDuration , 
                        date(channelv.datatime) datatime 
                        from channel_0124_values channelv  where
                        (DATE_FORMAT(channelv.datatime, '%m-%Y')) = :tdate and value > 0   
                        order by  channelv.datatime  desc limit 1";

        $stmtLastSun = $this->conn->prepare($sqlQuerylastSunshine);
        $stmtLastSun->bindParam(":tdate", $tdate);
        $stmtLastSun->execute();

        $itemCount = $stmt->rowCount();
        if ($itemCount > 0) {
            $tempArr;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rowLastSun = $stmtLastSun->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "valuedate" => $tdate,
                "firstSunshine" => $row['firstSunshine'],
                "firstSunDuration" => $row['firstSunDuration'],
                "lastSunshine" => $rowLastSun['lastSunshine'],
                "lastSunDuration" => $rowLastSun['lastSunDuration'],
            );
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

// ************************ *******************************************************
}
