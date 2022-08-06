<?php

/**
 * All Channel data those are common
 *
 * @author USER
 */
class InsertErrorChannelData {

    // Connection
    private $conn;

    // Db connection
    public function __construct($db) {
        $this->conn = $db;
    }

    public function insertStationData($stationlData, $StationInfo) {

        $dateformat = ((new DateTime($StationInfo['time']))->format("Y-m-d H:i:s"));
        echo "***** " . $dateformat;
        $stationId = $stationlData['stationId'];
        $name = $stationlData['name'];
        $timezone = $stationlData['timezone'];
        $firmware = $StationInfo['firmware'];
        $configtime = $StationInfo['configtime'];
        $paramtime = $StationInfo['paramtime'];
        $batteryVoltage = $StationInfo['batteryVoltage'];
        $temperature = $StationInfo['temperature'];
        $deviceType = $StationInfo['deviceType'];
        $providerName = $StationInfo['providerName'];
        $gsmSignal = $StationInfo['gsmSignal'];
        $ipAddress = $StationInfo['ipAddress'];
        $transmissionCycle = $StationInfo['transmissionCycle'];
        $transmissionOffset = $StationInfo['transmissionOffset'];
        $configuredTransmissionCycle = $StationInfo['configuredTransmissionCycle'];

        $query = "INSERT INTO `stationdata`
                                (`stationid`,
                                `stationname`,
                                `timezone`,
                                `time`,
                                `firmware`,
                                `configtime`,
                                `paramtime`,
                                `batteryVoltage`,
                                `temperature`,
                                `deviceType`,
                                `providerName`,
                                `gsmSignal`,
                                `ipAddress`,
                                `transmissionCycle`,
                                `transmissionOffset`,
                                `confiTransCycle`,
                                `filetime`)
                                VALUES
                                ('$stationId',
                                '$name',
                                '$timezone',
                                '$dateformat',
                                '$firmware',
                                '$configtime',
                                '$paramtime',
                                '$batteryVoltage',
                                '$temperature',
                                '$deviceType',
                                '$providerName',
                                '$gsmSignal',
                                '$ipAddress',
                                '$transmissionCycle',
                                '$transmissionOffset',
                                '$configuredTransmissionCycle',
                                '$dateformat')";

        if ($this->conn->query($query) == true) {
            echo "record inserted* ";
        } else {
            echo "Error: ";
        }
    }

    public function insertChannelData($channelData, $stationlData, $StationInfo) {

        $dateformat = ((new DateTime($StationInfo['time']))->format("Y-m-d H:i:s"));
        $stationId = $stationlData['stationId'];

        $channelId = $channelData['channelId'];
        $name = $channelData['name'];
        $unit = $channelData['unit'];
        $samplingInterval = $channelData['samplingInterval'];
        $storageInterval = $channelData['storageInterval'];
        $configuredSamplingInterval = $channelData['configuredSamplingInterval'];
        $configuredStorageInterval = $channelData['configuredStorageInterval'];
        $offset = $channelData['offset'];

        $table = "channel_" . $channelId;
        $query = "INSERT INTO `$table`
                        (`channelId`,
                        `name`,
                        `unit`,
                        `samplingInterval`,
                        `storageInterval`,
                        `configuredSamplingInterval`,
                        `configuredStorageInterval`,                        
                        `datatime`,
                        `stationid`)
                        VALUES
                        ('$channelId',
                        '$name',
                        '$unit',
                        '$samplingInterval',
                        '$storageInterval',
                        '$configuredSamplingInterval',
                        '$configuredStorageInterval',                        
                        '$dateformat',
                        '$stationId')";

        if ($this->conn->query($query) == true) {
            echo "record inserted** ";
        } else {
            echo "Error: ";
        }
    }

    public function insertChannelValuesData($values, $channelData, $StationInfo,$file) {

        $dateformat = ((new DateTime($StationInfo['time']))->format("Y-m-d H:i:s"));
        $channelId = $channelData['channelId'];
        $value = $values;
        $valuet = $dateformat = ((new DateTime($values['t']))->format("Y-m-d H:i:s"));
        $errorcode = $values['errorcode'];
        if ($values['errorcode'] != null) {           
            $start = strripos($file, "/");
            $strlenght = strlen($file);
            $sub = substr($file, $start+1, $strlenght);
//             echo "<pre>";
//            print_r($dateformat . "," . $channelId . "," . $values['errorcode'] . "," . $valuet . "," . $value. "," . $sub);
//            echo "<post>";            
            $table = "channelerrors";
            $query = "INSERT INTO `$table`
                    (`datatime`,
                    `channelid`,
                    `valuedate`,
                    `errorcode`,
                    `value`,`filename`)
                    VALUES
                    (
                    '$dateformat',
                    '$channelId',
                    '$valuet',
                    '$errorcode','$value','$sub')";
            if ($this->conn->query($query) == true) {
                echo "<br> ** Error record inserted*** <br>";
            } else {
                echo "Error: in insertion";
            }
        }
    }

}
