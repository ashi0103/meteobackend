<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include_once('../api/class/UtilCommon.php');
include('../api/config/database.php');
include('../api/class/ChannelExtremesDataPage.php');

/** @var type $requestMethod */
//$requestMethod = $_SERVER["REQUEST_METHOD"];
$pathinfo = $_SERVER['PATH_INFO'];
$called = explode('/', trim($_SERVER['PATH_INFO'], '/'));

$param1; // Get method 
$param2; // Channel Value
$param3; //  Date Or From Date
$param4; //  value Or some values
$param5; //  value Or some values
$param6; //  value Or some values
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
} else if (count($called) == 6) {
    $param1 = $called[0];
    $param2 = $called[1];
    $param3 = $called[2];
    $param4 = $called[3];
    $param5 = $called[4];
    $param6 = $called[6];
} else {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad Request");
    die();
}

$database = new Database();
$db = $database->getConnection();

if ($param1 == 'getpreciextreme') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getpreciextreme/period/max/data 
    getpreciextremeDayRain($db, $param2, $param3, $param4, $param5, $param6);
} else if ($param1 == 'calcperciextremeHrPart1') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/calcperciextremeHrPart1/2021-02-01/2021-02-05 
    calcperciextremeHrpart1($db, $param2, $param3);
} else if ($param1 == 'calcperciextremeHrPart2') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/calcperciextremeHrPart2/2021-02-01/2021-02-05 
    calcperciextremeHrpart2($db, $param2, $param3);
} else if ($param1 == 'calcperciextremeDayPart1') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/calcperciextremeDayPart1/2021-02-01/2021-02-05 
    calcperciextremeDayPart1($db, $param2, $param3);
} else if ($param1 == 'calcperciextremeDayPart2') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/calcperciextremeDayPart2/2021-02-01/2021-02-05 
    calcperciextremeDayPart2($db, $param2, $param3);
} else if ($param1 == 'getHumidityalldaily') {
    //http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityalldaily/2021-02-01/2021-02-05 
    getHumiditydailyAll($db, $param2, $param3);
} else if ($param1 == 'getHumidityDailyOverviewByDate') {
    // http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityDailyOverviewByDate/D/2021-05-22    
    //getting list of dates
    $datelist = $_GET['datelist'] ?? null;
    getHumidityDailyOverviewByDatesParam($db, $param2, $param3 ?? null, $param4 ?? null, $datelist);
} else if ($param1 == 'getHumidityOverviewByMonthParam') {
    // http://localhost:4200/meteo/api/humiditypageCtrl.php/getHumidityOverviewByMonthParam/D/05-2021    
    //getting list of dates
    $monthlist = $_GET['datelist'] ?? null;
    getHumidityOverviewByMonthParam($db, $param2, $param3 ?? null, $param4 ?? null, $monthlist);
    
    // Temperature Start ****
} else if ($param1 == 'gettempYminmaxAll') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxAll/period/max/Avg
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxAll/period/min/Avg
    gettempYminmaxAll($db, $param3, $param4 ?? null);
} else if ($param1 == 'gettempYminmaxCustom') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxCustom/period/max/Avg?datelist=2021,2019
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxCustom/period/min/Avg?datelist=2021,2019
    $yearlist = $_GET['datelist'] ?? null;
    gettempYminmaxCustom($db, $param3, $param4 ?? null, $yearlist);
} else if ($param1 == 'gettempSTminmaxAll') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempSTminmaxAll/period/max/Avg
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempSTminmaxAll/period/min/Avg
    gettempSTminmaxAll($db, $param3, $param4 ?? null);
} else if ($param1 == 'gettempMTminmaxAll') {
    // Temperature Monthly All
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempMTminmaxAll/period/max
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempMTminmaxAll/period/min
    gettempMTminmaxAll($db, $param3, $param4 ?? null);
} else if ($param1 == 'gettempDTminmaxAll') {
    // http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/MIN/10/Y?valuelist=2021   
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/min/10/Y
    $valuelist = $_GET['valuelist'] ?? null;
    gettempDTminmaxAll($db, $param2, $param3, $param4, $valuelist);
} else if ($param1 == 'gettempDTAminmaxAll') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/max/10/A?valuelist=2021 or Spring or Jan
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/min/10
    $valuelist = $_GET['valuelist'] ?? null;
    gettempDTAminmaxAll($db, $param2, $param3, $param4, $valuelist);
    // Temperature end *********
}else if ($param1 == 'getperciYminmaxAll') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciYminmaxAll/period/max/Avg
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciYminmaxAll/period/min/Abs
    getperciYminmaxAll($db, $param3, $param4 ?? null);
}else if ($param1 == 'getperciYminmaxCustom') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxCustom/period/max/Avg?datelist=2021,2019
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempYminmaxCustom/period/min/Avg?datelist=2021,2019
    $yearlist = $_GET['datelist'] ?? null;
    getperciYminmaxCustom($db, $param3, $param4 ?? null, $yearlist);
}else if ($param1 == 'getperciSTminmaxAll') {
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempSTminmaxAll/period/max/Avg
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempSTminmaxAll/period/min/Avg
    getperciSTminmaxAll($db, $param3, $param4 ?? null);
}else if ($param1 == 'getperciMTminmaxAll') {
    // Temperature Monthly All
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempMTminmaxAll/period/max
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempMTminmaxAll/period/min
    getperciMTminmaxAll($db, $param3, $param4 ?? null);
} 
// Perci/day
else if ($param1 == 'getperciDTminmaxAll') {
    // http://localhost:4200/meteo/api/extremepageCtrl.php/getperciDTminmaxAll/MIN/10/Y?valuelist=2021   
    //http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/min/10/Y
    $valuelist = $_GET['valuelist'] ?? null;
    getperciDTminmaxAll($db, $param2, $param3, $param4, $valuelist);
}
else if ($param1 == 'getperciHRSminmaxAll') {
        //$minmax, $inputDay, $selectdata, $valuelist
    // http://localhost:4200/meteo/api/extremepageCtrl.php/getperciHRSminmaxAll/MIN/10/Y?valuelist=2021   
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciHRSminmaxAll/min/72/ALL
    $valuelist = $_GET['valuelist'] ?? null;
    getperciHRSminmaxAll($db, $param2, $param3, $param4, $valuelist);
}
else if ($param1 == 'getperciRIminmaxAll') {  
    
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciRIminmaxAll/min/ALL    
    getperciRIminmaxAll($db, $param2,$param3);
}
else if ($param1 == 'getperciRIminmaxY') {
        //$minmax, $inputDay, $selectdata, $valuelist      
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciRIminmaxY/min/2021    
    getperciRIminmaxY($db, $param2,$param3);
}
else if ($param1 == 'getperciRIminmaxS') {            
        // Seasonal yearly Rain intensity
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciRIminmaxS/MIN
    getperciRIminmaxS($db, $param2);
    
}else if ($param1 == 'getperciRIminmaxM') {
              
        // Monthly  Rain intensity
    //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciRIminmaxM/MIN
    getperciRIminmaxM($db, $param2,$param3);
}else if ($param1 == 'getperciRIminmaxC') {              
        // Custom Rain intensity    
     //http://localhost:4200/meteo/api/extremepageCtrl.php/getperciRIminmaxC/MIN?valuelist=2021-08-12,2021-08-15
    $valuelist = $_GET['valuelist'] ?? null;
    getperciRIminmaxC($db, $param2,$valuelist);
}



