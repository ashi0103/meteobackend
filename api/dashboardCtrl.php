<?php

header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
header('Access-Control-Allow-Origin: *');

include('../api/config/database.php');
include('../api/class/ChannelData.php');
include('../api/class/ChannelPerciData.php');
include('../api/class/ChannelSunshineData.php');
include('../api/class/ChannelGblRadData.php');
include('../api/class/ChannelDataDashWindComp.php');
include('../api/class/ChannelPerciRecordData.php');
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
//http://localhost:4200/meteo/api/dashboardCtrl.php/gettodayalltemp/todaydate
// fetching database connection
//https://maennedorf.000webhostapp.com/meteo/api/dashboardCtrl.php/getlast7daysdata/0130
$database = new Database();
$db = $database->getConnection();
if ($param1 == 'gettempcomp') {
    getTempChartTodayAllValue($db);
} else if ($param1 == 'getlast7daysdata') {
//http://localhost:4200/meteo/api/dashboardCtrl.php/getlast7daysdata/0130   
    getlast7daystempData($db, $param2);
} else if ($param1 == 'gettodayalldata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/gettodayalldata/0130/2021-02-02 
    getChartTodayAllValue($db, $param2, $param3);
} else if ($param1 == 'get24hralldata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/get24hralldata/0130/2021-02-02/12 
    get24HrAllData($db, $param2, $param3, $param4); //value
}else if ($param1 == 'get24HrAllDataCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/get24HrAllDataCustom/0130/2021-02-02/2021-02-04 
    get24HrAllDataCustom($db, $param2, $param3, $param4); //value
}
else if ($param1 == 'gettodayavghourlydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/gettodayavghourlydata/0130/2021-02-02
    getTodayAvgHrData($db, $param2, $param3);
} else if ($param1 == 'get24hravghourlydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/get24hravghourlydata/0130/2021-02-02/12
    get24HrAvgHrData($db, $param2, $param3, $param4);
}else if ($param1 == 'getAvgHrDataCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getAvgHrDataCustom/0130/2021-02-02/2021-02-02
    getAvgHrDataCustom($db, $param2, $param3, $param4);
} 
else if ($param1 == 'get1Weekavghourlydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/get1Weekavghourlydata/0130
    get1WeekAvgHrData($db, $param2);
} else if ($param1 == 'getThisMonthAvgHourlydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getThisMonthAvgHourlydata/0130
    getThisMonthAvgHrData($db, $param2);
} else if ($param1 == 'getthismonthavgdailydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getthismonthavgdailydata/0130
    getThisMonthAvgDayData($db, $param2);
} else if ($param1 == 'getlast1monthavgdailydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getlast1monthavgdailydata/0130
    getLast1MonthAvgDayData($db, $param2);
} else if ($param1 == 'getThisYearAvgDaydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getThisYearAvgDaydata/0130
    getThisYearAvgDayData($db, $param2);
} else if ($param1 == 'getYearAvgDaydata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getYearAvgDaydata/0130/2021-02-02/14
    getYearAvgDayData($db, $param2, $param4); //daysvalue
}else if ($param1 == 'getYearAvgDayDataCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getYearAvgDayDataCustom/0130/2021-02-02/2021-02-02
    getYearAvgDayDataCustom($db, $param2, $param3 ,$param4); //daysvalue
}
else if ($param1 == 'getThisYearAvgMonthdata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getThisYearAvgMonthdata/0130
    getThisYearAvgMonthData($db, $param2);
} else if ($param1 == 'getYearsAvgMonthsdata') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getYearsAvgMonthsdata/0130/2021-02-02/12
    getYearsAvgMonthsData($db, $param2, $param4); //monthvalues
}else if ($param1 == 'getYearsAvgMonthsdatacustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getYearsAvgMonthsdatacustom/0130/2021-02-02/2021-06-06
    getYearsAvgMonthsdatacustom($db, $param2,$param3, $param4); //monthvalues
}
else if ($param1 == 'getwindchartcompdefault') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getwindchartcompdefault
    getwindchartcompdefault($db); //monthvalues
} else if ($param1 == 'getPercichartcompdefault') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getPercichartcompdefault
    getPercichartcompdefault($db); //monthvalues
} else if ($param1 == 'getPerciRecordcompdefault') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getPerciRecordcompdefault
    getPerciIntensityCompDefault($db); //monthvalues
} else if ($param1 == 'getSunshinecompdefault') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getSunshinecompdefault
    getSunshinecompdefault($db); //monthvalues
} else if ($param1 == 'getGblRadiationcompdefault') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getGblRadiationcompdefault
    getGblRadiationcompdefault($db); //monthvalues
} else if ($param1 == 'getWindDirectionTodayAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionTodayAvgHrData/0130/2021-02-02 
    getWindDirectionTodayAvgHrData($db, $param2, $param3);
} else if ($param1 == 'getWindDirection24HrAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirection24HrAvgHrData/0130/2021-02-02/14 
    getWindDirection24HrAvgHrData($db, $param2, $param3, $param4);
} else if ($param1 == 'getWindDirectionAvgHrDataCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionAvgHrDataCustom/0130/2021-02-02/2021-02-02 
    getWindDirectionAvgHrDataCustom($db, $param2, $param3, $param4);
}else if ($param1 == 'getWindDirection1WeekAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirection1WeekAvgHrData/0114/2021-02-02/7 
    getWindDirection1WeekAvgHrData($db, $param2, $param3, $param4);
} else if ($param1 == 'getWindDirectionThisMonthAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionThisMonthAvgHrData/0114 
    getWindDirectionThisMonthAvgHrData($db, $param2);
} else if ($param1 == 'getWindDirection1WeekAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirection1WeekAvgDayData/0114 
    getWindDirection1WeekAvgDayData($db, $param2);
} else if ($param1 == 'getWindDirectionLast1MonthAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionLast1MonthAvgDayData/0114 
    getWindDirectionLast1MonthAvgDayData($db, $param2);
} else if ($param1 == 'getWindDirectionThisMonthAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionThisMonthAvgDayData/0114 
    getWindDirectionThisMonthAvgDayData($db, $param2);
} else if ($param1 == 'getWindDirectionThisYearAvgDayData') {
    ////http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionThisYearAvgDayData/0114  
    getWindDirectionThisYearAvgDayData($db, $param2);
} else if ($param1 == 'getWindDirectionYearAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionYearAvgDayData/0114/2021-02-02/2021-02-02  
    getWindDirectionYearAvgDayData($db, $param2,$param3, $param4);
} else if ($param1 == 'getWindDirectionThisYearAvgMonthData') {
    ////http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionThisYearAvgMonthData/0114  
    getWindDirectionThisYearAvgMonthData($db, $param2);
} else if ($param1 == 'getWindDirectionYearsAvgMonthsData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getWindDirectionYearsAvgMonthsData/0114/2021-02-02/2021-02-02  
    getWindDirectionYearsAvgMonthsData($db, $param2, $param3,$param4);
}
// **** Cummulative Percipitation data ***
else if ($param1 == 'getCumulativePerciTodayAllData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciTodayAllData/0101/2021-02-02  
    getCumulativePerciTodayAllData($db, $param2, $param3);
} else if ($param1 == 'getCumulativePerciLast24HrAllData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciLast24HrAllData/0101/2021-02-02/24  
    getCumulativePerciLast24HrAllData($db, $param2, $param4);
}else if ($param1 == 'getCumulativePerciAllDataCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciAllDataCustom/0101/2021-02-02/2021-02-02  
    getCumulativePerciAllDataCustom($db, $param3, $param4);
}  
else if ($param1 == 'getCumulativePerciTodayAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciTodayAvgHrData/0101/2021-02-02  
    getCumulativePerciTodayAvgHrData($db, $param2, $param3);
} else if ($param1 == 'getCumulativePerciLast24AvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciLast24AvgHrData/0101/2021-02-02/24  
    getCumulativePerciLast24AvgHrData($db, $param2);
}else if ($param1 == 'getCumulativePerciAvgHrCustom') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciAvgHrCustom/0101/2021-02-02/2021-02-02  
    getCumulativePerciAvgHrCustom($db, $param3, $param4);
} 
else if ($param1 == 'getCumulativePerciLast1WeekAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciLast1WeekAvgHrData/0101/2021-02-02/2  
    getCumulativePerciLast1WeekAvgHrData($db, $param2, $param4);
} else if ($param1 == 'getCumulativePerciThisMonthAvgHrData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciThisMonthAvgHrData/0101/2021-02-02
    getCumulativePerciThisMonthAvgHrData($db, $param2);
} else if ($param1 == 'getCumulativePerci1WeekAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerci1WeekAvgDayData/0101/2021-02-02/7  
    getCumulativePerci1WeekAvgDayData($db, $param2, $param4);
} else if ($param1 == 'getCumulativePerciLast1MonthAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciLast1MonthAvgDayData/0101  
    getCumulativePerciLast1MonthAvgDayData($db, $param2);
} else if ($param1 == 'getCumulativePerciThisMonthAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciThisMonthAvgDayData/0101/2021-02-02  
    getCumulativePerciThisMonthAvgDayData($db, $param2);
} else if ($param1 == 'getCumulativePerciThisYearAvgDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciThisYearAvgDayData/0101 
    getCumulativePerciThisYearAvgDayData($db, $param2);
} else if ($param1 == 'getCumulativePerciAvgCustomDayData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciAvgCustomDayData/0101/2021-02-02/2021-02-02  
    getCumulativePerciAvgCustomDayData($db, $param3, $param4);
} else if ($param1 == 'getCumulativePerciThisYearAvgMonthData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciThisYearAvgMonthData/0101/2021-02-02  
    getCumulativePerciThisYearAvgMonthData($db, $param2, $param3);
} else if ($param1 == 'getCumulativePerciYearCustomAvgMonthData') {
    //http://localhost:4200/meteo/api/dashboardCtrl.php/getCumulativePerciYearCustomAvgMonthData/0101/2021-02-02/2021-02-02  
    getCumulativePerciYearCustomAvgMonthData($db, $param3, $param4);
} else if ($param1 == 'test') {
//http://localhost:4200/meteo/api/dashboardCtrl.php/test/0150/2021-05-14 
    test($db, $param3);
}

