<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelPerciDataPage.php');

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

if ($param1 == 'getteventbuckets') {
//http://localhost:4200/meteo/api/percipitationpageCtrl.php/getteventbuckets/0101/2021-02-02 
    getPreciEventBuckets($db, $param3);
} else if ($param1 == 'getprecidiffperiods') {
//http://localhost:4200/meteo/api/percipitationpageCtrl.php/getprecidiffperiods/0101/2021-05-22 
    getPreciDifferentPeriods($db, $param3);
} else if ($param1 == 'getprecimonthcomapre') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getprecimonthcomapre/0101/2021-05-22/2019 
    getPrecipitationMonthComapre($db, $param4);
} else if ($param1 == 'getpreciyearcomapre') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getpreciyearcomapre 
    getPrecipitationYearComapre($db);
} else if ($param1 == 'getprecitypebydate') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getprecitypebydate/0098/2021-05-22/2021-05-28
    getPreciTypeByDate($db, $param3, $param4);
} else if ($param1 == 'getprecidailyoverview') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getprecidailyoverview/0098/2021-05-22/2021-05-28 
    getprecidailyoverview($db, $param3, $param4);
} else if ($param1 == 'getMinAvailableDates') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getMinAvailableDates/0098
    getMinAvailableDates($db, $param2);
} else if ($param1 == 'getpercicompaginstnorm') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getpercicompaginstnorm/0101/2021
    getPerciCompAginstNorm($db, $param3);
} else if ($param1 == 'getpercicompaginstnormyear') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getpercicompaginstnormyear/0101/1
    getPerciCompAginstNormYear($db, $param3);
} else if ($param1 == 'getpercirainbalance') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getpercirainbalance/2021-06-21/30
    getPerciRainBalance($db, $param2, $param3);
} else if ($param1 == 'getPerciRainBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciRainBalanceMonthAgo/2021-06-21/30
    getPerciRainBalanceMonthAgo($db, $param2, $param3);
} else if ($param1 == 'getPerciRainBalanceByDay') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciRainBalanceByDay/2021-06-21/30
    getPerciRainBalanceByDay($db, $param2, $param3);
} else if ($param1 == 'insertPerciRainBalanceByDay') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/insertPerciRainBalanceByDay
    InsertPerciRainBalanceByDay($db);
} else if ($param1 == 'insertPerciRainBalanceMonthAgo') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/insertPerciRainBalanceMonthAgo
    InsertPerciRainBalanceMonthAgo($db);
} else if ($param1 == 'insertPerciRainBalanceByDayPart3') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/insertPerciRainBalanceByDayPart3
    InsertPerciRainBalanceByDayPart3($db);
} else if ($param1 == 'getPerciCumulativeRainByYear') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciCumulativeRainByYear/2020/2021
    getPerciCumulativeRainByYear($db, $param2, $param3);
} else if ($param1 == 'getPerciFrequencyEventsByYear') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciFrequencyEventsByYear/2020/2021/B1/1
    getPerciFrequencyEventsByYear($db, $param2, $param3, $param4, $param5);
} else if ($param1 == 'getPerciDryPeriod') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciDryPeriod/2021-05-22/2021-05-28/.99
    getPerciDryPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getPerciWetPeriod') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPerciWetPeriod/2021-05-22/2021-05-28/.99
    getPerciWetPeriod($db, $param2, $param3, $param4);
} else if ($param1 == 'getPreciDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPreciDailyOverviewByDate/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getPreciDailyOverviewByDate($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getPreciDailyOverviewByMonth') {
    // http://localhost:4200/meteo/api/percipitationpageCtrl.php/getPreciDailyOverviewByMonth/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getPreciDailyOverviewByMonth($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
}