//********** Start Functions *************************************


function gettempYminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempYminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

function gettempYminmaxCustom($db, $param3, $param4, $yearlist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempYminmaxCustom($param3, $param4, $yearlist);
    echo json_encode($tempArr);
}

function gettempSTminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempSTminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

function gettempMTminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempMTminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

//http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/MIN/10/S?valuelist=Spring
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/MIN/10/Y?valuelist=2021
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/MIN/10/M?valuelist=Jan
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTminmaxAll/MIN/10/C?valuelist=2021-02-01,2021-05-01
function gettempDTminmaxAll($db, $param2, $param3, $param4, $valuelist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempDTminmaxAll($param2, $param3, $param4, $valuelist);
    echo json_encode($tempArr);
}

//http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/MIN/10/S?valuelist=Spring
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/MIN/10/Y?valuelist=2021
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/MIN/10/M?valuelist=Jan
//    http://localhost:4200/meteo/api/extremepageCtrl.php/gettempDTAminmaxAll/MIN/10/C?valuelist=2021-02-01,2021-05-01
function gettempDTAminmaxAll($db, $param2, $param3, $param4, $valuelist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->gettempDTAminmaxAll($param2, $param3, $param4, $valuelist);
    echo json_encode($tempArr);
}


function getperciYminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciYminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

function getperciYminmaxCustom($db, $param3, $param4, $yearlist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciYminmaxCustom($param3, $param4, $yearlist);
    echo json_encode($tempArr);
}
function getperciSTminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciSTminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

function getperciMTminmaxAll($db, $param3, $param4) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciMTminmaxAll($param3, $param4);
    echo json_encode($tempArr);
}

function getperciDTminmaxAll($db, $param2, $param3, $param4, $valuelist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciDTminmaxAll($param2, $param3, $param4, $valuelist);                 
    echo json_encode($tempArr);
}

//$minmax, $inputDay, $selectdata, $valuelist
function getperciHRSminmaxAll($db, $param2, $param3, $param4, $valuelist) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciHRSminmaxAll($param2, $param3, $param4, $valuelist);
    echo json_encode($tempArr);
}

//$minmax, $inputDay, $selectdata, $valuelist // Rain Intensity All
function getperciRIminmaxAll($db, $param2,$param3) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciRIminmaxAllAndY($param2,$param3);
    echo json_encode($tempArr);
}

// Intensity per year
function getperciRIminmaxY($db,$param2,$param3) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciRIminmaxAllAndY($param2,$param3);
    echo json_encode($tempArr);
}

// Seasonal Intensity 
function getperciRIminmaxS($db,$param2) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciRIminmaxS($param2);
    echo json_encode($tempArr);
}

// Monthly Intensity
function getperciRIminmaxM($db,$param2,$param3) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciRIminmaxM($param2,$param3);
    echo json_encode($tempArr);
}

function getperciRIminmaxC($db,$param2,$param3) {
    $items = new ChannelExtremesDataPage($db);
    $tempArr = $items->getperciRIminmaxC($param2,$param3);
    echo json_encode($tempArr);
}


//***********************************************************************************
