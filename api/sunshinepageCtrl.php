<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelSunshineDataPage.php');

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
}else {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad Request");
    die();
}

$database = new Database();
$db = $database->getConnection();

if($param1 == 'getsunshinedailyoverview'){
  //http://localhost:4200/meteo/api/sunshinepageCtrl.php/getsunshinedailyoverview/2021-02-01/2021-02-05 
    getSunshinedailyoverview($db, $param2, $param3);
}else if ($param1 == 'getAllSunshineByDate') {
//http://localhost:4200/meteo/api/sunshinepageCtrl.php/getAllSunshineByDate/2021-02-01/2021-02-05  
    getAllSunshineByDate($db, $param2, $param3);
}
else if ($param1 == 'getsunshinediffperiods') {
//http://localhost:4200/meteo/api/sunshinepageCtrl.php/getsunshinediffperiods 
    getSunshineDifferentPeriods($db);
} 
else if ($param1 == 'geteventbuckets') {
//http://localhost:4200/meteo/api/sunshinepageCtrl.php/geteventbuckets
    getSunshineEventBuckets($db);
}
else if ($param1 == 'getsunshinecompaginstnorm') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getsunshinecompaginstnorm/0101/2021
    getSunshineCompAginstNorm($db, $param3);
}
else if ($param1 == 'getsunshinecompaginstnormyear') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getsunshinecompaginstnormyear/0101/1
    getSunshineCompAginstNormYear($db, $param3);
}
else if ($param1 == 'insertSunshineBalanceByDay') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/insertSunshineBalanceByDay
    InsertSunshineBalanceByDay($db);
}else if ($param1 == 'insertSunshineBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/insertSunshineBalanceMonthAgo
    InsertSunshineBalanceMonthAgo($db);
}else if ($param1 == 'insertSunshineBalanceByDayPart3') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/insertSunshineBalanceByDayPart3
    InsertSunshineBalanceByDayPart3($db);
}
else if ($param1 == 'getsunshinebalance') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getsunshinebalance/2021-06-21/30
    getSunshineBalance($db,$param2,$param3);
}
else if ($param1 == 'getSunshineBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineBalanceMonthAgo/2021-06-21/30
    getSunshineBalanceMonthAgo($db,$param2,$param3);
}
else if ($param1 == 'getSunshineBalanceByDay') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineBalanceByDay/2021-06-21/30
    getSunshineBalanceByDay($db,$param2,$param3);
}
else if ($param1 == 'getMinAvailableDates') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getMinAvailableDates/0124
    getMinAvailableDates($db, $param2);
}
else if ($param1 == 'getSunshineCloudyPeriod') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineCloudyPeriod/2021-05-22/2021-05-28/.99
    getSunshineCloudyPeriod($db,$param2,$param3,$param4);
}
else if ($param1 == 'getSunshineSunnyPeriod') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineSunnyPeriod/2021-05-22/2021-05-28/.99
    getSunshineSunnyPeriod($db,$param2,$param3,$param4);
}
else if ($param1 == 'getSunshineDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineDailyOverviewByDate/D/2021-05-22    
        //getting list of dates
        $datelist = $_GET['datelist']??null;           
        getSunshineDailyOverviewByDatesParam($db,$param2,$param3??null,$param4??null, $datelist);
}
else if ($param1 == 'getSunshineOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/sunshinepageCtrl.php/getSunshineOverviewByMonthParam/D/05-2021    
        //getting list of dates
        $monthlist = $_GET['datelist']??null;           
        getSunshineOverviewByMonthParam($db,$param2,$param3??null,$param4??null, $monthlist);
}


//********** Start Functions *************************************



function getAllSunshineByDate($db, $param2,$param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getAllSunshineByDate($param2,$param3);            
    echo json_encode($tempArr);
}


function getSunshinedailyoverview($db, $param2,$param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunhineDurationPerDays($param2,$param3);            
    echo json_encode($tempArr);
}


function getSunshineDifferentPeriods($db) {
    $tempArr = array();
    $items = new ChannelSunshineDataPage($db);

    $stmtPerciTweek = $items->findSunshineThisWeek();
    $rowPerciTweek = $stmtPerciTweek->fetch(PDO::FETCH_ASSOC);

    $stmtPerciLast7Days = $items->findSunshineByNoOfDays('7');
    $rowPerciLast7Days = $stmtPerciLast7Days->fetch(PDO::FETCH_ASSOC);

    $stmtPerciThisMonth = $items->findSunshineThisMonth();
    $rowPerciThisMonth = $stmtPerciThisMonth->fetch(PDO::FETCH_ASSOC);

    $stmtPerciLast31Days = $items->findSunshineByNoOfDays('31');
    $rowPerciLast31Days = $stmtPerciLast31Days->fetch(PDO::FETCH_ASSOC);

    $stmtPerciThisYear = $items->findSunshineThisYear();
    $rowPerciThisYear = $stmtPerciThisYear->fetch(PDO::FETCH_ASSOC);
    $util = new UtilCommon;
    $tempArr[] = array(        
        'sunshineweek' => $util->secondsToDayMinsHrs($rowPerciTweek['sunshineweek']),
        'sunshineTweek' => $util->secondsToDayMinsHrs($rowPerciTweek['sunshineTweek']),
        'sunshineAvgTweek' => $util->secondsToDayMinsHrs($rowPerciTweek['sunshineAvgTweek']),
        
        'sunshineLast7Days' => $util->secondsToDayMinsHrs($rowPerciLast7Days['sunshineLastXDays']),
        'sunshineAvgLast7Days' => $util->secondsToDayMinsHrs($rowPerciLast7Days['sunshineAvgLastXDays']),
        
        'sunshineTMonth' => $util->secondsToDayMinsHrs($rowPerciThisMonth['sunshineTMonth']),
        'sunshineAvgTMonth' => $util->secondsToDayMinsHrs($rowPerciThisMonth['sunshineAvgTMonth']),
        
        'sunshineLast31Days' => $util->secondsToDayMinsHrs($rowPerciLast31Days['sunshineLastXDays']),
        'sunshineAvgLast31Days' => $util->secondsToDayMinsHrs($rowPerciLast31Days['sunshineAvgLastXDays']),
        
        'sunshineThisYear' => $util->secondsToDayMinsHrs($rowPerciThisYear['sunshineThisYear']),
        'sunshineAvgThisYear' => $util->secondsToDayMinsHrs($rowPerciThisYear['sunshineAvgThisYear'])
    );
    echo json_encode($tempArr);
}


