<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

include('../api/config/database.php');
include('../api/class/ChannelData.php');
include('../api/class/InsertChannelData.php');

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
//http://localhost:4200/meteo/api/schedularCtrl.php/parseXml
// fetching database connection
$database = new Database();
$db = $database->getConnection();
if ($param1 == 'parseXml') {
    directoryXML($db);
}

function directoryXML($db) {
    // $dir = "/var/www/vhosts/meteo-maennedorf.ch/test/";
    // $destinationFilePath = "/var/www/vhosts/meteo-maennedorf.ch/test/archive/";
// Open a directory, and read its contents

    $dir = dirname(__DIR__, 3);
    $dir = $dir . '/OML/';
    echo $dir;
    $destinationFilePath = dirname(__DIR__, 3) . '/OML/archive/';
    $destFilePathcopy = dirname(__DIR__, 3) . '/Hydras3/';
    echo $destinationFilePath;

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                echo " ** " . $file;
                if (strlen($file) > 8 && is_file($dir . $file)) {
                    $filename = $dir . $file;
                    echo "** filename:** " . $filename . "<br>";
                    parseXML($db, $filename);
                    echo " ** Parsing done! ** ";

                    if (!copy($filename, $destFilePathcopy . $file)) {
                        echo "failed to copy $file";
                    } else {
                        echo "copied $file into $destFilePathcopy . $file\n";
                    }

                    if (!rename($filename, $destinationFilePath . $file)) {
                        echo "File can't be moved!";
                    } else {
                        echo " ** File has been moved!** " . $destinationFilePath . $file;
                    }
                }
            }
        }
    }
}

function parseXML($db, $file) {
    if (file_exists($file)) {
        $StationDataList = simplexml_load_file($file)
                or die("Error: Cannot create object");
        $stationlData = $StationDataList->StationData;
        $StationInfo = $StationDataList->StationData->StationInfo;
        $items = new InsertChannelData($db);
        $items->insertStationData($stationlData, $StationInfo);

        foreach ($StationDataList->StationData->ChannelData as $channelData) {
            $items->insertChannelData($channelData, $stationlData, $StationInfo);
            $list = $channelData->Values;
            if ((isset($list) && count($list) > 0) && $list->children() != null) {
                foreach ($list->children() as $values) {
                    $items->insertChannelValuesData($values, $channelData, $StationInfo, $file);
                }
            }
            PHP_EOL;
        }
    } else {
        exit('Failed to open ');
    }
}
