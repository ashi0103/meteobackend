<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelHumidityDataPage.php');

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

if ($param1 == 'getHumiditydailyoverview') {
    //http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumiditydailyoverview/2021-02-01/2021-02-05 
    getHumiditydailyoverview($db, $param2, $param3);
} 
else if ($param1 == 'getHumidityalldaily') {
    //http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityalldaily/2021-02-01/2021-02-05 
    getHumiditydailyAll($db, $param2, $param3);
}
else if ($param1 == 'getHumidityDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityDailyOverviewByDate/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getHumidityDailyOverviewByDatesParam($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getHumidityOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityOverviewByMonthParam/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getHumidityOverviewByMonthParam($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
}

//********** Start Functions *************************************

function getHumiditydailyAll($db, $param2, $param3) {
    $items = new ChannelHumidityDataPage($db);
    $tempArr = $items->getHumiditydailyAll($param2, $param3);
    echo json_encode($tempArr);
}

function getHumiditydailyoverview($db, $param2, $param3) {
    $items = new ChannelHumidityDataPage($db);
    $tempArr = $items->getHumidityDurationPerDays($param2, $param3);
    echo json_encode($tempArr);
}

function getMinAvailableDates($db, $param2) {
    $items = new ChannelHumidityDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);
    echo json_encode($tempArr);
}

function getHumidityDailyOverviewByDatesParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelHumidityDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getHumidityDailyOverviewByDatesParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}

function getHumidityOverviewByMonthParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelHumidityDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getHumidityOverviewByMonthParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}


//***********************************************************************************
