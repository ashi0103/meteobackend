<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelWindDataPage.php');

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

if ($param1 == 'getwinddailyoverview') {
    //http://localhost:4200/meteo/api/windpageCtrl.php/getwinddailyoverview/2021-02-01/2021-02-05 
    getWindDurationPerDays($db, $param2, $param3);
}if ($param1 == 'getwindalldaily') {
    //http://localhost:4200/meteo/api/windpageCtrl.php/getwindalldaily/2021-02-01/2021-02-05 
    getWindDailyAll($db, $param2, $param3);
}
else if ($param1 == 'getwindalldirections') {
//http://localhost:4200/meteo/api/windpageCtrl.php/getwindalldirections/2021-02-01/2021-02-05 
    getWindAllDirections($db, $param2, $param3);
} else if ($param1 == 'getwindcompaginstnorm') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getwindcompaginstnorm/0130/2021
    getWindCompAginstNorm($db, $param3);
} else if ($param1 == 'getwindcompaginstnormyear') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getwindcompaginstnormyear/0130/2
    getWindCompAginstNormYear($db, $param3);
} else if ($param1 == 'getMinAvailableDates') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getMinAvailableDates/0130
    getMinAvailableDates($db, $param2);
} else if ($param1 == 'getWindCalmPeriod') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getWindCalmPeriod/2021-05-22/2021-05-28/10
    getWindCalmPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getWindWindyPeriod') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getWindWindyPeriod/2021-05-22/2021-05-28/20
    getWindWindyPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getWindDailyOverviewByDatesParam') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getWindDailyOverviewByDatesParam/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getWindDailyOverviewByDatesParam($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getWindOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getWindOverviewByMonthParam/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getWindOverviewByMonthParam($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
}else if ($param1 == 'getwindHeatMap') {
    // http://localhost:4200/meteo/api/windpageCtrl.php/getwindHeatMap/2021-05-22/2021-05-28   
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getWindHeatMap($db, $param2, $param3);
}

//********** Start Functions *************************************

function getWindDailyAll($db, $param2, $param3) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindDailyAll($param2, $param3);
    echo json_encode($tempArr);
}


function getWindDurationPerDays($db, $param2, $param3) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindDurationPerDays($param2, $param3);
    echo json_encode($tempArr);
}


function getWindAllDirections($db, $param2,$param3) {
    $items = new ChannelWindDataPage($db);
    $finalArr = $items->getRadarChartValues($param2, $param3);    
//    echo "<pre>";
//    print_r($finalArr);
//    echo "<post>";

    echo json_encode($finalArr);
}


function getWindCompAginstNorm($db, $param3) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindCompAginstNorm($param3);
    echo json_encode($tempArr);
}

function getWindCompAginstNormYear($db, $param3) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindCompAginstNormYear($param3);
    echo json_encode($tempArr);
}


function getMinAvailableDates($db, $param2) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);
    echo json_encode($tempArr);
}

function getWindCalmPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindCalmPeriod($param2, $param3, $param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getWindWindyPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelWindDataPage($db);
    $tempArr = $items->getWindWindyPeriod($param2, $param3, $param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getWindHeatMap($db, $param2, $param3) {
    $items = new ChannelWindDataPage($db);    
    $tempArr = $items->getWindHeatMap($param2, $param3);
    echo json_encode($tempArr);
}

function getWindDailyOverviewByDatesParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelWindDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getWindDailyOverviewByDatesParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}

function getWindOverviewByMonthParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelWindDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getWindOverviewByMonthParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}



//***********************************************************************************





