<?php
require "general/header.php";
require "config/gerror.php";

$addon = "gm_construct_13_beta"; // TO-DO: use the system with more addons
if (! $addon) {
    echo "Missing fields";
    require "general/footer.php";
    exit;
}

$header = <<<EOD
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
            background-color: #181a1b;
            color: #e8e6e3;
        }
        #errors-th {
            background-color: #000;
        }
        td, th {
            padding: 6px;
        }
        #title {
            font-size: 2.5em;
            font-weight: bold;
        }
        #subtitle {
            font-size: 1.5em;
            font-weight: bold;
            background-color: #5b7c00;
            padding: 5px 10px 5px 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<span id="title">Auto Reported Script Errors </span>
<span id="subtitle">$addon</span></br>
</br>
EOD;
echo $header;

$errors = mysqli_query($CONNECTION, "SELECT * FROM gm_construct_13_beta ORDER BY `datetime` DESC LIMIT 100");

if ($errors == false) {
    echo "No errors";
    require "general/footer.php";
    exit;
}

$status = [
    [ "TO-DO", "" ],                   // 0
    [ "TO-DO Critical", "#4c0000" ],   // 1
    [ "Fixed", "#0c3817" ],            // 2
    [ "Wont Fix", "#454a10" ],         // 3
    [ "Unrelated", "#10184a" ],        // 4
    [ "Ignored", "#4f0656"]            // 5
];

echo "<table>";
while ($error = mysqli_fetch_array($errors)) {
    $row = <<<EOD
    <tr id='errors-th'>
        <th>status</th>
        <th>info</th>
        <th>error</th>
    </tr>
    <tr style='background-color: {$status[$error['status']][1]};'>
        <td>{$status[$error['status']][0]}</td>
        <td>{$error['datetime']}</br>{$error['map']}</br>{$error['quantity']} times</td>
        <td>
            <pre>{$error['message']}</br>{$error['stack']}</pre>
        </td>
    </tr>    
    EOD;
    echo $row;
}
echo "</table>";

$footer = <<<EOD
</body>
</html>
EOD;
echo $footer;

require "general/footer.php";
?>