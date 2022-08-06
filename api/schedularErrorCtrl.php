<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

include('../api/config/database.php');
include('../api/class/ChannelData.php');
include('../api/class/InsertErrorChannelData.php');



/** @var type $requestMethod */
//$requestMethod = $_SERVER["REQUEST_METHOD"];
$pathinfo = $_SERVER['PATH_INFO'];
$called = explode('/', trim($_SERVER['PATH_INFO'], '/'));

$param1; // Get method 

if (count($called) == 1) {
    $param1 = $called[0];
} else {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad Request");
    die();
}
//http://localhost:4200/meteo/api/schedularErrorCtrl.php/parseerrorXml
// fetching database connection
$database = new Database();
$db = $database->getConnection();
if ($param1 == 'parseerrorXml') {
    directoryXML($db);
}

function directoryXML($db) {
    $dir = "C:/AngularApp/Weatherforecast/latestxml/archive/";
   // $destinationFilePath = "C:/AngularApp/Weatherforecast/latestxml/archive/";
// Open a directory, and read its contents
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (strlen($file) > 2) {
                    $filename = $dir . $file;
                    //echo "<br>"."filename: " . $filename . ", <br>";
                    parseXML($db, $filename);
                }
            }
        }
    }
}

function parseXML($db, $file) {
    if (file_exists($file)) {
        $StationDataList = simplexml_load_file($file)
                or die("Error: Cannot create object");
        if(!empty($StationDataList)){        
        $stationlData = $StationDataList->StationData;
        $StationInfo = $StationDataList->StationData->StationInfo;        
        $items = new InsertErrorChannelData($db);  
        foreach ($StationDataList->StationData->ChannelData as $channelData) {                                   
            $list = $channelData->Values;
            if ($list->children() != null) {
                foreach ($list->children() as $values) {                    
                    $items->insertChannelValuesData($values,$channelData,$StationInfo,$file);
                }
            }
            PHP_EOL;
        }
        }
        
    } else {
        exit('Failed to open '.$file);
    }
}
