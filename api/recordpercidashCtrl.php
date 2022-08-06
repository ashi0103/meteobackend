<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelPerciRecordData.php');
include('../api/class/ChannelPerciData.php');



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

if ($param1 == 'getPerciRecordcompdefault') {
//http://localhost:4200/meteo/api/recordpercidashCtrl.php/getPerciRecordcompdefault
    getPerciIntensityCompDefault($db);
} else if ($param1 == 'gettodayalldata') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/gettodayalldata/0100/2021-05-28 
    getChartTodayAllValue($db, $param2, $param3);
} else if ($param1 == 'get24hralldata') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/get24hralldata/0100/2021-02-02/12 
    get24HrAllData($db, $param2, $param3, $param4); //value
}
else if ($param1 == 'getalldatacustom') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getalldatacustom/0100/2021-02-02/2021-02-02 
    getAllDataCustom($db, $param2, $param3, $param4); //value
}
else if ($param1 == 'getHourlyTodayMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getHourlyTodayMaxData/0100/2021-02-02
    getHourlyTodayMaxData($db, $param3);
}else if ($param1 == 'getHourly24HrMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getHourly24HrMaxData/0100/2021-02-02/24
    getHourly24HrMaxData($db, $param3,$param4);
}else if ($param1 == 'getHourlyMaxDataCustom') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getHourlyMaxDataCustom/0100/2021-02-02/2021-02-02
    getHourly24HrMaxData($db, $param3,$param4);
}
else if ($param1 == 'getHourly1WeekMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getHourly1WeekMaxData/0100/2021-02-02
    getHourly1WeekMaxData($db, $param3);
}
else if ($param1 == 'getHourlyTmonthMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getHourlyTmonthMaxData/0100/2021-02-02
    getHourlyTmonthMaxData($db, $param3);
}
else if ($param1 == 'getDaily1WeekMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getDaily1WeekMaxData/0100/2021-02-02
    getDaily1WeekMaxData($db, $param3);
}else if ($param1 == 'getDaily1MonthMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getDaily1MonthMaxData/0100/2021-02-02
    getDaily1MonthMaxData($db, $param3);
}
else if ($param1 == 'getDailyTMonthMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getDailyTMonthMaxData/0100/2021-02-02
    getDailyTMonthMaxData($db, $param3);
}else if ($param1 == 'getDailyTYearMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getDailyTYearMaxData/0100/2021-02-02
    getDailyTYearMaxData($db, $param3);
}
else if ($param1 == 'getMonthlyTYearMaxData') {
    //http://localhost:4200/meteo/api/recordpercidashCtrl.php/getMonthlyTYearMaxData/0100/2021-02-02
    getMonthlyTYearMaxData($db, $param3);
}



//else if ($param1 == 'test') {
////http://localhost:4200/meteo/api/dashboardCompCtrl.php/test/0150/2021-05-14 
//    test($db, $param3);
//}




function getPerciIntensityCompDefault($db) {
    $items = new ChannelPerciRecordData($db);
    $percidata = new ChannelPerciData($db);
    //$param1 = "0103";
    $Arr7days10min = $items->getlast7Daysperci10min();
    //$param2 = "0102";
    $Arr7days1hr = $items->getlast7Daysperci1Hrs();
    
    $param2 = "0100";
    $stmt1 = $percidata->getlast7DaysperciIntensity($param2);
    $stmtIntensityMax = $percidata->getlast7DaysperciIntensityMax($param2);
    $rowIntensityMax = $stmtIntensityMax->fetch(PDO::FETCH_ASSOC);
    $maxperciintensity = $rowIntensityMax['maxperciintensity'];
    
    
    $itemCount = count($Arr7days10min);
    if ($itemCount > 0) {
        $tempArr = array();
        for ($i = 0; $i < count($Arr7days10min); $i++) {
            $value10Min = $Arr7days10min[$i];
            $value1hr = $Arr7days1hr[$i];            
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

            $tempArr[] = array(
                'valuedate' => $value10Min['tdate'],
                'perci10mins' => number_format($value10Min['percimax10mins'], 2),
                'perci1Hrs' => number_format($value1hr['percimax1hr'], 2),
                'perciIntensityMax' => $row1['perciIntensityMax'],
                'maxperciintensity' => $maxperciintensity,
            );
        }
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getChartTodayAllValue($db, $param2, $param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getChartTodayAllValue($param2, $param3);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "valuetime" => $valuetime,
                "value" => $value
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

function get24HrAllData($db, $param2, $param3, $param4) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->get24HrAllData($param2, $param3, $param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $value,
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

function getAllDataCustom($db, $param2, $param3, $param4) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getAllDataCustom($param2, $param3, $param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $value,
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



function getHourlyTodayMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getHourlyTodayMaxData($param3);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getHourly24HrMaxData($db,$param3,$param4) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getHourly24HrMaxData($param3,$param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getHourlyMaxDataCustom($db,$param3,$param4) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getHourlyMaxDataCustom($param3,$param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getHourly1WeekMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getHourly1WeekMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getHourlyTmonthMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getHourlyTmonthMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getDaily1WeekMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getDaily1WeekMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getDaily1MonthMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getDaily1MonthMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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
function getDailyTMonthMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getDailyTMonthMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getDailyTYearMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getDailyTYearMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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

function getMonthlyTYearMaxData($db,$param3) {
    $items = new ChannelPerciRecordData($db);
    $stmt = $items->getMonthlyTYearMaxData();
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $maxperciInten,
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