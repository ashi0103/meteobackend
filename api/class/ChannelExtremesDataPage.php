<?php

include_once('UtilCommon.php');

/**
 * All Channel data those are common
 *
 * @author USER
 */
class ChannelExtremesDataPage {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMinAvailableDates($channelId) {
        $tablechannelmain = 'channel_' . $channelId;
        $sqlQuery = "select min(datatime) mindate from $tablechannelmain where  channelid =:channelid";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":channelid", $channelId);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr;
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "minimumdate" => $row['mindate']
            );
        }
//             echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
        return $tempArr;
    }

    function calcperciextremeHr($criteria, $sdate, $edate) {

        if ($sdate == $edate) {
            $criteriaval = $criteria . ' hr';
            $sqlQueryMax = " select CAST(DATE_FORMAT(max(runtime) , '%Y-%m-%d %H') as datetime)   as vlastrun , 
                            CAST(DATE_FORMAT(now() , '%Y-%m-%d %H') as datetime)  as vtimenow 
                            from extremevalues   
                            where criteria = :criteriaval ";
            $stmtMax = $this->conn->prepare($sqlQueryMax);
            $stmtMax->bindParam(':criteriaval', $criteriaval);
            $stmtMax->execute();
            $rowMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $sdate = $rowMax['vlastrun'];
            $edate = $rowMax['vtimenow'];
        }

        $channelname = 'precipitation';
        $stmt = $this->conn->prepare("CALL calculateExtremeHrValues(:channelid, :criteria, :sdate,:edate)");
        $stmt->bindParam(':channelid', $channelname);
        $stmt->bindParam(':criteria', $criteria);
        $stmt->bindParam(':sdate', $sdate);
        $stmt->bindParam(':edate', $edate);
        $stmt->execute();
        echo(" Success " . $criteria . ' sdate ' . $sdate . ' edate ' . $edate);
    }

    function calcperciextremeDay($criteria, $sdate, $edate) {

        if ($sdate == $edate) {
            $criteriaval = $criteria . ' Day';
            $sqlQueryMax = " select CAST(DATE_FORMAT(max(runtime) , '%Y-%m-%d %H') as datetime)   as vlastrun , 
                            CAST(DATE_FORMAT(now() , '%Y-%m-%d %H') as datetime)  as vtimenow 
                            from extremevalues   
                            where criteria = :criteriaval ";
            $stmtMax = $this->conn->prepare($sqlQueryMax);
            $stmtMax->bindParam(':criteriaval', $criteriaval);
            $stmtMax->execute();
            $rowMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $sdate = $rowMax['vlastrun'];
            $edate = $rowMax['vtimenow'];
        }

        $channelname = 'precipitation';
        $stmt = $this->conn->prepare("CALL calculateExtremeDayValues(:channelid, :criteria, :sdate,:edate)");
        $stmt->bindParam(':channelid', $channelname);
        $stmt->bindParam(':criteria', $criteria);
        $stmt->bindParam(':sdate', $sdate);
        $stmt->bindParam(':edate', $edate);
        $stmt->execute();
        echo(" Success " . $criteria . ' sdate ' . $sdate . ' edate ' . $edate);
    }

    private function getPeriodPerci($period) {
        $rangeOptions = array(
            'YR' => 'YR', // Year Rain
            'SR' => 'SR', //Seasonal Rain
            'MR' => 'MR', //Monthly Rain
            'D10R' => '9 day', //10 Day Rain
            'D5R' => '4 day', // 5 Day Rain
            'D3R' => '2 day', // 3 Day Rain
            'D2R' => '1 day', // 2 Day Rain
            'D1R' => '0 day', // Day Rain
            'HR72R' => '72 Hour', //72 hr Rain
            'HR48R' => '48 Hour', // 48 hr Rain
            'HR24R' => '24 Hour', // 24 hr Rain
            'HR12R' => '12 Hour', // 12 hr Rain
            'HR6R' => '6 Hour', // 6 Hr Rain                    
            'HR1R' => '1 Hour', //  full: "1 Hr Rain",                   
            'MIN10R' => '10 Minute', //  full: "10 Min Rain",
            'IR' => 'IR' //  RainIntensity
        );

        $range = $rangeOptions[$period];

        return $range;
    }

    private function getDataStartEndDatePerci($data, $param5, $param6) {

        $startEndDateArr = array();
        $seasionList = ['Winter', 'Spring', 'Summer', 'Autumn'];
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $years;
        $custom = 'C';

        if ($data == 'Y') {
            //Year
        } else if ($data == 'S') {
            //Season            
            if ($param5 == 'Winter') {
                $start = date('12-01'); // hard-coded '01' for first day
                $end = date('02-t');
            } else if ($param5 == 'Spring') {
                $start = date('03-01'); // hard-coded '01' for first day
                $end = date('05-31');
            } else if ($param5 == 'Summer') {
                $start = date('06-01'); // hard-coded '01' for first day
                $end = date('08-31');
            } else if ($param5 == 'Autumn') {
                $start = date('09-01'); // hard-coded '01' for first day
                $end = date('11-30');
            }
        } else if ($data == 'M') {
            //Month
        } else if ($data == 'C') {
            //Custom
        } else if ($data == 'ALL') {
            //All Data
        }
    }

    public function getPercidayRain($period, $minmax, $data, $param5, $param6) {

        $OrderBy = ' desc '; // MAX
        if ($minmax == 'MIN') {
            $OrderBy = ' asc ';
        }
        $range = $this->getPeriodPerci($period);

        $perciStartEndDate = $this->getDataStartEndDatePerci($data, $param5, $param6);

        $sqlQuery = " select  date_add(t.Date, interval -$range ) as fromdt, t.Date as todate ,
            (select sum(t1.value) as sumvalue from 
            (select CAST(sum(channelv.value) AS DECIMAL(10,3) ) as value 
                    from channel_0101_values channelv
                       where channelv.channelid ='0101' and 
                            date(channelv.valuedate)  between 
                      date_add(t.Date, interval -$range) and t.Date
                             group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') ) t1
                     )   as sumvalue
            from yeardays t where t.Date 
            between  '2021-01-07' and '2021-07-31' order by sumvalue desc 
            limit 1 ; ";

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
                    'rad' => $row['humidity']
                );
            }
        }
        return $tempArr;
    }

    public function getPerciHrRain($period, $minmax, $data) {

        $perciPeriod = $this->getPeriodPerci($period);
        $perciPeriod = $this->getDataStartEndDatePerci($period);

        $sqlQuery = " select  date_add(t.Date, interval -1 day) as fromdt, t.Date as todate ,
            (select sum(t1.value) as sumvalue from 
            (select CAST(sum(channelv.value) AS DECIMAL(10,3) ) as value 
                    from channel_0101_values channelv
                       where channelv.channelid ='0101' and 
                            date(channelv.valuedate)  between 
                      date_add(t.Date, interval -1 day) and t.Date
                             group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') ) t1
                     )   as sumvalue
            from yeardays t where t.Date 
            between  '2021-01-07' and '2021-07-31' order by sumvalue desc 
            limit 1 ; ";

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
                    'rad' => $row['humidity']
                );
            }
        }
        return $tempArr;
    }

    public function gettempYminmaxAll($minmax, $avgOrAbs) {

        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value , valueyear from 
                        (select  $absClause(value) value, year(valuedate) valueyear
                                        from channel_0130_values 
                                        group by date(valuedate))t
                        group by valueyear ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['value'],
                    'valuelabel' => $row['valueyear']
                );
            }
        }
        return $tempArr;
    }

    public function gettempYminmaxCustom($minmax, $avgOrAbs, $yearlist) {


        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value , valueyear from 
                        (select  $absClause(value) value, year(valuedate) valueyear
                                        from channel_0130_values 
                                        group by date(valuedate))t where valueyear in ($yearlist)
                        group by valueyear  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['value'],
                    'valuelabel' => $row['valueyear']
                );
            }
        }
        return $tempArr;
    }

    public function getSeasonStartEnd() {

        $winterStart = '12-01';
        $winterEnd = '02-28';
        $springStart = '03-01';
        $springEnd = '05-31';
        $summerStart = '06-01';
        $summerEnd = '08-31';
        $autumnStart = '09-01';
        $autumnEnd = '11-30';

        $currentyear = date("Y");
        $last10year = $currentyear - 10;

        $seasonsRange = Array();
        for ($t = $last10year; $t <= $currentyear; $t++) {
            $febdate = 28;
            if ($t % 400 == 0 || $t % 4 == 0) {
                $febdate = 29;
            }
            $seasonsRange[$t] = array(
                'year' => $t,
                'winterStart' => ($t - 1) . '-' . $winterStart,
                'winterEnd' => ($t) . '-' . '02-' . $febdate,
                'springStart' => ($t) . '-' . $springStart,
                'springEnd' => ($t) . '-' . $springEnd,
                'summerStart' => ($t) . '-' . $summerStart,
                'summerEnd' => ($t) . '-' . $summerEnd,
                'autumnStart' => ($t) . '-' . $autumnStart,
                'autumnEnd' => ($t) . '-' . $autumnEnd
            );
        }

        return $seasonsRange;
    }

    public function gettempSTminmaxAll($minmax, $avgOrAbs) {

        $seasonsRange = $this->getSeasonStartEnd();
        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        } else {
            $absClause = $minmax;
        }

        $currentyear = date("Y");
        $last10year = $currentyear - 10;

        $seasondataArr = Array();
        for ($t = $last10year; $t <= $currentyear; $t++) {

            $seasonvalues = $seasonsRange[$t];
            $winterStart = $seasonvalues['winterStart'];
            $winterEnd = $seasonvalues['winterEnd'];
            $springStart = $seasonvalues['springStart'];
            $springEnd = $seasonvalues['springEnd'];
            $summerStart = $seasonvalues['summerStart'];
            $summerEnd = $seasonvalues['summerEnd'];
            $autumnStart = $seasonvalues['autumnStart'];
            $autumnEnd = $seasonvalues['autumnEnd'];

            $clauseVal = $winterStart . $t;
            $sqlQuery = "  select CAST(($absClause(val)) AS DECIMAL(10,2)) val  from 
                        (select  $absClause(value) val, $clauseVal as grpvalue,
                        year(valuedate) valuedate
                                              from channel_0130_values 
                                              where date(valuedate) between :start and :end
                                              group by date(valuedate)) t
                         group by (grpvalue)  ";

            $stmtWinter = $this->conn->prepare($sqlQuery);
            $stmtWinter->bindParam(':start', $winterStart);
            $stmtWinter->bindParam(':end', $winterEnd);
            $stmtWinter->execute();
            $rowWinter = $stmtWinter->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $springStart . $t;
            $stmtSpring = $this->conn->prepare($sqlQuery);
            $stmtSpring->bindParam(':start', $springStart);
            $stmtSpring->bindParam(':end', $springEnd);
            $stmtSpring->execute();
            $rowSpring = $stmtSpring->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $summerStart . $t;
            $stmtSummer = $this->conn->prepare($sqlQuery);
            $stmtSummer->bindParam(':start', $summerStart);
            $stmtSummer->bindParam(':end', $summerEnd);
            $stmtSummer->execute();
            $rowSummer = $stmtSummer->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $autumnStart . $t;
//            print_r($autumnStart);
//            print_r($autumnEnd); 
//            print_r($absClause);
            $stmtAutumn = $this->conn->prepare($sqlQuery);
            $stmtAutumn->bindParam(':start', $autumnStart);
            $stmtAutumn->bindParam(':end', $autumnEnd);
            $stmtAutumn->execute();
            $rowAutumn = $stmtAutumn->fetch(PDO::FETCH_ASSOC);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowWinter['val'] ?? null,
                'valuelabel' => 'Winter'
            );
            array_push($seasondataArr, $tempdata);
            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowSpring['val'] ?? null,
                'valuelabel' => 'Spring'
            );
            array_push($seasondataArr, $tempdata);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowSummer['val'] ?? null,
                'valuelabel' => 'Summer'
            );
            array_push($seasondataArr, $tempdata);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowAutumn['val'] ?? null,
                'valuelabel' => 'Autumn'
            );
            array_push($seasondataArr, $tempdata);
        }

        return $seasondataArr;
    }

    public function gettempMTminmaxAll($minmax, $avgOrAbs) {

        $seasonsRange = $this->getSeasonStartEnd();
        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        } else {
            $absClause = $minmax;
        }

        $sqlQuery = " select  CAST(($absClause(value)) AS DECIMAL(10,2))  val , DATE_FORMAT(valuedate, '%Y')  valuelabel , 
                        DATE_FORMAT(valuedate, '%b')  valuedate
                        from channel_0130_values 
                        group by Month(valuedate) ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['val'],
                    'valuelabel' => $row['valuelabel'],
                    'valuedate' => $row['valuedate']
                );
            }
        }
        return $tempArr;
    }

    public function gettempDTminmaxAll($minmax, $inputDay, $selectdata, $valuelist) {

        $resultArr = Array();
        $minmaxclause = 'max'; // MAX
        if ($minmax == 'MIN') {
            $minmaxclause = 'min';
        }
        $inputDate = date('Y-m-d');
        $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
        $end_date = $inputDate;
        if ($selectdata == 'ALL') {
            $inputDate = date('Y-m-d');
            $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calctempDTresult($date, $end_date, $inputDay, $minmaxclause);
        } else if ($selectdata == 'Y') {
            $year = $valuelist; // Selected year is less than current year
            $inputDate = date('Y-m-d');
            if ($year < $inputDate) {
                $yearval = $year . '-12-31';
                $inputDate = date($yearval);
            }
            $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calctempDTresult($date, $end_date, $inputDay, $minmaxclause);
        } else if ($selectdata == 'S') {
            $seasonstart = strtolower($valuelist) . 'Start'; // Selected season 
            $seasonend = strtolower($valuelist) . 'End';
            $seasonsRange = $this->getSeasonStartEnd();
            $currentyear = date("Y");
            $last10year = $currentyear - 5;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $seasonrangevalue = $seasonsRange[$t];
                $date = $seasonrangevalue[$seasonstart];
                $end_date = $seasonrangevalue[$seasonend];
                $calcresult = $this->calctempDTresult($date, $end_date, $inputDay, $minmaxclause);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'M') {
            $monthlist = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectmonth = $valuelist; // Selected month                         
            $key = (array_search($selectmonth, $monthlist)) + 1;
            $monthvalue = $key;
            if ($key < 10) {
                $monthvalue = '0' . $key;
            }
            $currentyear = date("Y");
            $last10year = $currentyear - 10;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $startdate = $t . '-' . $monthvalue . '-' . '01';
                $enddate = $t . '-' . $monthvalue . '-' . 't';
                $date = date($startdate);
                $end_date = date($enddate);
                $calcresult = $this->calctempDTresult($date, $end_date, $inputDay, $minmaxclause);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'C') {
            $datelist = explode(",", $valuelist);
            $date = date($datelist[0]);
            $end_date = date($datelist[1]);
            $resultArr = $this->calctempDTresult($date, $end_date, $inputDay, $minmaxclause);
        }


        if ($minmax == 'MIN') {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] < $b['value']) ? -1 : 1;
            });
        } else {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] > $b['value']) ? -1 : 1;
            });
        }

        return $resultArr;