//else if ($param1 == 'test') {
////http://localhost:4200/meteo/api/dashboardCompCtrl.php/test/0150/2021-05-14 
//    test($db, $param3);
//}
// http://localhost:4200/meteo/api/percipitationpageCtrl.php/getprecidailyoverview/0098/2021-05-22/2021-05-28 
function getprecidailyoverview($db, $param3, $param4) {
    $items = new ChannelPerciDataPage($db);
    $perciAmountStmt = $items->getPerciAmountByDays($param3, $param4);

    //Find out maximum of Perci Amount
    $stmtperciAmountMaxStmt = $items->getlastPerciAmountMaxDays($param3, $param4);
    $rowperciAmountMaxRow = $stmtperciAmountMaxStmt->fetch(PDO::FETCH_ASSOC);
    $maxperciAmountMax = $rowperciAmountMaxRow['maxperciamount'];
    $itemCount = $perciAmountStmt->rowCount();


    $perciLengthArry = $items->finPerciLengthByDays($param3, $param4);
    // Find out the maximum of all values
    $maxperciLength = $items->finPerciLengthByDays($param3, $param4);
    $maxperciLengthValue = -1;
    for ($i = 0; $i < count($maxperciLength); $i++) {
        $maxpercivalue = $maxperciLength[$i];
        $totalvalue = $maxpercivalue['totalvalue'];
        if ($totalvalue > $maxperciLengthValue) {
            $maxperciLengthValue = $totalvalue;
        }
    }

    if ($itemCount > 0) {
        $int = 0;
        $tempArr = array();
        while ($row = $perciAmountStmt->fetch(PDO::FETCH_ASSOC)) {
            $percivalue = $perciLengthArry[$int] ?? 0;
            $totalvalue = $percivalue['totalvalue'] ?? 0;
            $tempArr[] = array(
                'percidate' => $row['valuedate'],
                'perciamount' => $row['perciamount'],
                'perciLength' => $percivalue['total'] ?? 0,
                'perciLengthValue' => $totalvalue,
                'maxperciLengthValue' => $maxperciLengthValue,
                'maxperciamount' => $maxperciAmountMax
            );
            $int = $int + 1;
        }
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
    echo json_encode($tempArr);
}

function getPreciTypeByDate($db, $param3, $param4) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPreciTypeByDate($param3, $param4);
    echo json_encode($tempArr);
}

function getMinAvailableDates($db, $param2) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getMinAvailableDates($param2);
    echo json_encode($tempArr);
}

function getPerciCompAginstNorm($db, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciCompAginstNorm($param3);
    echo json_encode($tempArr);
}

function getPerciCompAginstNormYear($db, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciCompAginstNormYear($param3);
    echo json_encode($tempArr);
}

function getPreciDifferentPeriods($db, $param3) {
    $tempArr = array();
    $items = new ChannelPerciDataPage($db);
    $stmtLastPerci = $items->findLastPrecipitation($param3);
    $rowLastPerci = $stmtLastPerci->fetch(PDO::FETCH_ASSOC);

    $seconds = $rowLastPerci['diff'];
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$seconds");
    $diff = $dt1->diff($dt2)->format('%a day, %h hr, %i min');
    $diff = $diff . ' (' . ($rowLastPerci['lastpercitime']) . ')';

    $stmtPerciTweek = $items->findPrecipitationThisWeek();
    $rowPerciTweek = $stmtPerciTweek->fetch(PDO::FETCH_ASSOC);

    $stmtPerciLast7Days = $items->findPrecipitationByNoOfDays('-7');
    $rowPerciLast7Days = $stmtPerciLast7Days->fetch(PDO::FETCH_ASSOC);

    $stmtPerciThisMonth = $items->findPrecipitationThisMonth();
    $rowPerciThisMonth = $stmtPerciThisMonth->fetch(PDO::FETCH_ASSOC);

    $stmtPerciLast31Days = $items->findPrecipitationByNoOfDays('-31');
    $rowPerciLast31Days = $stmtPerciLast31Days->fetch(PDO::FETCH_ASSOC);

    $stmtPerciThisYear = $items->findPrecipitationThisYear();
    $rowPerciThisYear = $stmtPerciThisYear->fetch(PDO::FETCH_ASSOC);

    $tempArr[] = array(
        'datepersetime' => $rowLastPerci['datepersetime'],
        'lastPerci' => $diff,
        'perciThisWeek' => $rowPerciTweek['perciTweek'],
        'perciLast7Days' => $rowPerciLast7Days['perciLastXDays'],
        'perciThisMonth' => $rowPerciThisMonth['perciThisMonth'],
        'perciLast31Days' => $rowPerciLast31Days['perciLastXDays'],
        'perciThisYear' => $rowPerciThisYear['perciThisYear']
    );
    echo json_encode($tempArr);
}

