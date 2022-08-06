<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelTemperatureDataPage.php');

/** @var type $requestMethod */
//$requestMethod = $_SERVER["REQUEST_METHOD"];
$pathinfo = $_SERVER['PATH_INFO'];
$called = explode('/', trim($_SERVER['PATH_INFO'], '/'));



$param1; // Get method 
$param2; // Channel id or To Date
$param3; //  Date Or From Date
$param4; //  value Or some values
$param5; //  value Or some values
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
} else if (count($called) == 5) {
    $param1 = $called[0];
    $param2 = $called[1];
    $param3 = $called[2];
    $param4 = $called[3];
    $param5 = $called[4];
} else {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad Request");
    die();
}

$database = new Database();
$db = $database->getConnection();

if ($param1 == 'gettemperaturedailyoverview') {
    //http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturedailyoverview/2021-02-01/2021-02-05 
    getTemperaturedailyoverview($db, $param2, $param3);
}if ($param1 == 'gettemperaturealldaily') {
    //http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturealldaily/2021-02-01/2021-02-05 
    getTemperaturedailyAll($db, $param2, $param3);
}
else if ($param1 == 'gettemperaturediffperiods') {
//http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturediffperiods/2021-02-05 
    getTemperatureDifferentPeriods($db, $param2);
} else if ($param1 == 'geteventbuckets') {
//http://localhost:4200/meteo/api/temperaturepageCtrl.php/geteventbuckets
    getTemperatureEventBuckets($db);
} else if ($param1 == 'gettemperaturecompaginstnorm') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturecompaginstnorm/0130/2021
    getTemperatureCompAginstNorm($db, $param3);
} else if ($param1 == 'gettemperaturecompaginstnormyear') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturecompaginstnormyear/0130/2021
    getTemperatureCompAginstNormYear($db, $param3);
} else if ($param1 == 'getCumulativeTempByYear') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getCumulativeTempByYear/2020/2021
    getCumulativeTempByYear($db, $param2, $param3);
}
else if ($param1 == 'insertTemperatureBalanceByDay') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/insertTemperatureBalanceByDay
    InsertTemperatureBalanceByDay($db);
} else if ($param1 == 'insertTemperatureBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/insertTemperatureBalanceMonthAgo
    InsertTemperatureBalanceMonthAgo($db);
} else if ($param1 == 'insertTemperatureBalanceByDayPart3') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/insertTemperatureBalanceByDayPart3
    InsertTemperatureBalanceByDayPart3($db);
} else if ($param1 == 'gettemperaturebalance') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/gettemperaturebalance/2021-06-21/30
    getTemperatureBalance($db, $param2, $param3);
} else if ($param1 == 'getTemperatureBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureBalanceMonthAgo/2021-06-21/30
    getTemperatureBalanceMonthAgo($db, $param2, $param3);
} else if ($param1 == 'getTemperatureBalanceByDay') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureBalanceByDay/2021-06-21/30
    getTemperatureBalanceByDay($db, $param2, $param3);
} else if ($param1 == 'getMinAvailableDates') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getMinAvailableDates/0130
    getMinAvailableDates($db, $param2);
} else if ($param1 == 'getTemperatureColdPeriod') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureColdPeriod/2021-05-22/2021-05-28/0
    getTemperatureColdPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getTemperatureWarmPeriod') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureWarmPeriod/2021-05-22/2021-05-28/20
    getTemperatureWarmPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getTemperatureDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureDailyOverviewByDate/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getTemperatureDailyOverviewByDatesParam($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getTemperatureOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureOverviewByMonthParam/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getTemperatureOverviewByMonthParam($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
}else if ($param1 == 'getTemperatureHeatMap') {
    // http://localhost:4200/meteo/api/temperaturepageCtrl.php/getTemperatureHeatMap/2021-05-22/2021-05-28   
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getTemperatureHeatMap($db, $param2, $param3);
}

//********** Start Functions *************************************

function getTemperaturedailyAll($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperaturedailyAll($param2, $param3);
    echo json_encode($tempArr);
}


function getTemperaturedailyoverview($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureDurationPerDays($param2, $param3);
    echo json_encode($tempArr);
}


function getTemperatureDifferentPeriods($db, $param2) {
    $finalArr = array();
    $items = new ChannelTemperatureDataPage($db);
    //Today
    $todaytemp = $items->get1DayTemperature($param2, 'T');
    $finalArr = array_merge($finalArr, $todaytemp);
    //Yesterday
    $ydate = date("Y-m-d", strtotime("-1 day", strtotime($param2)));
    $ydaytemp = $items->get1DayTemperature($ydate, 'Y');
    $finalArr = array_merge($finalArr, $ydaytemp);

    $tempTweek = $items->findTemperatureThisWeek();
    $finalArr = array_merge($finalArr, $tempTweek);

    $tempLast7Days = $items->findTemperatureByNoOfDays('7');
    $finalArr = array_merge($finalArr, $tempLast7Days);

    $tempThisMonth = $items->findTemperatureThisMonth();
    $finalArr = array_merge($finalArr, $tempThisMonth);

    $tempLast31Days = $items->findTemperatureByNoOfDays('31');
    $finalArr = array_merge($finalArr, $tempLast31Days);

    $tempThisYear = $items->findTemperatureThisYear();
    $finalArr = array_merge($finalArr, $tempThisYear);

    $tempLastFrost = $items->findTemperatureLastFrost();
    $finalArr = array_merge($finalArr, $tempLastFrost);

    $tempFirstFrost = $items->findTemperatureFirstFrost();
    $finalArr = array_merge($finalArr, $tempFirstFrost);

    $tempIcyDay = $items->findTemperatureLastIcyDay();
    $finalArr = array_merge($finalArr, $tempIcyDay);

    $tempLastSummerDay = $items->findTemperatureLastSummerDay();
    $finalArr = array_merge($finalArr, $tempLastSummerDay);

    $tempLastHeatDay = $items->findTemperatureLastHeatDay();
    $finalArr = array_merge($finalArr, $tempLastHeatDay);

    $tempHeatNight = $items->findTemperatureLastHeatNight();
    $finalArr = array_merge($finalArr, $tempHeatNight);

//    echo "<pre>";
//    print_r($finalArr);
//    echo "<post>";

    echo json_encode($finalArr);
}

function getTemperatureEventBuckets($db) {

    //Values are in mins
    $year = date("Y");
    $last5year = $year - 10;
    $valuesArr = array();

    for ($y = $year; $y > $last5year; $y--) {
        $tempArr = array();
        $i = 0;
        $items = new ChannelTemperatureDataPage($db);

        $tempArr[$i] = $items->findTemperatureBuckets(-50, -5, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
        $i = $i + 1;

        $tempArr[$i] = $items->findTemperatureBuckets(-5, 0, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);


        $i = $i + 1;
        $tempArr[$i] = $items->findTemperatureBuckets(0, 10, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);

        $i = $i + 1;

        $tempArr[$i] = $items->findTemperatureBuckets(10, 20, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);

        $i = $i + 1;

        $tempArr[$i] = $items->findTemperatureBuckets(20, 25, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
        $i = $i + 1;

        $tempArr[$i] = $items->findTemperatureBuckets(25, 30, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
        $i = $i + 1;

        $tempArr[$i] = $items->findTemperatureBuckets(30, 80, $y);
        $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
        $i = $i + 1;


        $valueArr = array(
            'rangeMi50andMi5' => $tempArr[0] ?? '-',
            'rangeMi5and0' => $tempArr[1] ?? '-',
            'range0and10' => $tempArr[2] ?? '-',
            'range10and20' => $tempArr[3] ?? '-',
            'range20and25' => $tempArr[4] ?? '-',
            'range25and30' => $tempArr[5] ?? '-',
            'range30above' => $tempArr[6] ?? '-',
        );

        array_push($valuesArr, $valueArr);
    }
    echo json_encode($valuesArr);
}

function getTemperatureCompAginstNorm($db, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureCompAginstNorm($param3);
    echo json_encode($tempArr);
}

function getTemperatureCompAginstNormYear($db, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureCompAginstNormYear($param3);
    echo json_encode($tempArr);
}

function getCumulativeTempByYear($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getCumulativeTempByYear($param2, $param3);
    echo json_encode($tempArr);
}

function getTemperatureBalance($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureBalanceValues($param2, $param3);
    echo json_encode($tempArr);
}

function getTemperatureBalanceMonthAgo($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureBalanceMonthAgoValue($param2, $param3);
    echo json_encode($tempArr);
}

//Chart
function getTemperatureBalanceByDay($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureBalanceByDay($param2, $param3);
    echo json_encode($tempArr);
}

// Insert into table
function InsertTemperatureBalanceByDay($db) {
    $items = new ChannelTemperatureDataPage($db);
    $date = date('Y-m-d');
    $items->InsertTemperatureBalanceByDay($date, '30');
    $items->InsertTemperatureBalanceByDay($date, '60');
}

// Insert into table
function InsertTemperatureBalanceMonthAgo($db) {
    $items = new ChannelTemperatureDataPage($db);
    $date = date('Y-m-d');
    $items->InsertTemperatureBalanceByDay($date, '90');
    $items->InsertTemperatureBalanceByDay($date, '121');
}

// Insert into table
function InsertTemperatureBalanceByDayPart3($db) {
    $items = new ChannelTemperatureDataPage($db);
    $date = date('Y-m-d');
    $items->InsertTemperatureBalanceByDay($date, '182');
    $items->InsertTemperatureBalanceByDay($date, '273');
    $items->InsertTemperatureBalanceByDay($date, '365');
}

function getMinAvailableDates($db, $param2) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);
    echo json_encode($tempArr);
}

function getTemperatureColdPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureColdPeriod($param2, $param3, $param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getTemperatureWarmPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelTemperatureDataPage($db);
    $tempArr = $items->getTemperatureWarmPeriod($param2, $param3, $param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getTemperatureHeatMap($db, $param2, $param3) {
    $items = new ChannelTemperatureDataPage($db);    
    $tempArr = $items->getTemperatureHeatMap($param2, $param3);
    echo json_encode($tempArr);
}

function getTemperatureDailyOverviewByDatesParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelTemperatureDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getTemperatureDailyOverviewByDatesParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}

function getTemperatureOverviewByMonthParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelTemperatureDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getTemperatureOverviewByMonthParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}



//***********************************************************************************