//        echo "<pre>";
//        print_r($resultArr);
//        echo "<post>";
    }

    public function calctempDTresult($date, $end_date, $inputDay, $minmaxclause) {

        $resultArr = Array();
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->calctempDTminmaxAll($date, $inputDay, $minmaxclause);
            if ($tempArr['value'] != null) {
                array_push($resultArr, $tempArr);
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }

        return $resultArr;
    }

    public function calctempDTminmaxAll($inputDate, $inputDay, $minmaxclause) {

        $sqlQuery = '';
        if ($inputDay != 1) {
            $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value, 
                        concat( DATE_FORMAT(date_add(:indate, interval -:inday day), '%d-%b-%Y') , ' :: ',  DATE_FORMAT(:indate, '%d-%b-%Y')) valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        AVG(channelv.value) value from channel_0130_values channelv
                        where channelv.channelid ='0130' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') order by channelv.valuedate desc
                        ) t ";
        } else {
            $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value, 
                        DATE_FORMAT(:indate, '%d-%b-%Y')  valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        AVG(channelv.value) value from channel_0130_values channelv
                        where channelv.channelid ='0130' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') order by channelv.valuedate desc
                        ) t ";
        }


        $finalinputDay = $inputDay - 1;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->bindParam(":inday", $finalinputDay);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "value" => $row['value'],
                "valuelabel" => $row['valuelabel']
            );
        }
        return $tempArr;
    }

    public function gettempDTAminmaxAll($minmax, $inputDay, $selectdata, $valuelist) {

        $resultArr = Array();
        $minmaxclause = 'max'; // MAX
        if ($minmax == 'MIN') {
            $minmaxclause = 'min';
        }
        $inputDate = date('Y-m-d');
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        $end_date = $inputDate;
        if ($selectdata == 'ALL') {
            $inputDate = date('Y-m-d');
            $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calctempDTAresult($date, $end_date, $inputDay, $minmaxclause);
        } else if ($selectdata == 'Y') {
            $year = $valuelist; // Selected year is less than current year
            $inputDate = date('Y-m-d');
            if ($year < $inputDate) {
                $yearval = $year . '-12-31';
                $inputDate = date($yearval);
            }
            $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calctempDTAresult($date, $end_date, $inputDay, $minmaxclause);
        } else if ($selectdata == 'S') {
            $seasonstart = strtolower($valuelist) . 'Start'; // Selected season 
            $seasonend = strtolower($valuelist) . 'End';
            $seasonsRange = $this->getSeasonStartEnd();
            $currentyear = date("Y");
            $last10year = $currentyear - 5;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $seasonrangevalue = $seasonsRange[$t];
                $date = $seasonrangevalue[$seasonstart];
                $end_date = $seasonrangevalue[$seasonend];
                $calcresult = $this->calctempDTAresult($date, $end_date, $inputDay, $minmaxclause);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'M') {
            $monthlist = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectmonth = $valuelist; // Selected month                         
            $key = (array_search($selectmonth, $monthlist)) + 1;
            $monthvalue = $key;
            if ($key < 10) {
                $monthvalue = '0' . $key;
            }
            $currentyear = date("Y");
            $last10year = $currentyear - 10;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $startdate = $t . '-' . $monthvalue . '-' . '01';
                $enddate = $t . '-' . $monthvalue . '-' . 't';
                $date = date($startdate);
                $end_date = date($enddate);
                $calcresult = $this->calctempDTAresult($date, $end_date, $inputDay, $minmaxclause);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'C') {
            $datelist = explode(",", $valuelist);
            $date = date($datelist[0]);
            $end_date = date($datelist[1]);
            $resultArr = $this->calctempDTAresult($date, $end_date, $inputDay, $minmaxclause);
        }


        if ($minmax == 'MIN') {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] < $b['value']) ? -1 : 1;
            });
        } else {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] > $b['value']) ? -1 : 1;
            });
        }

        return $resultArr;
