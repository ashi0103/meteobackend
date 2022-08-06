<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


include('../api/config/database.php');
include('../api/class/ChannelDataDashComp.php');
include('../api/class/ChannelDataDashSunComp.php');
include('../api/class/ChannelDataDashWindComp.php');
include('../api/class/ChannelDataDashPressureComp.php');
include_once('../api/class/UtilCommon.php');

/** @var type $requestMethod */
//$requestMethod = $_SERVER["REQUEST_METHOD"];
$pathinfo = $_SERVER['PATH_INFO'];
$called = explode('/', trim($_SERVER['PATH_INFO'], '/'));

$param1; // Get method 
$param2; // Channel id
$param3; //  Date
$param4; //  value
if (count($called) == 1) {
    $param1 = $called[0];
} else if (count($called) == 2) {
    $param1 = $called[0];
    $param2 = $called[1];
} else if (count($called) == 3) {
    $param1 = $called[0];
    $param2 = $called[1];
    $param3 = $called[2];
} else if (count($called) == 4) {
    $param1 = $called[0];
    $param2 = $called[1];
    $param3 = $called[2];
    $param4 = $called[3];
} else {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad Request");
    die();
}

$database = new Database();
$db = $database->getConnection();

if ($param1 == 'gettempcomp') {
//http://localhost:4200/meteo/api/dashboardCompCtrl.php/gettempcomp/0130/2021-02-02 
    getTempDashboardComp($db, $param2, $param3);
} else if ($param1 == 'getpercicomp') {
//http://localhost:4200/meteo/api/dashboardCompCtrl.php/getpercicomp/0130/2021-02-02 
    getPrecitpitationComponent($db, $param3);
} else if ($param1 == 'getsunshinecomp') {
//http://localhost:4200/meteo/api/dashboardCompCtrl.php/getsunshinecomp/0130/2021-02-02 
    getSunshineComponent($db, $param3);
} else if ($param1 == 'getwindcomp') {
//http://localhost:4200/meteo/api/dashboardCompCtrl.php/getwindcomp/0130/2021-02-02 
    getWindComponent($db, $param3);
} else if ($param1 == 'getpressurecomp') {
//http://localhost:4200/meteo/api/dashboardCompCtrl.php/getpressurecomp/0150/2021-02-02 
    getPressureComponent($db, $param3);
}

//else if ($param1 == 'test') {
////http://localhost:4200/meteo/api/dashboardCompCtrl.php/test/0150/2021-05-14 
//    test($db, $param3);
//}


function getTempDashboardComp($db, $param2, $param3) {
    $tempArr = array();
    $items = new ChannelDataDashComp($db);
    $stmtCurTdate = $items->getCurrentTemperaturePerTDate($param2, $param3);
    $rowCurTdate = $stmtCurTdate->fetch(PDO::FETCH_ASSOC);

    $stmtAvgTdate = $items->getAvgTemperaturePerTDate($param2, $param3);
    $rowAvgTdate = $stmtAvgTdate->fetch(PDO::FETCH_ASSOC);

    $stmtMinTdate = $items->getMinTemperaturePerTDate($param2, $param3);
    $rowMinTdate = $stmtMinTdate->fetch(PDO::FETCH_ASSOC);

    $stmtMaxTdate = $items->getMaxTemperaturePerTDate($param2, $param3);
    $rowMaxTdate = $stmtMaxTdate->fetch(PDO::FETCH_ASSOC);

    $stmtAppTdate = $items->getAppTemperaturePerTDate($param3);
    $rowAppTdate = $stmtAppTdate->fetch(PDO::FETCH_ASSOC);
    $stmtLast24HrT = $items->getLast24HourTemperature();
    $rowLast24HrT = $stmtLast24HrT->fetch(PDO::FETCH_ASSOC);
    $stmtAvgMonth = $items->getMonthlyAvgTemperature();
    $rowAvgMonth = $stmtAvgMonth->fetch(PDO::FETCH_ASSOC);
    
    $calcdate = $rowCurTdate['calcdate']??'';
    $d = date_parse_from_format("m-d-y", $calcdate);
    $datevalue =  $d["month"];
    
    
    $stmtMonthlyNorm = $items->getMonthlyNormTemperature($datevalue);
    $rowMonthlyNorm = $stmtMonthlyNorm->fetch(PDO::FETCH_ASSOC);
        $avgmonthlytemp = $rowAvgMonth['avgmonthlytemp']??0;
        $monthlyNormValue = $rowMonthlyNorm['value']??0;
        $tempArr[] = array(
            'dataPer' => $rowCurTdate['calcdate']??'',
            'dataPerTime' => $rowCurTdate['calctime']??'',
            'currentTemp' => $rowCurTdate['curtemp']??'',
            'tempDailyMax' => $rowMaxTdate['maxtemp']??'',
            'tempDailyMaxTime' => $rowMaxTdate['maxtemptime']??'',
            'tempDailyMin' => $rowMinTdate['mintemp']??'',
            'tempDailyMinTime' => $rowMinTdate['mintemptime']??'',
            'apparentTemp' => $rowAppTdate['apptemp']??'',
            'avgTemplast24hr' => $rowLast24HrT['avg24hrtemp'],
            'avgTempThisMonth' => $rowAvgMonth['avgmonthlytemp'],
            'avgTempThisMonthNorm' => $rowMonthlyNorm['value']??'',
            'diffToNorm' => number_format($avgmonthlytemp-$monthlyNormValue,2)
        );
    
        echo json_encode($tempArr);
}

