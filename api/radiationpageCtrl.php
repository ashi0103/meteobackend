<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelRadiationDataPage.php');

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

if ($param1 == 'getradiationdailyoverview') {
    //http://localhost:4200/meteo/api/radiationpageCtrl.php/getradiationdailyoverview/2021-02-01/2021-02-05 
    getRadiationdailyoverview($db, $param2, $param3);
} 
else if ($param1 == 'getradiationalldaily') {
    //http://localhost:4200/meteo/api/radiationpageCtrl.php/getradiationalldaily/2021-02-01/2021-02-05 
    getRadiationdailyAll($db, $param2, $param3);
}
else if ($param1 == 'getradiationDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/radiationpageCtrl.php/getradiationDailyOverviewByDate/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getRadiationDailyOverviewByDatesParam($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getradiationOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/radiationpageCtrl.php/getradiationOverviewByMonthParam/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getRadiationOverviewByMonthParam($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
}

//********** Start Functions *************************************

function getRadiationdailyAll($db, $param2, $param3) {
    $items = new ChannelRadiationDataPage($db);
    $tempArr = $items->getRadiationdailyAll($param2, $param3);
    echo json_encode($tempArr);
}

function getRadiationdailyoverview($db, $param2, $param3) {
    $items = new ChannelRadiationDataPage($db);
    $tempArr = $items->getRadiationDurationPerDays($param2, $param3);
    echo json_encode($tempArr);
}

function getMinAvailableDates($db, $param2) {
    $items = new ChannelRadiationDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);
    echo json_encode($tempArr);
}

function getRadiationDailyOverviewByDatesParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelRadiationDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getRadiationDailyOverviewByDatesParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}

function getRadiationOverviewByMonthParam($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelRadiationDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getRadiationOverviewByMonthParam($param2, $param3, $param4, $datelist);
    echo json_encode($tempArr);
}


//***********************************************************************************
