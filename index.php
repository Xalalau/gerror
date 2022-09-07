<?php
require "general/header.php";
require "config/gerror.php";

$addon = $_GET['addon'] ?? NULL;

$result_tables = mysqli_query($CONNECTION, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'gmoderror';");
$tables = [];
while($row = $result_tables->fetch_row()) {
    $tables[] = $row[0];
}

if ( ! in_array($addon, $tables)) {
    if ($addon == NULL) {
        echo "Select a registered addon:<br/><br/>";
    } else {
        echo "Unregistered addon! The current options are:<br/><br/>";
    }

    foreach($tables as $table) {
        echo " - <a href=\"?addon=$table\">" . $table . "</a><br/>";
    }

    require "general/footer.php";
    exit;
}

$other_addons = "";
foreach($tables as $table) {
    if ($table != $addon) {
        $other_addons .= "<a href=\"?addon=$table\">" . $table . "</a><br/>";
    }
}

if ($other_addons == "") {
    $subheading = "<span id=\"subheading\">$addon</span>";
} else {
    $subheading = <<<EOD
    <div id="subheading-tooltip" class="tooltip">
        <span id="subheading">$addon</span>
        <span class="tooltip-text">$other_addons</span>
    </div>
    EOD;
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
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            top: -5px;
            left: 104%;
        }
        .tooltip:hover .tooltip-text {
            visibility: visible;
        }
        #subheading-tooltip .tooltip-text {
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
    [ "TO-DO"    , "36, 39, 41" ],     // 0
    [ "Critical" , "76, 0, 0"   ],     // 1
    [ "Fixed"    , "6, 58, 16"  ],     // 2
    [ "Wont Fix" , "74, 47, 16" ],     // 3
    [ "Unrelated", "16, 24, 74" ],     // 4
    [ "Ignored"  , "79, 6, 86"  ]      // 5
];

$tooltip_rows = "<div>";
foreach($status as $key => $error_type) {
    $tooltip_rows .= "<div style=\"background-color: rgb({$error_type[1]}, 255);\">$key - {$error_type[0]}</div>";
}
$tooltip_rows .= "</div>";

$table_header = <<<EOD
<table id="errors-table">
<tr id='errors-th'>
<th>status</th>
<th>info</th>
<th>error</th>
</tr>
EOD;
echo $table_header;
$odd = false;
while ($error = mysqli_fetch_array($errors)) {
    $opacity = $odd ? 0.7 : 1;
    $row = <<<EOD
    <tr style='background-color: rgba({$status[$error['status']][1]}, {$opacity});'>
        <td>
            <div class="tooltip">
                {$status[$error['status']][0]}
                <span class="tooltip-text">$tooltip_rows</span>
            </div>
        </td>
        <td>{$error['datetime']}</br>{$error['map']}</br>{$error['quantity']} time(s)</td>
        <td>
            <pre>{$error['message']}</br>{$error['stack']}</pre>
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