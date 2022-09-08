<?php
require "general/header.php";
require "config/gerror.php";

$addon = $_GET['addon'] ?? NULL;
$auth = $_GET['auth'] ?? NULL;

$result_tables = mysqli_query($CONNECTION, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'gmoderror';");
$tables = [];
while($row = $result_tables->fetch_row()) {
    if ($row[0] != "config") {
        $tables[] = $row[0];
    }
}

if ( ! in_array($addon, $tables)) {
    if ($addon == NULL) {
        echo "Select a registered addon:<br/><br/>";
    } else {
        echo "Unregistered addon! The current options are:<br/><br/>";
    }

    foreach($tables as $table) {
        echo " - <a href='?addon=$table'>" . $table . "</a><br/>";
    }

    require "general/footer.php";
    exit;
}

// TO-DO: implement at least a form with a cookie to hold this info
$valid_auth = false;
if (isset($auth)) {
    $result_auth = SafeMysqliQuery($CONNECTION, "SELECT * FROM config WHERE `key`='auth' AND `value`=?", "s", $auth);

    if (mysqli_num_rows($result_auth) == 1) {
        $valid_auth = true;
    }
}

$other_addons = "";
foreach($tables as $table) {
    if ($table != $addon) {
        $other_addons .= "<a href='?addon=$table'>" . $table . "</a><br/>";
    }
}

if ($other_addons == "") {
    $subheading = "<span id='subheading'>$addon</span>";
} else {
    $subheading = <<<EOD
    <div id="subheading-tooltip" class="tooltip">
        <span id="subheading">$addon</span>
        <span class="tooltip-text">$other_addons</span>
    </div>
    EOD;
}

$update_status = $_POST['update_status'] ?? NULL;
if ($update_status != NULL && $valid_auth == true) {
    $idx = intval($_POST['idx']);
    if ($idx != 0) {
        $update = SafeMysqliQuery($CONNECTION, "UPDATE " . $addon . " SET `status`=? WHERE `idx`=" . $idx, "i", $_POST['update_status']);
    }
}

$html_header = <<<EOD
<html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>
<head>
	<title>GMod Errors</title>
	<meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>
	<meta name='viewport' content='width=device-width, initial-scale=1'/>
	<meta name='robots' content='index,follow'/>
	<meta name='author' content='Xalalau'/>
	<meta name='description' content='My GMod script errors.'/>
	<link href='https://xalalau.com/recursos/favicon2.png' rel='icon' type='image/png'/>
    <style>   
        html {
            background-color: #101112;
            color: #e8e6e3;
        }
        #errors-table {
        }
        #errors-th {
            background-color: #000;
        }
        td, th {
            padding: 6px;
        }
        #heading {
            font-size: 2.5em;
            font-weight: bold;
        }
        #subheading {
            font-size: 1.5em;
            font-weight: bold;
            background-color: #5b7c00;
            padding: 5px 10px 5px 10px;
            border-radius: 8px;
        }
        #subtitles {
            padding: 12px 0 7px 0;
        }
        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }
        .tooltip .tooltip-text {
            visibility: hidden;
            min-width: 120px;
            background-color: black;
            color: #fff;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            top: -67px;
            left: 102%;
        }
        .tooltip:hover .tooltip-text {
            visibility: visible;
        }
        #subheading-tooltip .tooltip-text {
            text-align: center;
            left: 100%;
            background-color: #2a3b01;
            border-radius: 8px;
            padding: 5px 10px 5px 10px;
        }
        #subheading-tooltip .tooltip-text a {
            text-decoration: none;
            color: #fff;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
<span id="heading">Auto Reported Script Errors </span>
$subheading
</br>
</br>
EOD;
echo $html_header;

$errors = mysqli_query($CONNECTION, "SELECT * FROM $addon ORDER BY `datetime` DESC LIMIT 100");

if ($errors == false) {
    echo "No errors";
    require "general/footer.php";
    exit;
}

$status = [
    [ "TO-DO"    , "36, 39, 41" ],
    [ "Critical" , "76, 0, 0"   ],
    [ "Fixed"    , "6, 58, 16"  ],
    [ "Wont Fix" , "74, 47, 16" ],
    [ "Unrelated", "16, 24, 74" ],
    [ "Ignored"  , "79, 6, 86"  ] 
];

function GetErrorTooltipRows($addon, $status, $auth, $valid_auth, $idx) {
    $disabled = $valid_auth == false ? "disabled=1" : "";
    $auth_link = $valid_auth == true ? "&auth=$auth" : "";
    $tooltip_rows = "<form name='set_status' method='post' style='margin-block-end: 0;' action='/index.php?addon=$addon$auth_link'>";
    foreach($status as $key => $error_type) {
        $tooltip_rows .= "<div style='background-color: rgb({$error_type[1]}, 255);'><input $disabled type='radio' id='status-radio-{$key}-{$idx}' name='update_status' value='{$key}'><label for='status-radio-{$key}-{$idx}'>{$error_type[0]}</label></div>";
    }
    $tooltip_rows .= "<input type='hidden' name='idx' value='$idx'\><input $disabled type='submit' style='width: 100%; margin-top: 5px;' value='Submit'></form>";
    return $tooltip_rows;
}

$table_header = <<<EOD
<table id="errors-table">
<tr id='errors-th'>
<th>info</th>
<th>error</th>
<th>status</th>
</tr>
EOD;
echo $table_header;
$odd = false;
while ($error = mysqli_fetch_array($errors)) {
    $opacity = $odd ? 0.7 : 1;
    $tooltip_rows = GetErrorTooltipRows($addon, $status, $auth, $valid_auth, $error['idx']);
    $row = <<<EOD
    <tr style='background-color: rgba({$status[$error['status']][1]}, {$opacity});'>
        <td>{$error['datetime']}</br>{$error['map']}</br>{$error['quantity']} time(s)</td>
        <td>
            <pre>{$error['message']}</br>{$error['stack']}</pre>
        </td>
        <td>
            <div class="tooltip">
                {$status[$error['status']][0]}
                <span class="tooltip-text">$tooltip_rows</span>
            </div>
        </td>
    </tr>    
    EOD;
    echo $row;
    $odd = !$odd;
}
echo "</table>";

$html_footer = <<<EOD
</body>
</html>
EOD;
echo $html_footer;

require "general/footer.php";
?>