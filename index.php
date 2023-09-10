<?php
require "general/start.php";
require "config/db.php";

function ValidateAddon($CONNECTION, $table_name) {
    $result_tables = mysqli_query($CONNECTION, "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . getenv('MYSQL_DATABASE') . "';");
    $addon_tables = [];
    while($row = $result_tables->fetch_row()) {
        if ($row[0] != "config") {
            $addon_tables[] = $row[0];
        }
    }

    if ( ! in_array($table_name, $addon_tables)) {
        if ($table_name == NULL) {
            echo "Select a registered addon:<br/><br/>";
        } else {
            echo "Unregistered addon! The current options are:<br/><br/>";
        }

        foreach($addon_tables as $addon_table) {
            echo " - <a href='?addon=$addon_table'>" . $addon_table . "</a><br/>";
        }

        require "general/finish.php";
    } else {
        return $addon_tables;
    }
}

function ValidateAuth($CONNECTION, $auth) {
    // TO-DO: implement at least a form with a cookie to hold this info
    $valid_auth = false;
    if (isset($auth)) {
        $result_auth = SafeMysqliQuery($CONNECTION, "SELECT * FROM config WHERE `key`='auth' AND `value`=?", "s", $auth);

        if (mysqli_num_rows($result_auth) == 1) {
            $valid_auth = true;
        }
    }

    return $valid_auth;
}

function GetAddonsList($CONNECTION, $addon_tables, $table_name, $auth_link) {
    $other_addons = "";
    foreach($addon_tables as $addon_table) {
        if ($addon_table != $table_name) {
            $other_addons .= "<a href='?addon=$addon_table$auth_link'>" . $addon_table . "</a><br/>";
        }
    }
    
    if ($other_addons == "") {
        return "<span id='subheader'><a href='?addon=$table_name$auth_link'>$table_name</a></span>";
    } else {
        return <<<EOD
        <div id="subheader-tooltip" class="tooltip">
            <span id="subheader"><a href="?addon=$table_name$auth_link">$table_name</a></span>
            <span class="tooltip-text">$other_addons</span>
        </div>
        EOD;
    }
}

function GetHeader() {
    return <<<EOD
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
            #subheader {
                font-size: 1.5em;
                font-weight: bold;
                background-color: #5b7c00;
                padding: 5px 10px 5px 10px;
                border-radius: 8px;
                margin-left: 15px;
            }
            #subheader a {
                color: #fff;
            }
            #subtitles {
                padding: 12px 0 7px 0;
            }
            .tooltip {
                position: relative;
                display: inline-block;
                text-decoration: underline;
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
                left: 102%;
                top: -5px;
            }
            .tooltip:hover .tooltip-text {
                visibility: visible;
            }
            #subheader-tooltip, #subheader-tooltip a {
                text-decoration: none;
                padding: 0px 4px 0px 4px;
            }
            #subheader-tooltip .tooltip-text a:hover {
                background-color: #315f95;
                border-radius: 8px;
                padding: 0px 4px 0px 4px;
            }
            #subheader-tooltip .tooltip-text {
                text-align: center;
                left: 100%;
                background-color: #244c7a;
                border-radius: 8px;
                padding: 5px 10px 5px 10px;
            }
            #subheader-tooltip .tooltip-text a {
                text-decoration: none;
                color: #fff;
                font-size: 1.5em;
                font-weight: bold;
            }
            .tooltip .row-tooltip {
                top: -67px;
            }
            .row-set-button {
                width: 100%;
                margin-top: 5px;
                font-weight: bold;
                font-size: 1.0em;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    </head>
    EOD;
}

function GetErrorTable($CONNECTION, $table_name, $auth, $auth_link, $valid_auth, $status_colors) {
    $result_errors = mysqli_query($CONNECTION, "SELECT * FROM $table_name ORDER BY `datetime` DESC LIMIT 300");

    if (mysqli_num_rows($result_errors) == 0) {
        echo "No errors registered. Break the addon to start.";
        require "general/finish.php";
    }

    $error_table = "";

    $error_table .= <<<EOD
    <table id="errors-table">
    <tr id='errors-th'>
    <th>info</th>
    <th>error</th>
    <th>status</th>
    <th>
        <a target='_blank' href='https://github.com/Xalalau/gerror'>
            <img width='25px' alt='GitHub' src='resource/github.png'\>
        </a>
    </th>
    </tr>
    EOD;

    $odd = false;
    while ($error = mysqli_fetch_array($result_errors)) {
        $opacity = $odd ? 0.7 : 1;
        $idx = $error['idx'];

        $disabled = $valid_auth == false ? "disabled=1" : "";
        $tooltip_rows = "<form class='set_status' name='set_status' method='post' style='margin-block-end: 0;' action='/index.php?addon=$table_name$auth_link'>";
        foreach($status_colors as $key => $error_type) {
            $tooltip_rows .= "<div style='background-color: rgb({$error_type[1]}, 255);'><input $disabled type='radio' id='status-radio-{$key}-{$idx}' name='update_status' value='{$key}'><label for='status-radio-{$key}-{$idx}'>{$error_type[0]}</label></div>";
        }
        $tooltip_rows .= "<input type='hidden' name='idx' value='$idx'\><input $disabled type='submit' class='row-set-button' value='Submit'></form>";

        $row = <<<EOD
        <tr id='row-$idx' style='background-color: rgba({$status_colors[$error['status']][1]}, {$opacity});'>
            <td>{$error['datetime']} UTC</br>{$error['map']}</br>{$error['quantity']} time(s)</td>
            <td>
                <pre>{$error['message']}</br>{$error['stack']}</pre>
            </td>
            <td>
                <div class="tooltip">
                    {$status_colors[$error['status']][0]}
                    <span class="tooltip-text row-tooltip">$tooltip_rows</span>
                </div>
            </td>
        </tr>    
        EOD;

        $odd = !$odd;
        $error_table .= $row;
    }

    $error_table .= "</table>";

    return $error_table;
}

function RenderPage($CONNECTION, $status_colors) {
    $table_name = $_GET['addon'] ?? NULL;
    $auth = $_GET['auth'] ?? NULL;

    $addon_tables = ValidateAddon($CONNECTION, $table_name);
    $valid_auth = ValidateAuth($CONNECTION, $auth);

    $auth_link = $valid_auth == true ? "&auth=$auth" : "";

    $header = GetHeader();
    $subheader = GetAddonsList($CONNECTION, $addon_tables, $table_name, $auth_link);

    $error_table = GetErrorTable($CONNECTION, $table_name, $auth, $auth_link, $valid_auth, $status_colors);

    echo <<<EOD
    <html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>
    $header
    <body>
    <span id="heading">Script Error Deck</span>
    $subheader
    </br>
    </br>
    $error_table
    </body>
    </html>
    EOD;
}

$status_colors = [
    [ "TO-DO"    , "36, 39, 41" ],
    [ "Critical" , "76, 0, 0"   ],
    [ "Fixed"    , "6, 58, 16"  ],
    [ "Wont Fix" , "74, 47, 16" ],
    [ "Unrelated", "16, 24, 74" ],
    [ "Ignored"  , "79, 6, 86"  ] 
];

RenderPage($CONNECTION, $status_colors);

require "general/finish.php";
?>