function getPrecitpitationComponent($db, $param3) {
    $tempArr = array();
    $items = new ChannelDataDashComp($db);
    $stmtLast10Mins = $items->getPerciLast10Mins($param3);
    $rowLast10Mins = $stmtLast10Mins->fetch(PDO::FETCH_ASSOC);
    $stmtLast1Hour = $items->getPerciLast1Hour($param3);
    $rowLast1Hour = $stmtLast1Hour->fetch(PDO::FETCH_ASSOC);
    $stmtPerciToday = $items->findPerciToday($param3);
    $rowPerciToday = $stmtPerciToday->fetch(PDO::FETCH_ASSOC);

    $stmtLastPerci = $items->findLastPrecipitation($param3);
    $rowLastPerci = $stmtLastPerci->fetch(PDO::FETCH_ASSOC);
    //$itemCount = $stmtLast1Hour->rowCount();
    
   
    $seconds = $rowLastPerci['diff'];
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$seconds");
    $diff = $dt1->diff($dt2)->format('%a day, %h hr, %i min');
    $diff = $diff . ' (' . ($rowLastPerci['lastpercitime']) . ')';

    $stmtIntensityToday = $items->findPerciIntensityToday($param3);
    $rowIntensityToday = $stmtIntensityToday->fetch(PDO::FETCH_ASSOC);
    $stmtThisMonth = $items->findPerciThisMonth($param3);
    $rowThisMonth = $stmtThisMonth->fetch(PDO::FETCH_ASSOC);
    
    $stmtThisMonthNorm = $items->getMonthlyNormPercipitation($rowThisMonth['months']);
    $rowThisMonthNorm = $stmtThisMonthNorm->fetch(PDO::FETCH_ASSOC);
    
    

    $tempArr[] = array(
        'dateperse' => $rowLast10Mins['dateperse'],
        'datepersetime' => $rowLastPerci['datepersetime'],
        'perci10mins' => $rowLast10Mins['perci10mins'],
        'perci1Hr' => $rowLast1Hour['perci1Hr'],
        'perciToday' => $rowPerciToday['perciToday'],
        'perciLengthToday' => $items->findPerciLenghtToday($param3),
        'lastPerci' => $diff,
        'perciIntensityMax' => $rowIntensityToday['perciIntensityMax']?? '',
        'perciIntensityMaxTime' => $rowIntensityToday['perciIntensityMaxTime']??'',
        'perciMonthly' => $rowThisMonth['perciMonthly'],
        'perciMonthlyNorm' => $rowThisMonthNorm['value']??''
    );
    
    echo json_encode($tempArr);
}

