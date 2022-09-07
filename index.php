<?php
require "general/header.php";
require "config/gerror.php";

$addon = "gm_construct_13_beta"; // TO-DO: use the system with more addons
if (! $addon) {
    echo "Missing fields";
    require "general/footer.php";
    exit;
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
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: black;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            top: -5px;
            left: 105%;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
        }
    </style>
</head>
<body>
<span id="heading">Auto Reported Script Errors </span>
<span id="subheading">$addon</span></br>
</br>
EOD;
echo $html_header;

$errors = mysqli_query($CONNECTION, "SELECT * FROM gm_construct_13_beta ORDER BY `datetime` DESC LIMIT 100");

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

$tooltip = <<<EOD
<div>
<div style="background-color: rgb({$status[0][1]}, 255);">0 - {$status[0][0]}</div> 
<div style="background-color: rgb({$status[1][1]}, 255);">1 - {$status[1][0]}</div> 
<div style="background-color: rgb({$status[2][1]}, 255);">2 - {$status[2][0]}</div> 
<div style="background-color: rgb({$status[3][1]}, 255);">3 - {$status[3][0]} Fix</div> 
<div style="background-color: rgb({$status[4][1]}, 255);">4 - {$status[4][0]}</div> 
<div style="background-color: rgb({$status[5][1]}, 255);">5 - {$status[5][0]}</div>
</div>
EOD;

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
                <span class="tooltiptext">$tooltip</span>
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