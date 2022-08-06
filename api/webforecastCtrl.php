<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

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
//http://localhost:4200/meteo/api/webforecastCtrl.php/getWebForcastSymbol
//http://localhost:4200/meteo/api/webforecastCtrl.php/getWebForcastTxt

if ($param1 == 'getWebForcastSymbol') {
    getWebForcast('Symbol');
} else if ($param1 == 'getWebForcastTxt') {
    getWebForcast('text');
}

function getWebForcast($param) {
//    //Local Start
//    $dir = "C:/AngularApp/Weatherforecast/reqirements/webForecast_changes/forecastxml/";
//    //Local End
    
    //prod start
    $dir = dirname(__DIR__, 3);
    $dir =$dir.'/meteonewsforecast/'; 
   // echo $dir;    
    //prod End
    
    //parsing symbole.xml   
    if ($param == 'Symbol') {
        $symboleFile = $dir . 'symbole.xml';
        parsingSymbolXML($symboleFile);
    }
    if ($param == 'text') {
        $textFile = $dir . 'text.xml';
        parsingTextXML($textFile);
    }
}

function parsingSymbolXML($file) {
    if (file_exists($file)) {
        $filemtime = date("d.m.Y H:i",filemtime($file));        
        $article = simplexml_load_file($file)
                or die("Error: Cannot create object");
        $list = $article->Location->attributes();

        if ($article->Location->children() != null) {
            $tempArr = Array();
            $tempArr[] = Array(
                'filemtime' => $filemtime,
                'name' => (string) $list->name,
                'id' => (string) $list->id,
                'altitude' => (string) $list->altitude,
                'latitude' => (string) $list->latitude,
                'longitude' => (string) $list->longitude,
                'countrycode' => (string) $list->countrycode
            );
            foreach ($article->Location->Day as $days) {
                $dateformat = ((new DateTime($days['val']))->format("Y-m-d"));
                $tempArr[] = Array(
                    'day' => $dateformat,
                    'TxtDay' => (string) $days->TxtDay,
                    'SymbDay' => (string) $days->SymbDay,
                    'TempMor' => (string) $days->TempMor,
                    'TempAft' => (string) $days->TempAft,
                );
                PHP_EOL;
            }
//            echo "<pre>";
//            print_r($tempArr);
//            echo "<post>";
            echo json_encode($tempArr);
        }
    } else {
        exit('Failed to open symbole.xml');
    }
}

function parsingTextXML($file) {
    if (file_exists($file)) {
        $filemtime = date("d.m.Y H:i",filemtime($file));
        $article = simplexml_load_file($file)
                or die("Error: Cannot create object");
        if ($article->children() != null) {
            $tempArr = Array();
            foreach ($article->Bulletin as $btns) {               
                $dateformat = ((new DateTime($btns->Day['val']))->format("Y-m-d"));
                $tempArr[] = Array(
                    'filemtime' => $filemtime,
                    'name' => (string) (string) $btns['name'],
                    'prod' => (string) $btns['prod'],
                    'language' => (string) $btns['language'],
                    'day' => $dateformat,
                    'txt' => (string) $btns->Day->Txt,
                );
                PHP_EOL;
            }
            echo json_encode($tempArr);
        }
    } else {
        exit('Failed to open symbole.xml');
    }
}