//        echo "<pre>";
//        print_r($resultArr);
//        echo "<post>";
    }

    public function calctempDTAresult($date, $end_date, $inputDay, $minmaxclause) {

        $resultArr = Array();
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->calctempDTAminmaxAll($date, $inputDay, $minmaxclause);
            if ($tempArr['value'] != null) {
                array_push($resultArr, $tempArr);
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }

        return $resultArr;
    }

    public function calctempDTAminmaxAll($inputDate, $inputDay, $minmaxclause) {


        $sqlQuery = '';
        if ($inputDay != 1) {
            $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value, 
                        concat( DATE_FORMAT(date_add(:indate, interval -:inday day), '%d-%b-%Y') , ' :: ',DATE_FORMAT(:indate, '%d-%b-%Y') ) valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        $minmaxclause(channelv.value) value from channel_0130_values channelv
                        where channelv.channelid ='0130' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') order by channelv.valuedate desc
                        ) t ";
        } else {
            $sqlQuery = " select CAST(avg(value) AS DECIMAL(10,2) ) value, 
                        DATE_FORMAT(:indate, '%d-%b-%Y')  valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        $minmaxclause(channelv.value) value from channel_0130_values channelv
                        where channelv.channelid ='0130' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') order by channelv.valuedate desc
                        ) t ";
        }

        $finalinputDay = $inputDay - 1;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->bindParam(":inday", $finalinputDay);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "value" => $row['value'],
                "valuelabel" => $row['valuelabel']
            );
        }
        return $tempArr;
    }

