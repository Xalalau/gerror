<?php
require "general/header.php";
require "config/gerror.php";

function InsertError($CONNECTION, $error) {
    $insert = SafeMysqliQuery($CONNECTION, "INSERT INTO " . $error['addon'] . " (`map`, `quantity`, `message`, `stack`) VALUES (?, ?, ?, ?)", "siss", $error['map'], $error['quantity'], $error['msg'], $error['stack']);
        
    if ($insert) {
        echo "Entry added";
    } else {
        echo "Failed to add entry";
    }
}

function UpdateError($CONNECTION, $registered, $error) {
    $registered = mysqli_fetch_assoc($registered);
    $quantity = $registered["quantity"] + $error['quantity'];
    $status = $registered["status"];

    // Only to-dos and fixed are accepted
    if ($status > 2) {
        echo "Update ignored due to registered error status";
        return;
    }

    $update = SafeMysqliQuery($CONNECTION, "UPDATE " . $error['addon'] . " SET `map`=?, `quantity`=$quantity, `message`=?, `stack`=? WHERE `idx`=" . $registered['idx'], "sss", $error['map'], $error['msg'], $error['stack']);

    if ($update) {
        echo "Entry updated";
    } else {
        echo "Failed to update entry";
    }
}

function Main($CONNECTION) {
    if ( ! (isset($_POST['addon']) && isset($_POST['msg']) && isset($_POST['stack']) && isset($_POST['map']) && isset($_POST['quantity']))) {
        echo "Missing fields";
        return;
    }

    $tables = mysqli_query($CONNECTION, "SELECT `TABLE_NAME` FROM information_schema.TABLES WHERE `TABLE_SCHEMA`='" . getenv('MYSQL_DATABASE') . "'");
    while ($table = mysqli_fetch_array($tables)) {
        if ($_POST['addon'] == $table[0]) {
            $addon = $table[0];
            break;
        }
    }

    if (! isset($addon)) {
        echo "Invalid addon name";
        return;
    }

    $error = [
        'addon' => $addon,
        'msg' => $_POST['msg'],
        'stack' => $_POST['stack'],
        'map' => $_POST['map'],
        'quantity' => $_POST['quantity']
    ];

    $registered = SafeMysqliQuery($CONNECTION, "SELECT * FROM " . $error['addon'] . " WHERE `message`=?", "s", $error['msg']);
    $registered_count = mysqli_num_rows($registered);

    if ($registered_count == 0) {
        InsertError($CONNECTION, $error);
    } else {
        UpdateError($CONNECTION, $registered, $error);
    }
}

Main($CONNECTION);

require "general/footer.php";
?>