function getSunshineEventBuckets($db) {
    $countArr = array();
    $tempArr = array();
    $i = 0;
    //Values are in mins
    $items = new ChannelSunshineDataPage($db);
    
    $tempArr[$i] = $items->findSunshineBuckets(0, 0);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;
    
    $tempArr[$i] = $items->findSunshineBuckets(0, 15);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;
    $tempArr[$i] = $items->findSunshineBuckets(15, 60);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findSunshineBuckets(60, 120);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findSunshineBuckets(120, 180);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findSunshineBuckets(180, 300);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findSunshineBuckets(300, 480);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findSunshineBuckets(480, 1440);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
        
    $maximum = max($countArr);
    $arr = array(
        "valuedate" => '',
        "value" => ''
    );
    $tempArr[0] = array_pad($tempArr[0], $maximum, $arr);
    $tempArr[1] = array_pad($tempArr[1], $maximum, $arr);
    $tempArr[2] = array_pad($tempArr[2], $maximum, $arr);
    $tempArr[3] = array_pad($tempArr[3], $maximum, $arr);
    $tempArr[4] = array_pad($tempArr[4], $maximum, $arr);
    $tempArr[5] = array_pad($tempArr[5], $maximum, $arr);
    $tempArr[6] = array_pad($tempArr[6], $maximum, $arr);
    $tempArr[7] = array_pad($tempArr[7], $maximum, $arr);

    $valueArr[] = array(        
        'nosun' => $tempArr[0],
        'range0and15' => $tempArr[1],
        'range15and60' => $tempArr[2],
        'range60and120' => $tempArr[3],
        'range120and180' => $tempArr[4],
        'range180and300' => $tempArr[5],
        'range300and480' => $tempArr[6],
        'range480above' => $tempArr[7]
    );
    echo json_encode($valueArr);
}


function getSunshineCompAginstNorm($db,$param3) {
    $items = new ChannelSunshineDataPage($db);    
    $tempArr = $items->getSunshineCompAginstNorm($param3);            
    echo json_encode($tempArr);
}
function getSunshineCompAginstNormYear($db, $param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineCompAginstNormYear($param3);            
    echo json_encode($tempArr);
}


function getSunshineBalance($db,$param2,$param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineBalanceValues($param2,$param3);
    echo json_encode($tempArr);
}

function getSunshineBalanceMonthAgo($db,$param2,$param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineBalanceMonthAgoValue($param2,$param3);
    echo json_encode($tempArr);
}

//Chart
function getSunshineBalanceByDay($db,$param2,$param3) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineBalanceByDay($param2,$param3);
    echo json_encode($tempArr);
}

// Insert into table
function InsertSunshineBalanceByDay($db) {
    $items = new ChannelSunshineDataPage($db);
    $date = date('Y-m-d');    
    $items->InsertSunshineBalanceByDay($date,'30');    
    $items->InsertSunshineBalanceByDay($date,'60');    
}

// Insert into table
function InsertSunshineBalanceMonthAgo($db) {
    $items = new ChannelSunshineDataPage($db);
    $date = date('Y-m-d');    
    $items->InsertSunshineBalanceByDay($date,'90');
    $items->InsertSunshineBalanceByDay($date,'121');   
}

// Insert into table
function InsertSunshineBalanceByDayPart3($db) {
    $items = new ChannelSunshineDataPage($db);
    $date = date('Y-m-d');    
     $items->InsertSunshineBalanceByDay($date,'182');
     $items->InsertSunshineBalanceByDay($date,'273');
     $items->InsertSunshineBalanceByDay($date,'365');
}

function getMinAvailableDates($db, $param2) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);            
    echo json_encode($tempArr);
}

function getSunshineCloudyPeriod($db,$param2,$param3,$param4) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineCloudyPeriod($param2,$param3,$param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getSunshineSunnyPeriod($db,$param2,$param3,$param4) {
    $items = new ChannelSunshineDataPage($db);
    $tempArr = $items->getSunshineSunnyPeriod($param2,$param3,$param4);
    
    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}


function getSunshineDailyOverviewByDatesParam($db,$param2,$param3,$param4,$datelist) {
    $items = new ChannelSunshineDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getSunshineDailyOverviewByDatesParam($param2,$param3,$param4,$datelist);
    echo json_encode($tempArr);
}


function getSunshineOverviewByMonthParam($db,$param2,$param3,$param4,$datelist) {
    $items = new ChannelSunshineDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getSunshineOverviewByMonthParam($param2,$param3,$param4,$datelist);
    echo json_encode($tempArr);
}



//***********************************************************************************