function getSunshineComponent($db, $param3) {
    $tempArr = array();
    $items = new ChannelDataDashSunComp($db);
    $stmtSunShineLast10Mins = $items->findSunshine10Mins($param3);
    $rowSunShineLast10Mins = $stmtSunShineLast10Mins->fetch(PDO::FETCH_ASSOC);

    $stmtSunshineLast1Hour = $items->findSunshine1Hr($param3);
    $rowSunshineLast1Hour = $stmtSunshineLast1Hour->fetch(PDO::FETCH_ASSOC);
    $stmtSunshineToday = $items->findSunshineToday($param3);
    $rowSunshineToday = $stmtSunshineToday->fetch(PDO::FETCH_ASSOC);

    $stmtGblRadLenght10Min = $items->findGlobalRadiation10Min($param3);
    $rowGblradLenght10Mins = $stmtGblRadLenght10Min->fetch(PDO::FETCH_ASSOC);

    $stmtDiffRad10Min = $items->findDiffuseRadiation10Min($param3);
    $rowDiffRad10Min = $stmtDiffRad10Min->fetch(PDO::FETCH_ASSOC);

    $stmtGblRadHr = $items->findGlobalRadiation1Hr($param3);
    $rowGblRadHr = $stmtGblRadHr->fetch(PDO::FETCH_ASSOC);

    $stmtGblRadToday = $items->findGlobalRadiationToday($param3);
    $rowGblRadToday = $stmtGblRadToday->fetch(PDO::FETCH_ASSOC);
//     $util = new Utility;      
//     $sundurationtoday = //$util->secondsToTime($rowSunshineToday['sundurationtoday']);      
    $totalHr = gmdate("H", $rowSunshineToday['sundurationtoday']);
    $totalMin = gmdate("i", $rowSunshineToday['sundurationtoday']);

    $min = ' min ';
    $hr = ' hr ';
    if ($totalHr > 1) {
        $min = ' mins';
    }if ($totalHr > 1) {
        $hr = ' hrs ';
    }
    $sundurationtoday = $totalHr . $hr . $totalMin . $min;

    $tempArr[] = array(
        'dateperse' => $rowSunShineLast10Mins['dateperse']??'',
        'datepersetime' => $rowSunShineLast10Mins['datepersetime']??'',
        'sunduration10mins' => $rowSunShineLast10Mins['sunduration10mins']??'',
        'sundurationlast1hr' => $rowSunshineLast1Hour['sundurationlast1hr'] . $min,
        'sundurationtoday' => $sundurationtoday,
        'globalradiation10mins' => $rowGblradLenght10Mins['globalradiation10mins']??'',
        'diffuseradiation' => $rowDiffRad10Min['diffuseradiation']??'',
        'globalradiationlasthr' => $rowGblRadHr['globalradiationlasthr']??'',
        'globalradTodaymax' => $rowGblRadToday['globalradTodaymax']??'',
        'globalradTodaymaxtime' => $rowGblRadToday['globalradTodaymaxtime']??''
    );
    echo json_encode($tempArr);
}

function getWindComponent($db, $param3) {
    $tempArr = array();
    $items = new ChannelDataDashWindComp($db);

    $stmtWindDirLast10Mins = $items->findWindDirection10Mins($param3);
    $rowWindDirLast10Mins = $stmtWindDirLast10Mins->fetch(PDO::FETCH_ASSOC);

    $finalResultArr = $items->findWindDirection1Hour($param3);
    $directionValue = $finalResultArr[0];
    $avgDirectionLast1Hr = $directionValue['avgDirection'];
    $avgDirectionDegreeLast1Hr = $directionValue['value'];

    $stmtAvgWindSpeed10Min = $items->findAvgWindSpeed10Mins($param3);
    $rowAvgWindSpeed10Min = $stmtAvgWindSpeed10Min->fetch(PDO::FETCH_ASSOC);

    $stmtWindSpeedHr = $items->findAvgWindSpeed1Hour($param3);
    $rowWindSpeedHr = $stmtWindSpeedHr->fetch(PDO::FETCH_ASSOC);

    $stmtMaxWindSpeedToday = $items->findMaxWindSpeedToday($param3);
    $rowMaxWindSpeedToday = $stmtMaxWindSpeedToday->fetch(PDO::FETCH_ASSOC);

    $stmtMaxGustLastHr = $items->findMaxGustLastHr($param3);
    $rowMaxGustLastHr = $stmtMaxGustLastHr->fetch(PDO::FETCH_ASSOC);
    $stmtMaxGustLastHrDegree = $items->findMaxGustLastHrDegree($rowMaxGustLastHr['gustmaxtimelasthrdegree']??'');
    $rowMaxGustLastHrDegree = $stmtMaxGustLastHrDegree->fetch(PDO::FETCH_ASSOC);

    $stmtMaxGustToday = $items->findMaxGustToday($param3);
    $rowMaxGustToday = $stmtMaxGustToday->fetch(PDO::FETCH_ASSOC);
    $stmtMaxGustTodayDegree = $items->findMaxGustTodayDegree($rowMaxGustToday['maxwindgusttimetodaydegree']??'');
    $rowMaxGustTodayDegree = $stmtMaxGustTodayDegree->fetch(PDO::FETCH_ASSOC);
    $tempArr[] = array(
        'dataPer' => $rowWindDirLast10Mins['dataPer']??'',
        'dataPerTime' => $rowWindDirLast10Mins['dataPerTime']??'',
        'last10mins' => $rowWindDirLast10Mins['last10mins']??'',
        'windDirlast10mins' => $items->getWindDirection($rowWindDirLast10Mins['windDirlast10mins']??''),
        'windDirlast10minsDeg' => $rowWindDirLast10Mins['windDirlast10mins']??'',
        'winddirlast1Hour' => $avgDirectionLast1Hr,
        'winddirdegreelast1Hour' => $avgDirectionDegreeLast1Hr,
        'windspeedlast10Mins' => number_format($rowAvgWindSpeed10Min['windspeedlast10Mins']*3.6??0 , 2),
        'windspeedlast1hr' => number_format($rowWindSpeedHr['windspeedlast1hr']*3.6??0 , 2),
        'avgWindspeedToday' => number_format($rowMaxWindSpeedToday['avgWindspeedToday']*3.6??0 , 2),
        'maxWindGustLastHr' => number_format($rowMaxGustLastHr['maxwindgustlasthr']*3.6??0 , 2),
        'maxWindGustLastHrDir' => $items->getWindDirection($rowMaxGustLastHrDegree['maxwindgusthrdegree']??''),
        'maxWindGustLastHrDirDegree' => $rowMaxGustLastHrDegree['maxwindgusthrdegree']??'',
        'maxWindGustTimeLastHr' => $rowMaxGustLastHr['gustmaxtimelasthr']??'',
        'maxWindGustToday' => number_format($rowMaxGustToday['maxwindgusttoday']*3.6??0 , 2),
        'maxWindGustTodayDir' => $items->getWindDirection($rowMaxGustTodayDegree['maxwindgusttodaydegree']??''),
        'maxWindGustTodayDirDegree' => $rowMaxGustTodayDegree['maxwindgusttodaydegree']??'',
        'maxWindGustTimeToday' => $rowMaxGustToday['maxwindgusttimetoday']??''
    );



    echo json_encode($tempArr);
}