function getPreciEventBuckets($db, $param3) {
    $countArr = array();
    $tempArr = array();
    $i = 0;
    $items = new ChannelPerciDataPage($db);
    $tempArr[$i] = $items->findPrecipitationBucketsSpecial();
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;
    $tempArr[$i] = $items->findPrecipitationBuckets(0.03, 1.00);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findPrecipitationBuckets(1.00, 5.00);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findPrecipitationBuckets(5.00, 10.00);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findPrecipitationBuckets(10.00, 20.00);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findPrecipitationBuckets(20.00, 30.00);
    $tempArr[$i] = $tempArr[$i] != null ? $tempArr[$i] : array(0);
    $countArr[$i] = count($tempArr[$i]);
    $i = $i + 1;

    $tempArr[$i] = $items->findPrecipitationBuckets(30.00, 4000.00);
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

    $valueArr[] = array(
        'maxEvents' => $maximum,
        'range01and03' => $tempArr[0],
        'range03and01' => $tempArr[1],
        'range1and5' => $tempArr[2],
        'range5and10' => $tempArr[3],
        'range10and20' => $tempArr[4],
        'range20and30' => $tempArr[5],
        'range30above' => $tempArr[6]
    );
    echo json_encode($valueArr);
}

function getPrecipitationMonthComapre($db, $param4) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->findPrecipitationMonthComapre($param4);

    echo json_encode(array($tempArr));
}

function getPrecipitationYearComapre($db) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->findPrecipitationYearComapre();
    echo json_encode($tempArr);
}

function getPerciRainBalance($db, $param2, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciRainBalanceValues($param2, $param3);
    echo json_encode($tempArr);
}

function getPerciRainBalanceMonthAgo($db, $param2, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciRainBalanceMonthAgoValue($param2, $param3);
    echo json_encode($tempArr);
}

//Chart
function getPerciRainBalanceByDay($db, $param2, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciRainBalanceByDay($param2, $param3);
    echo json_encode($tempArr);
}

// Insert into table
function InsertPerciRainBalanceByDay($db) {
    $items = new ChannelPerciDataPage($db);
    $date = date('Y-m-d');
    $items->InsertPerciRainBalanceByDay($date, '30');
    $items->InsertPerciRainBalanceByDay($date, '60');
}

// Insert into table
function InsertPerciRainBalanceMonthAgo($db) {
    $items = new ChannelPerciDataPage($db);
    $date = date('Y-m-d');
    $items->InsertPerciRainBalanceByDay($date, '90');
    $items->InsertPerciRainBalanceByDay($date, '121');
}

function InsertPerciRainBalanceByDayPart3($db) {
    $items = new ChannelPerciDataPage($db);
    $date = date('Y-m-d');
    $items->InsertPerciRainBalanceByDay($date, '182');
    $items->InsertPerciRainBalanceByDay($date, '273');
    $items->InsertPerciRainBalanceByDay($date, '365');
}

function getPerciCumulativeRainByYear($db, $param2, $param3) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciCumulativeRainByYear($param2, $param3);
    echo json_encode($tempArr);
}

// $param2: FromYear , $param3 ToYear, $param4 Bucket,$param5 values,  
function getPerciFrequencyEventsByYear($db, $param2, $param3, $param4, $param5) {
    $items = new ChannelPerciDataPage($db);
    $operator = 'GT';
    if ($param4 == 'B1' || $param4 == 'B2' || $param4 == 'B3') {
        $operator = 'LT';
    }
    $tempArr = $items->getPerciFrequencyEventsByYear($param2, $param3, $operator, $param5);
    echo json_encode($tempArr);
}

function getPerciDryPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciDryPeriod($param2, $param3, $param4);


    usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });

    echo json_encode($tempArr);
}


function getPerciWetPeriod($db, $param2, $param3, $param4) {
    $items = new ChannelPerciDataPage($db);
    $tempArr = $items->getPerciWetPeriod($param2, $param3, $param4);
    
     usort($tempArr, function ($first, $second) {
        if ($first['countvalue'] === $second['countvalue']) {
            return 0;
        }
        return $first['countvalue'] > $second['countvalue'] ? -1 : 1;
    });
    
    echo json_encode($tempArr);
}

function getPreciDailyOverviewByDate($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelPerciDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getPreciDailyOverviewByDatesParam($param2, $param3, $param4, $datelist);    
    echo json_encode($tempArr);
}

function getPreciDailyOverviewByMonth($db, $param2, $param3, $param4, $datelist) {
    $items = new ChannelPerciDataPage($db);
    // Default: D.  Year and Month: YM . Date X to Date Y: XY. Multiple compare: C
    $tempArr = $items->getPreciDailyOverviewByMonthParam($param2, $param3, $param4, $datelist);
        
//    echo "<pre>";
//    print_r($tempArr);
   echo json_encode($tempArr);
}