function test($db, $param3) {
    $items = new ChannelSunshineData($db);
    $items->getlast7DaysFirstLastSunshine();
}

//Starting functions from here
/**
 *
 * @param type $db
 */

/**
 * http://localhost:4200/meteo/api/dashboardCtrl.php/getlast7daystemp/0130
 * @param type $db
 */
function getlast7daystempData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getlast7daysData($param2);
    $allMinMaxstmt = $items->getlast7daysAllMinMaxData($param2);
    $allMinMaxRow = $allMinMaxstmt->fetch(PDO::FETCH_ASSOC);
    $allMin = $allMinMaxRow['allmin'];
    $allMax = $allMinMaxRow['allmax'];

    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "days" => $days,
                "currtemp" => $curtemp,
                "mintemp" => $mintemp,
                "maxtemp" => $maxtemp,
                "avgtemp" => $avgtemp,
                "allmax" => $allMax,
                "allmin" => $allMin
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

/**
 * 
 * @param type $db
 * @param type $param2
 * @param type $param3
 */
function getChartTodayAllValue($db, $param2, $param3) {
    $items = new ChannelData($db);
    $stmt = $items->getChartTodayAllValue($param2, $param3);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "valuetime" => $valuetime,
                "value" => $value,
                "avgDirection" => getWindirection($value, $param2, $db)
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
    $items = new ChannelData($db);
    $stmt = $items->get24HrAllData($param2, $param3, $param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $value,
                "avgDirection" => getWindirection($value, $param2, $db)
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

function get24HrAllDataCustom($db, $param2, $param3, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->get24HrAllDataCustom($param2, $param3, $param4);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $e = array(
                "valuedate" => $valuedate,
                "value" => $value,
                "avgDirection" => getWindirection($value, $param2, $db)
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

function getTodayAvgHrData($db, $param2, $param3) {
    $items = new ChannelData($db);
    $stmt = $items->getTodayAvgHrData($param2, $param3);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function get24HrAvgHrData($db, $param2, $param3, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->get24HrAvgHrData($param2, $param3, $param4,);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}


function getAvgHrDataCustom($db, $param2, $param3, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->getAvgHrDataCustom($param2, $param3, $param4);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function get1WeekAvgHrData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->get1WeekAvgHrData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getThisMonthAvgHrData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getThisMonthAvgHrData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getThisMonthAvgDayData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getThisMonthAvgDayData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getLast1MonthAvgDayData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getLast1MonthAvgDayData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getThisYearAvgDayData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getThisYearAvgDayData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getYearAvgDayData($db, $param2, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->getYearAvgDayData($param2, $param4);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getYearAvgDayDataCustom($db, $param2,$param3, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->getYearAvgDayDataCustom($param2,$param3, $param4);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getThisYearAvgMonthData($db, $param2) {
    $items = new ChannelData($db);
    $stmt = $items->getThisYearAvgMonthData($param2);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getYearsAvgMonthsData($db, $param2, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->getYearsAvgMonthsData($param2, $param4);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getYearsAvgMonthsdatacustom($db, $param2, $param3, $param4) {
    $items = new ChannelData($db);
    $stmt = $items->getYearsAvgMonthsDataCustom($param2, $param3, $param4);
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
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}


function getwindchartcompdefault($db) {
    $items = new ChannelData($db);
    $param1 = "1112";
    $stmt = $items->getlast7daysDataWind($param1);
    $stmtMinMax = $items->getlast7daysAllWindMinMaxData($param1);

    $rowMinMax = $stmtMinMax->fetch(PDO::FETCH_ASSOC);
    $windSpeedMax = $rowMinMax['allmax'];
    $windSpeedMaxAvg = $rowMinMax['allmaxavg'];

    $param2 = "1110";
    $stmt1 = $items->getlast7daysDataWind($param2);
    $stmtMaxGust = $items->getlast7daysAllWindMinMaxData($param2);
    $rowMaxGust = $stmtMaxGust->fetch(PDO::FETCH_ASSOC);
    $windSpeedGustMax = $rowMaxGust['allmax'];


    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $tempArr[] = array(
                'days' => $row['days'],
                'curwind' => $row['curtemp'],
                'minwind' => $row['mintemp'],
                'maxwind' => $row['maxtemp'],
                'avgwind' => $row['avgtemp'],
                'gustdays' => $row1['days'],
                'maxwindgust' => $row1['maxtemp'],
                'allmax' => $windSpeedMax,
                'allmaxavg' => $windSpeedMaxAvg,
                'allgustmax' => $windSpeedGustMax
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

function getPercichartcompdefault($db) {
    $items = new ChannelPerciData($db);
    $perciRecordData = new ChannelPerciRecordData($db);
    $param1 = "0101";
    $stmt = $items->getlast7DaysperciAmount($param1);

    $stmtperciAmountMax = $items->getlast7DaysperciAmountMax($param1);
    $rowperciAmountMax = $stmtperciAmountMax->fetch(PDO::FETCH_ASSOC);
    $maxperciAmountMax = $rowperciAmountMax['maxperciamount'];
    $itemCount = $stmt->rowCount();
    
    $param3 = "0098";  
    $arr7dayperciType = $perciRecordData->getlast7DaysperciType($param3);
    
    // Passed 0098
    $perciLength = $items->find7DaysPerciLength();
    
    $maxperciLength = $items->find7DaysPerciLength();
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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $PerciType = $arr7dayperciType[$int]??'';
            $percivalue = $perciLength[$int];
            $totalvalue = $percivalue['totalvalue'];
            $tempArr[] = array(
                'valuedate' => $row['valuedate'],
                'perciamount' => $row['perciamount'],
                'perciLength' => $percivalue['total'],
                'perciLengthValue' => $totalvalue,
                'maxperciLengthValue' => $maxperciLengthValue,
                'perciType' => $PerciType['value']??'',
                'perciTypeText' => $PerciType['valuetxt']??'',
                'maxperciamount' => $maxperciAmountMax                    
            );
            $int = $int + 1;
        }
        echo json_encode($tempArr);
    } else {
        http_response_code(404);
        echo json_encode(
                array("message" => "No record found.")
        );
    }
}

function getPerciIntensityCompDefault($db) {
    $items = new ChannelPerciRecordData($db);
    //$param1 = "0103";
    $Arr7days10min = $items->getlast7Daysperci10min();      
    //$param2 = "0102";
    $Arr7days1hr = $items->getlast7Daysperci1Hrs();  
    
    
//    $param3 = "0098";    
//    $stmt = $items->getlast7DaysperciType($param3);
    
    $itemCount = count($Arr7days10min);        
    if ($itemCount > 0) {
        $tempArr = array();                  
        for ($i = 0; $i < count($Arr7days10min); $i++){
        $value10Min = $Arr7days10min[$i];
        $value1hr = $Arr7days1hr[$i];
         //$row = $stmt->fetch(PDO::FETCH_ASSOC); 
          
//       echo "<post>";
//        echo "<pre>";
//        print_r($value1hr['percimax1hr']);
//       echo "<post>";
            $tempArr[] = array(
                'valuedate' => $value10Min['tdate'],
                'perci10mins' => number_format($value10Min['percimax10mins'],2),
                'perci1Hrs' => number_format($value1hr['percimax1hr'],2),
                //'perciType' => $row['perciType']??''
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

function getSunshinecompdefault($db) {
    $items = new ChannelSunshineData($db);
    $param1 = "0124";
    $stmt = $items->getlast7DaySunhineDuration($param1);
    $sunFirstLastArr = $items->getlast7DaysFirstLastSunshine();
    $itemCount = $stmt->rowCount();
                 
    $i = 0;
    if ($itemCount > 0) {
        $tempArr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $util = new UtilCommon;
            $sunFirstLastValues = $sunFirstLastArr[$i];
            $i = $i + 1;
            $tempArr[] = array(
                'valuedate' => $row['valuedate'],
                'sunshineDurationHrMin' => $util->MinsToHrsMin($row['sunshineDuration']),
                'sunshineDuration' => number_format($row['sunshineDuration']/60,2),
                'firstSunshine' => $sunFirstLastValues['firstSunshine'],
                'lastSunshine' => $sunFirstLastValues['lastSunshine']
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

function getGblRadiationcompdefault($db) {
    $items = new ChannelGblRadData($db);
    $param1 = "0120";
    $stmt = $items->getlast7DaysAvgGblRad($param1);
    $param2 = "0120";
    $stmt1 = $items->getlast7DaysMaxGblRad($param2);
    $itemCount = $stmt->rowCount();
    if ($itemCount > 0) {
        $tempArr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $tempArr[] = array(
                'valuedate' => $row['valuedate'],
                'avggblradiation' => $row['avggblradiation'],
                'maxgblradiation' => $row1['maxgblradiation']
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

/**
 *  @param type $db
 * @param type $param2
 * @param type $param3
 */
function getWindDirectionTodayAvgHrData($db, $param2, $param3) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionTodayAvgHrData($param2, $param3);
}

/**
 *  @param type $db
 * @param type $param2
 * @param type $param3
 */
function getWindDirection24HrAvgHrData($db, $param2, $param3, $param4) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirection24HrAvgHrData($param2, $param3, $param4,false);
}

function getWindDirectionAvgHrDataCustom($db, $param2, $param3, $param4) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirection24HrAvgHrData($param2, $param3, $param4,true);
}


/**
 *  @param type $db
 * @param type $param2
 * @param type $param3
 */
function getWindDirection1WeekAvgHrData($db, $param2, $param3, $param4) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirection1WeekAvgHrData($param2, $param3, $param4);
}

function getWindDirectionThisMonthAvgHrData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionThisMonthAvgHrData($param2);
}

function getWindDirection1WeekAvgDayData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirection1WeekAvgDayData($param2);
}

function getWindDirectionLast1MonthAvgDayData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionLast1MonthAvgDayData($param2);
}

function getWindDirectionThisMonthAvgDayData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionThisMonthAvgDayData($param2);
}

function getWindDirectionThisYearAvgDayData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionThisYearAvgDayData($param2);
}

function getWindDirectionYearAvgDayData($db, $param2, $param3, $param4) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionYearAvgDayData($param2, $param3, $param4);
}

function getWindDirectionYearsAvgMonthsData($db, $param2, $param3,$param4) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionYearsAvgMonthsData($param2, $param3,$param4);
}

function getWindDirectionThisYearAvgMonthData($db, $param2) {
    $items = new ChannelDataDashWindComp($db);
    $items->getWindDirectionThisYearAvgMonthData($param2);
}

function getWindirection($degree, $param2, $db) {
    $direction = '';
    if ($param2 == '0114') {
        $items = new ChannelDataDashWindComp($db);
        $direction = $items->getWindDirection($degree);
    }
    return $direction;
}

function getCumulativePerciTodayAllData($db, $param2, $param3) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciTodayAllData($param2, $param3);
}

function getCumulativePerciLast24HrAllData($db, $param2, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciLast24HrAllData($param2, $param4);
}

function getCumulativePerciAllDataCustom($db, $param3, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciAllDataCustom($param3, $param4);
}

function getCumulativePerciTodayAvgHrData($db, $param2, $param3) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciTodayAvgHrData($param2, $param3);
}

function getCumulativePerciLast24AvgHrData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciLast24AvgHrData($param2);
}

function getCumulativePerciAvgHrCustom($db, $param3, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciAvgHrCustom($param3, $param4);
}


function getCumulativePerciLast1WeekAvgHrData($db, $param2, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciLast1WeekAvgHrData($param2, $param4);
}

function getCumulativePerciThisMonthAvgHrData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciThisMonthAvgHrData($param2);
}

function getCumulativePerci1WeekAvgDayData($db, $param2, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerci1WeekAvgDayData($param2, $param4);
}

function getCumulativePerciLast1MonthAvgDayData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciLast1MonthAvgDayData($param2);
}

function getCumulativePerciThisMonthAvgDayData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciThisMonthAvgDayData($param2);
}

function getCumulativePerciThisYearAvgDayData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciThisYearAvgDayData($param2);
}

function getCumulativePerciAvgCustomDayData($db,$param3, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciAvgCustomDayData($param3, $param4);
}

function getCumulativePerciThisYearAvgMonthData($db, $param2) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciThisYearAvgMonthData($param2);
}

function getCumulativePerciYearCustomAvgMonthData($db,$param3, $param4) {
    $items = new ChannelPerciData($db);
    $items->getCumulativePerciYearCustomAvgMonthData($param3, $param4);
}