// ************************ *******************************************************
//************* Start Perci *******************************    

    public function getperciYminmaxAll($minmax, $avgOrAbs) {

        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $sqlQuery = " select CAST(sum(value) AS DECIMAL(10,2) ) value , valueyear from 
                        (select  sum(value) value, year(valuedate) valueyear
                                        from channel_0103_values 
                                        group by date(valuedate))t
                        group by valueyear ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['value'],
                    'valuelabel' => $row['valueyear']
                );
            }
        }
        return $tempArr;
    }

    public function getperciYminmaxCustom($minmax, $avgOrAbs, $yearlist) {


        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $sqlQuery = " select CAST(sum(value) AS DECIMAL(10,2) ) value , valueyear from 
                        (select  sum(value) value, year(valuedate) valueyear
                                        from channel_0103_values 
                                        group by date(valuedate))t where valueyear in ($yearlist)
                        group by valueyear  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['value'],
                    'valuelabel' => $row['valueyear']
                );
            }
        }
        return $tempArr;
    }

    public function getperciSTminmaxAll($minmax, $avgOrAbs) {

        $seasonsRange = $this->getSeasonStartEnd();
        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $currentyear = date("Y");
        $last10year = $currentyear - 10;

        $seasondataArr = Array();
        for ($t = $last10year; $t <= $currentyear; $t++) {

            $seasonvalues = $seasonsRange[$t];
            $winterStart = $seasonvalues['winterStart'];
            $winterEnd = $seasonvalues['winterEnd'];
            $springStart = $seasonvalues['springStart'];
            $springEnd = $seasonvalues['springEnd'];
            $summerStart = $seasonvalues['summerStart'];
            $summerEnd = $seasonvalues['summerEnd'];
            $autumnStart = $seasonvalues['autumnStart'];
            $autumnEnd = $seasonvalues['autumnEnd'];

            $clauseVal = $winterStart . $t;
            $sqlQuery = "  select CAST((sum(val)) AS DECIMAL(10,2)) val  from 
                        (select  sum(value) val, $clauseVal as grpvalue,
                        year(valuedate) valuedate
                                              from channel_0103_values 
                                              where date(valuedate) between :start and :end
                                              group by date(valuedate)) t
                         group by (grpvalue)  ";

            $stmtWinter = $this->conn->prepare($sqlQuery);
            $stmtWinter->bindParam(':start', $winterStart);
            $stmtWinter->bindParam(':end', $winterEnd);
            $stmtWinter->execute();
            $rowWinter = $stmtWinter->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $springStart . $t;
            $stmtSpring = $this->conn->prepare($sqlQuery);
            $stmtSpring->bindParam(':start', $springStart);
            $stmtSpring->bindParam(':end', $springEnd);
            $stmtSpring->execute();
            $rowSpring = $stmtSpring->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $summerStart . $t;
            $stmtSummer = $this->conn->prepare($sqlQuery);
            $stmtSummer->bindParam(':start', $summerStart);
            $stmtSummer->bindParam(':end', $summerEnd);
            $stmtSummer->execute();
            $rowSummer = $stmtSummer->fetch(PDO::FETCH_ASSOC);

            $clauseVal = $autumnStart . $t;
//            print_r($autumnStart);
//            print_r($autumnEnd); 
//            print_r($absClause);
            $stmtAutumn = $this->conn->prepare($sqlQuery);
            $stmtAutumn->bindParam(':start', $autumnStart);
            $stmtAutumn->bindParam(':end', $autumnEnd);
            $stmtAutumn->execute();
            $rowAutumn = $stmtAutumn->fetch(PDO::FETCH_ASSOC);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowWinter['val'] ?? null,
                'valuelabel' => 'Winter'
            );
            array_push($seasondataArr, $tempdata);
            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowSpring['val'] ?? null,
                'valuelabel' => 'Spring'
            );
            array_push($seasondataArr, $tempdata);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowSummer['val'] ?? null,
                'valuelabel' => 'Summer'
            );
            array_push($seasondataArr, $tempdata);

            $tempdata = array(
                'valuedate' => $t,
                'value' => $rowAutumn['val'] ?? null,
                'valuelabel' => 'Autumn'
            );
            array_push($seasondataArr, $tempdata);
        }

        return $seasondataArr;
    }

    public function getperciMTminmaxAll($minmax, $avgOrAbs) {

        $seasonsRange = $this->getSeasonStartEnd();
        $absClause = 'max'; // MAX
        if ($avgOrAbs == 'Avg') {
            $absClause = 'Avg';
        }

        $sqlQuery = " select  CAST((sum(value)) AS DECIMAL(10,2))  val , DATE_FORMAT(valuedate, '%Y')  valuelabel , 
                        DATE_FORMAT(valuedate, '%b')  valuedate
                        from channel_0103_values 
                        group by Month(valuedate) ,YEAR(valuedate)  ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['val'],
                    'valuelabel' => $row['valuelabel'],
                    'valuedate' => $row['valuedate']
                );
            }
        }
        return $tempArr;
    }

    public function getperciDTminmaxAll($minmax, $inputDay, $selectdata, $valuelist) {

        $resultArr = Array();
        $minmaxclause = 'max'; // MAX
        if ($minmax == 'MIN') {
            $minmaxclause = 'min';
        }
        $inputDate = date('Y-m-d');
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        $end_date = $inputDate;
        if ($selectdata == 'ALL') {
            $inputDate = date('Y-m-d');
            $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calcperciDTresult($date, $end_date, $inputDay);
        } else if ($selectdata == 'Y') {
            $year = $valuelist; // Selected year is less than current year
            $inputDate = date('Y-m-d');
            $inDate = date('Y');
            if ($year < $inDate) {
                $yearval = $year . '-12-31';
                $inputDate = date($yearval);
            }
            $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
            $end_date = $inputDate;
            $resultArr = $this->calcperciDTresult($date, $end_date, $inputDay);
        } else if ($selectdata == 'S') {
            $seasonstart = strtolower($valuelist) . 'Start'; // Selected season 
            $seasonend = strtolower($valuelist) . 'End';
            $seasonsRange = $this->getSeasonStartEnd();
            $currentyear = date("Y");
            $last10year = $currentyear - 5;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $seasonrangevalue = $seasonsRange[$t];
                $date = $seasonrangevalue[$seasonstart];
                $end_date = $seasonrangevalue[$seasonend];
                $calcresult = $this->calcperciDTresult($date, $end_date, $inputDay);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'M') {
            $monthlist = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectmonth = $valuelist; // Selected month             
            $key = (array_search($selectmonth, $monthlist)) + 1;
            $monthvalue = $key;
            if ($key < 10) {
                $monthvalue = '0' . $key;
            }
            $currentyear = date("Y");
            $last10year = $currentyear - 10;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $startdate = $t . '-' . $monthvalue . '-' . '01';
                $enddate = $t . '-' . $monthvalue . '-' . 't';
                $date = date($startdate);
                $end_date = date($enddate);
                $calcresult = $this->calcperciDTresult($date, $end_date, $inputDay);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'C') {
            $datelist = explode(",", $valuelist);
            $date = date($datelist[0]);
            $end_date = date($datelist[1]);
            $resultArr = $this->calcperciDTresult($date, $end_date, $inputDay);
        }


        if ($minmax == 'MIN') {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] < $b['value']) ? -1 : 1;
            });
        } else {
            usort($resultArr, function ($a, $b) {
                return ($a['value'] > $b['value']) ? -1 : 1;
            });
        }

        return $resultArr;
