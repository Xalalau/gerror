<?php
require "general/start.php";
require "config/db.php";

function GetErrorDate() {
    date_default_timezone_set('UTC');

    $date = new DateTimeImmutable();

    return $date->format('Y-m-d H:i:s');
}

function InsertError($CONNECTION, $error) {
    $insert = SafeMysqliQuery($CONNECTION, "INSERT INTO " . $error['tableName'] . " (`datetime`, `map`, `quantity`, `message`, `stack`) VALUES (?, ?, ?, ?, ?)", "ssiss", GetErrorDate(), $error['map'], $error['quantity'], $error['msg'], $error['stack']);
        
    if ($insert) {
        echo "Entry added";
    } else {
        echo "Failed to add entry";
    }
}

function UpdateError($CONNECTION, $registered, $error) {
    $registered = mysqli_fetch_assoc($registered);
    $quantity = $registered["quantity"] + $error['quantity'];

    /*
    $status = $registered["status"];

    // Only to-do, critical and fixed can be updated
    if ($status > 2) {
        echo "Update ignored due to registered error status";
        return;
    }
    */

    $update = SafeMysqliQuery($CONNECTION, "UPDATE " . $error['tableName'] . " SET `datetime`=?, `map`=?, `quantity`=$quantity, `message`=?, `stack`=? WHERE `idx`=" . $registered['idx'], "ssss", GetErrorDate(), $error['map'], $error['msg'], $error['stack']);

    if ($update) {
        echo "Entry updated";
    } else {
        echo "Failed to update entry";
    }
}

function CheckLastVersionTimestamp($CONNECTION, $error) {
    $keyName = $error['tableName'] . "_version_timestamp";
    $version_datetime = DateTime::createFromFormat('U', $_POST['versionDate']);
    $result_last_version_timestamp = mysqli_fetch_row(SafeMysqliQuery($CONNECTION, "SELECT `value` FROM config WHERE `key`=?", "s", $keyName));

    if (! $result_last_version_timestamp) {
        $version_timestamp = strtotime($version_datetime->format('Y-m-d H:i:s'));
        SafeMysqliQuery($CONNECTION, "INSERT INTO config (`key`, `value`) VALUES (?, '$version_timestamp')", "s", $keyName);
        return true;
    } else {
        $last_version_datetime = DateTime::createFromFormat('U', $result_last_version_timestamp[0]);
        $now_datetime = new DateTime();

        if ($version_datetime < $last_version_datetime) {
            return false;
        } elseif ($version_datetime > $last_version_datetime && $version_datetime < $now_datetime) {
            $version_timestamp = strtotime($version_datetime->format('Y-m-d H:i:s'));
            SafeMysqliQuery($CONNECTION, "UPDATE config SET `value`=? WHERE `key`=?", "ss", $version_timestamp, $keyName);
            return true;
        } elseif ($version_datetime == $last_version_datetime) {
            return true;
        } else {
            return false;
        }
    }
}

function Main($CONNECTION) {
    if ( ! (
            ($_POST['addon'] ?? $_POST['databaseName']) &&
            isset($_POST['msg']) &&
            isset($_POST['stack']) &&
            isset($_POST['map']) &&
            isset($_POST['quantity']) &&
            isset($_POST['versionDate'])
            )
        ) {
        echo "Missing fields";
        return;
    }

    $tableName = $_POST['addon'] ?? $_POST['databaseName'];
    $tables = mysqli_query($CONNECTION, "SELECT `TABLE_NAME` FROM information_schema.TABLES WHERE `TABLE_SCHEMA`='" . getenv('MYSQL_DATABASE') . "'");
    while ($table = mysqli_fetch_array($tables)) {
        if ($tableName == $table[0]) {
            $found = $table[0];
            break;
        }
    }

    if (! isset($found)) {
        echo "Invalid table name";
        return;
    }

    $error = [
        'tableName' => $tableName,
        'msg' => $_POST['msg'],
        'stack' => $_POST['stack'],
        'map' => $_POST['map'],
        'quantity' => $_POST['quantity']
    ];

    if (CheckLastVersionTimestamp($CONNECTION, $error) == false) {
        echo "Unsupported version, error ignored";
        return;
    }

    $registered = SafeMysqliQuery($CONNECTION, "SELECT * FROM " . $error['tableName'] . " WHERE `message`=?", "s", $error['msg']);
    $registered_count = mysqli_num_rows($registered);

    if ($registered_count == 0) {
        InsertError($CONNECTION, $error);
    } else {
        UpdateError($CONNECTION, $registered, $error);
    }
}

Main($CONNECTION);

require "general/finish.php";
?>