function getPressureComponent($db, $param3) {
    $tempArr = array();
    $items = new ChannelDataDashPressureComp($db);
    $stmtPressureLast10Mins = $items->findPressure10Mins($param3);
    $rowPressureLast10Mins = $stmtPressureLast10Mins->fetch(PDO::FETCH_ASSOC);

    $stmtPressureChange3Hour = $items->findPressureChange3Hrs($param3);
    $rowPressureChange3Hour = $stmtPressureChange3Hour->fetch(PDO::FETCH_ASSOC);

    $stmtPressureChange12Hr = $items->findPressureChange12Hrs($param3);
    $rowPressureChange12Hr = $stmtPressureChange12Hr->fetch(PDO::FETCH_ASSOC);

    $stmtHumidity10Mins = $items->findHumidity10Mins($param3);
    $rowHumidity10Mins = $stmtHumidity10Mins->fetch(PDO::FETCH_ASSOC);

    $stmtHumidityDailyMin = $items->findHumidityDailyMin($param3);
    $rowHumidityDailyMin = $stmtHumidityDailyMin->fetch(PDO::FETCH_ASSOC);

    $stmtHumidityDailyMax = $items->findHumidityDailyMax($param3);
    $rowHumidityDailyMax = $stmtHumidityDailyMax->fetch(PDO::FETCH_ASSOC);

    $stmtDewpoint10Mins = $items->findDewpoint10Mins($param3);
    $rowDewpoint10Mins = $stmtDewpoint10Mins->fetch(PDO::FETCH_ASSOC);

    $stmtWetBulbTemp = $items->findWetBulbTemp($param3);
    $rowWetBulbTemp = $stmtWetBulbTemp->fetch(PDO::FETCH_ASSOC);

    $tempArr[] = array(
        'pressurelast10Mins' => $rowPressureLast10Mins['pressurelast10Mins']??'',
        'datepersetime' => $rowPressureLast10Mins['datepersetime']??'',
        'dateperse' => $rowPressureLast10Mins['dateperse']??'',
        'pressureChangelst3hr' => $rowPressureChange3Hour['pressureChangelst3hr']??'',
        'pressureChangelst12hr' => $rowPressureChange12Hr['pressureChangelst12hr']??'',
        'humidity10Mins' => $rowHumidity10Mins['humidity10Mins']??'',
        'humiditydailymin' => $rowHumidityDailyMin['humiditydailymin']??'',
        'humiditydailyminTime' => $rowHumidityDailyMin['humiditydailyminTime']??'',
        'humiditydailyMax' => $rowHumidityDailyMax['humiditydailyMax']??'',
        'humiditydailyMaxtime' => $rowHumidityDailyMax['humiditydailyMaxtime']??'',
        'dewpoint10Mins' => $rowDewpoint10Mins['dewpoint10Mins']??'',
        'wetBulb10Mins' => $rowWetBulbTemp['wetBulb10Mins']??''
    );
    echo json_encode($tempArr);
}