//        echo "<pre>";
//        print_r($resultArr);
//        echo "<post>";
    }

    public function calcperciDTresult($date, $end_date, $inputDay) {
           
        
        $resultArr = Array();
        while (strtotime($date) <= strtotime($end_date)) {
            $tempArr = $this->calcperciDTminmaxAll($date, $inputDay);
            if ($tempArr['value'] != null) {
                array_push($resultArr, $tempArr);
            }
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }

        return $resultArr;
    }

    public function calcperciDTminmaxAll($inputDate, $inputDay) {

//        echo'<pre>';
//        print_r($inputDate);
//        echo'<post>';
//        echo'<pre>';
//        print_r($inputDay);
//        echo'<post>';
        $sqlQuery = '';
        if ($inputDay != 1) {
            $sqlQuery = " select CAST(sum(value) AS DECIMAL(10,2) ) value, 
                        concat( DATE_FORMAT(date_add(:indate, interval -:inday day), '%d-%b-%Y')  , ' :: ',DATE_FORMAT(:indate, '%d-%b-%Y') ) valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        sum(channelv.value) value from channel_0103_values channelv
                        where channelv.channelid ='0103' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  date(channelv.valuedate) order by channelv.valuedate desc
                        ) t ";
        } else {
            $sqlQuery = " select CAST(sum(value) AS DECIMAL(10,2) ) value, 
                        DATE_FORMAT(:indate, '%d-%b-%Y')  valuelabel  
                        from 
                        (select DATE_FORMAT(channelv.valuedate, '%d-%m-%Y') valuedate ,
                        sum(channelv.value) value from channel_0103_values channelv
                        where channelv.channelid ='0103' and 
                        date(channelv.datatime) >= 
                        date_add(:indate, interval -:inday day) and date(channelv.datatime) <= :indate
                        group by  date(channelv.valuedate) order by channelv.valuedate desc
                        ) t ";
        }


        $finalinputDay = $inputDay - 1;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->bindParam(":inday", $finalinputDay);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $tempArr = array();
        if ($itemCount > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tempArr = array(
                "value" => $row['value'],
                "valuelabel" => $row['valuelabel']
            );
        }


        return $tempArr;
    }

    public function getperciHRSminmaxAll($minmax, $inputDay, $selectdata, $valuelist) {

        $resultArr = Array();
        $minmaxclause = 'max'; // MAX
        if ($minmax == 'MIN') {
            $minmaxclause = 'min';
        }
        $inputDate = date('Y-m-d');
        $date = date('Y-m-d', strtotime('-365 days', strtotime($inputDate)));
        $end_date = $inputDate;
        if ($selectdata == 'ALL') {
            // Taking start data as input date : todays date and time ( ALL use case) : 2021-11-06
            $inputDate = date('Y-m-d');
            //$inputDate = '2021-11-06 18:05:00';
            //Taking days for last one year days : ex -5  - 2021-11-06 12:05:00 - 5 days = 2021-11-01 12:05:00
            $start_date = date('Y-m-d H:i:s', strtotime('-730 days', strtotime($inputDate))); //2021-11-01 12:05:00
            if ($start_date < '2021-01-01') {
                $start_date = '2021-01-01';
            }
            $end_date = $inputDate;  //2021-11-06 12:05:00             
            // 2021-11-01 12:05:00 --  2021-11-06 12:05:00
            $resultArr = $this->calcperciHRSresult($start_date, $end_date, $inputDay, $minmax);
        } else if ($selectdata == 'Y') {
            $year = $valuelist; // Selected year is less than current year
            $inputDate = date('Y-m-d');
            if ($year < $inputDate) {
                $yearval = $year . '-12-31';
                $inputDate = date($yearval);
            }
            $date = date('Y-m-d', strtotime('-730 days', strtotime($inputDate)));
            if ($date < '2021-01-01') {
                $start_date = '2021-01-01';
            }
            $end_date = $inputDate;
            $resultArr = $this->calcperciHRSresult($date, $end_date, $inputDay, $minmax);
        } else if ($selectdata == 'S') {
            $seasonstart = strtolower($valuelist) . 'Start'; // Selected season 
            $seasonend = strtolower($valuelist) . 'End';
            $seasonsRange = $this->getSeasonStartEnd();
            $currentyear = date("Y");
            $last10year = $currentyear - 5;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $seasonrangevalue = $seasonsRange[$t];
                $date = $seasonrangevalue[$seasonstart];
                $end_date = $seasonrangevalue[$seasonend];
                $calcresult = $this->calcperciHRSresult($date, $end_date, $inputDay, $minmax);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'M') {
            $monthlist = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectmonth = $valuelist; // Selected month                         
            $key = (array_search($selectmonth, $monthlist)) + 1;
            $monthvalue = $key;
            if ($key < 10) {
                $monthvalue = '0' . $key;
            }
            $currentyear = date("Y");
            $last10year = $currentyear - 10;
            for ($t = $last10year; $t <= $currentyear; $t++) {
                $startdate = $t . '-' . $monthvalue . '-' . '01';
                $enddate = $t . '-' . $monthvalue . '-' . 't';
                $date = date($startdate);
                $end_date = date($enddate);
                $calcresult = $this->calcperciHRSresult($date, $end_date, $inputDay, $minmax);
                if ($calcresult != null) {
                    $resultArr = array_merge($resultArr, $calcresult);
                }
            }
        } else if ($selectdata == 'C') {
            $datelist = explode(",", $valuelist);
            $date = date($datelist[0]);
            $end_date = date($datelist[1]);
            $resultArr = $this->calcperciHRSresult($date, $end_date, $inputDay, $minmax);
        }


//            echo "<pre>";
//            print_r($resultArr);
//            echo "<post>";
//        if ($minmax == 'MIN') {
//            usort($resultArr, function ($a, $b) {
//                return ($a['value'] < $b['value']) ? -1 : 1;
//            });
//        } else {
//            usort($resultArr, function ($a, $b) {
//                return ($a['value'] > $b['value']) ? -1 : 1;
//            });
//        }
//            echo "<pre>";
//            print_r($resultArr);
//            echo "<post>";
        return $resultArr;
    }

    //AANAYA
    //AKSHITA  
    public function calcperciHRSresult($start_date, $end_date, $inputDay, $minmax) {

        //   2021-11-01 12:05:00 --  2021-11-06 12:05:00
        $resultArr = Array();
        while (strtotime($start_date) <= strtotime($end_date)) {
            $tempArr = $this->calcperciHRSminmaxAll($start_date, $inputDay);
            $resultArr = array_merge($resultArr, $tempArr);
            $start_date = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($start_date)));
        }

        // Slicing hourly 
        $n = 1;
        $slicestart = 1;
        $slicestep = ($inputDay * 6);
        for ($t = 0; $t < count($resultArr); $t++) {
            $resulttoSlice = $resultArr;
            if (count($resulttoSlice) > $slicestep) {
                $slicedArr = array_slice($resulttoSlice, $t, $slicestep);
            } else {
                $slicedArr = array_slice($resulttoSlice, $t);
            }

            $startDateTime = $slicedArr[0];
            $endDateTime = $slicedArr[count($slicedArr) - 1];
            $arrSum = array_sum(array_column($slicedArr, 'value'));

            $mainresultArr[] = array(
                "valuelabel" => $startDateTime['time10mins'] . ' : ' . $endDateTime['time10mins'],
                "value" => $arrSum
            );

            //find minimum and maximum values for given slice

            if ($t == ($slicestep * $n)) {
                if ($t == $slicestep) {
                    $slicestart = 0;
                    $sliceend = $slicestep;
                }

                $slicedminmaxArr = array_slice($mainresultArr, $slicestart, $sliceend);

//                echo "** <pre>";
//                print_r($slicedminmaxArr);                                          
//                echo "<post> ***";

                usort($slicedminmaxArr, function ($a, $b) {
                    return ($a['value'] < $b['value']) ? -1 : 1;
                });
                $minval = $slicedminmaxArr[0];
                $maxval = $slicedminmaxArr[count($slicedminmaxArr) - 1];

                if ($minmax == 'MIN') {
                    $finalresultArr[] = array(
                        "valuelabel" => $minval['valuelabel'],
                        "value" => $minval['value']
                    );
                } else {
                    $finalresultArr[] = array(
                        "valuelabel" => $maxval['valuelabel'],
                        "value" => $maxval['value']
                    );
                }

                $slicestart = $t;
                $sliceend = $slicestep;
                $n = $n + 1;
            }
        }

//        echo "<pre>";
//        print_r($finalresultArr);
//        echo "<post>";
        return $finalresultArr;
    }

    public function calcperciHRSminmaxAll($inputDate) {


        $sqlQuery = " select DATE_FORMAT(time10mins, '%d-%m-%Y %H:%i') time10mins , CAST(ifnull(value,0) AS DECIMAL(10,2)) value from 
                            (select date_add(`alldate`.`mydate`,interval 5 minute) AS `time10mins` 
                            from (with recursive `seq` as (select 0 AS `value` union all select (`seq`.`value` + 1)
                             AS `value + 1` from `seq` where (`seq`.`value` < 143)) 
                             select (cast(:indate as date) + interval (`parameter`.`value` * 10) minute) AS `mydate`
                             from `seq` `parameter` order by `parameter`.`value`)  `alldate`)t
                             left join 
                             (select value,valuedate  from channel_0103_values where channelid ='0103'
                                    and date(valuedate)=:indate
                             ) t1
                             on t.time10mins= t1.valuedate
                             order by t.time10mins ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(":indate", $inputDate);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'value' => $row['value'],
                    'time10mins' => $row['time10mins']
                );
            }
        }
        return $tempArr;
    }

    // Method for All and selected year
    public function getperciRIminmaxAllAndY($minmax, $inputvalue) {

        $sqlQuery = '';
        if ($inputvalue == 'ALL') {
            $sqlQuery = " select  date(valuedate) valueyear, max(value) value
			from channel_0100_values 
                        group by date(valuedate) ";
        } else {
            $sqlQuery = " select valueyear,value from (                        
                        select  date(valuedate) valueyear,  CAST(max(value) AS DECIMAL(10,2) )  value
                                                from channel_0100_values 
                                    group by date(valuedate))t 
                        where YEAR(t.valueyear) =$inputvalue   ";
        }

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $itemCount = $stmt->rowCount();
        $i = 0;
        $tempArr = null;
        if ($itemCount > 0) {
            $tempArr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i = $i + 1;
                $tempArr[] = array(
                    'value' => $row['value'],
                    'valuelabel' => $row['valueyear']
                );
            }
        }
        return $tempArr;
    }

//    public function getperciRIminmaxAllAndS($minmax, $inputvalue) {
//
//        $sqlQuery = '';
//        if ($inputvalue == 'ALL') {
//            $sqlQuery = " select  year(valuedate) valueyear, max(value) value
//			from channel_0100_values 
//                        group by year(valuedate) ";
//        } else {
//            $sqlQuery = " select valueyear,value from (                        
//                        select  year(valuedate) valueyear, CAST(max(value) AS DECIMAL(10,2) )  value
//                                                from channel_0100_values 
//                                    group by year(valuedate))t 
//                        where t.valueyear =$inputvalue   ";
//        }
//
//        $stmt = $this->conn->prepare($sqlQuery);
//        $stmt->execute();
//        $itemCount = $stmt->rowCount();
//        $i = 0;
//        $tempArr = null;
//        if ($itemCount > 0) {
//            $tempArr = array();
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                $i = $i + 1;
//                $tempArr[] = array(
//                    'value' => $row['value'],
//                    'valuelabel' => $row['valueyear']
//                );
//            }
//        }
//        return $tempArr;
//    }
//    public function getperciRIminmaxAllAndM($minmax, $inputvalue) {
//
//        $sqlQuery = '';
//        if ($inputvalue == 'ALL') {
//            $sqlQuery = " select  year(valuedate) valueyear, max(value) value
//			from channel_0100_values 
//                        group by year(valuedate) ";
//        } else {
//            $sqlQuery = " select valueyear,value from (                        
//                        select  year(valuedate) valueyear, CAST(max(value) AS DECIMAL(10,2) )  value
//                                                from channel_0100_values 
//                                    group by year(valuedate))t 
//                        where t.valueyear =$inputvalue   ";
//        }
//
//        $stmt = $this->conn->prepare($sqlQuery);
//        $stmt->execute();
//        $itemCount = $stmt->rowCount();
//        $i = 0;
//        $tempArr = null;
//        if ($itemCount > 0) {
//            $tempArr = array();
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                $i = $i + 1;
//                $tempArr[] = array(
//                    'value' => $row['value'],
//                    'valuelabel' => $row['valueyear']
//                );
//            }
//        }
//        return $tempArr;
//    }

    public function getperciRIminmaxS($inputvalue) {
        $seasonsRange = $this->getSeasonStartEnd();
        $currentyear = date("Y");
        $last10year = $currentyear - 10;

        $seasondataArr = Array();
        for ($t = $last10year; $t <= $currentyear; $t++) {

            $seasonvalues = $seasonsRange[$t];
            $winterStart = $seasonvalues['winterStart'];
            $winterEnd = $seasonvalues['winterEnd'];
            $springStart = $seasonvalues['springStart'];
            $springEnd = $seasonvalues['springEnd'];
            $summerStart = $seasonvalues['summerStart'];
            $summerEnd = $seasonvalues['summerEnd'];
            $autumnStart = $seasonvalues['autumnStart'];
            $autumnEnd = $seasonvalues['autumnEnd'];

            $sqlQuery = " select  CAST(max(value) AS DECIMAL(10,2)) val, date(valuedate) valuedate
                                              from channel_0100_values 
                                              where date(valuedate) between :start and :end
                                              group by date(valuedate) ";

            $stmtWinter = $this->conn->prepare($sqlQuery);
            $stmtWinter->bindParam(':start', $winterStart);
            $stmtWinter->bindParam(':end', $winterEnd);
            $stmtWinter->execute();

            while ($rowWinter = $stmtWinter->fetch(PDO::FETCH_ASSOC)) {
                $tempdata = array(
                    'valuedate' => $rowWinter['valuedate'],
                    'value' => $rowWinter['val'] ?? null,
                    'valuelabel' => 'Winter'
                );
                array_push($seasondataArr, $tempdata);
            }


            $stmtSpring = $this->conn->prepare($sqlQuery);
            $stmtSpring->bindParam(':start', $springStart);
            $stmtSpring->bindParam(':end', $springEnd);
            $stmtSpring->execute();

            while ($rowSpring = $stmtSpring->fetch(PDO::FETCH_ASSOC)) {
                $tempdata = array(
                    'valuedate' => $rowSpring['valuedate'],
                    'value' => $rowSpring['val'] ?? null,
                    'valuelabel' => 'Spring'
                );
                array_push($seasondataArr, $tempdata);
            }


            $stmtSummer = $this->conn->prepare($sqlQuery);
            $stmtSummer->bindParam(':start', $summerStart);
            $stmtSummer->bindParam(':end', $summerEnd);
            $stmtSummer->execute();

            while ($rowSummer = $stmtSummer->fetch(PDO::FETCH_ASSOC)) {
                $tempdata = array(
                    'valuedate' => $rowSummer['valuedate'],
                    'value' => $rowSummer['val'] ?? null,
                    'valuelabel' => 'Summer'
                );
                array_push($seasondataArr, $tempdata);
            }

            $stmtAutumn = $this->conn->prepare($sqlQuery);
            $stmtAutumn->bindParam(':start', $autumnStart);
            $stmtAutumn->bindParam(':end', $autumnEnd);
            $stmtAutumn->execute();

            while ($rowAutumn = $stmtAutumn->fetch(PDO::FETCH_ASSOC)) {
                $tempdata = array(
                    'valuedate' => $rowAutumn['valuedate'],
                    'value' => $rowAutumn['val'] ?? null,
                    'valuelabel' => 'Autumn'
                );
                array_push($seasondataArr, $tempdata);
            }
        }

//        echo "<pre>";
//        print_r($seasondataArr);
//        echo "<post>";
        return $seasondataArr;
    }

    public function getperciRIminmaxM($inputvalue, $valuelist) {
        $monthlist = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $selectmonth = $valuelist; // Selected month                         
        $key = (array_search($selectmonth, $monthlist)) + 1;
        $monthvalue = $key;

        if ($key < 10) {
            $monthvalue = '0' . $key;
        }
        $currentyear = date("Y");
        $last10year = $currentyear - 10;
        $monthdataArr = Array();
        for ($t = $last10year; $t <= $currentyear; $t++) {
            $startdate = $t . '-' . $monthvalue . '-' . '01';
            $enddate = $t . '-' . $monthvalue . '-' . 't';
            $date = date($startdate);
            $end_date = date($enddate);

            $sqlQuery = "  select  CAST(max(value) AS DECIMAL(10,2)) val, date(valuedate) valuedate, Month(valuedate) valuemonth
                                              from channel_0100_values 
                                              where date(valuedate) between :start and :end
                                              group by date(valuedate) ";

            $stmtmonth = $this->conn->prepare($sqlQuery);
            $stmtmonth->bindParam(':start', $date);
            $stmtmonth->bindParam(':end', $end_date);
            $stmtmonth->execute();

            while ($rowmonth = $stmtmonth->fetch(PDO::FETCH_ASSOC)) {
                $tempdata[] = array(
                    'valuedate' => $rowmonth['valuedate'],
                    'value' => $rowmonth['val'] ?? null,
                    'valuelabel' => $rowmonth['valuedate']
                );
                //array_push($monthdataArr, $tempdata);
            }
        }              
        
        return $tempdata;
    }

    // minmax , start and end date
    public function getperciRIminmaxC($inputvalue, $valuelist) {

        $sortingClause = 'desc';
        if ($inputvalue == 'MIN') {
            $sortingClause = 'asc';
        }

        $datelist = explode(",", $valuelist);
        $date = date($datelist[0]);
        $end_date = date($datelist[1]);

        $sqlQuery = "  select  CAST(sum(value) AS DECIMAL(10,2)) val, date(valuedate) valuedate
                                              from channel_0100_values 
                                              where date(valuedate) between :start  and :end
                                              group by date(valuedate) order by val $sortingClause   ";

        $stmtbydate = $this->conn->prepare($sqlQuery);
        $stmtbydate->bindParam(':start', $date);
        $stmtbydate->bindParam(':end', $end_date);
        $stmtbydate->execute();
        $itemCount = $stmtbydate->rowCount();
        if ($itemCount > 0) {
            while ($row = $stmtbydate->fetch(PDO::FETCH_ASSOC)) {
                $tempArr[] = array(
                    'value' => $row['val'] ?? null,
                    'valuelabel' => $row['valuedate']
                );
            }
        }
//
//        echo "<pre>";
//        print_r($tempArr);
//        echo "<post>";

        return $tempArr;
    }

}
