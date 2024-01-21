<?php

// No MVC here, only old school plain web.

function GetAddonTables($CONNECTION, $table_name) {
    $addon_tables = [];

    $query = mysqli_query($CONNECTION, "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . getenv('MYSQL_DATABASE') . "';");

    while($row = $query->fetch_row()) {
        if ($row[0] != "config") {
            $addon_tables[] = $row[0];
        }
    }

    return $addon_tables;
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

function GetAddonsList($CONNECTION, $addon_tables, $auth_link) {
    $addons_list = [];

    foreach($addon_tables as $addon_table) {
        array_push($addons_list, [
            'name' => $addon_table,
            'auth' => "$addon_table$auth_link"
        ]);
    }

    return $addons_list;
}

function GetErrorData($CONNECTION, $table_name) {
    $err_query_arr = mysqli_query($CONNECTION, "SELECT * FROM $table_name ORDER BY `datetime` DESC LIMIT 300");

    if (mysqli_num_rows($err_query_arr) == 0) {
        echo "No errors registered. Break the addon to start.";
        require "general/finish.php";
    }

    return $err_query_arr;
}

function UpdateStatus($CONNECTION, $table_name, $idx, $update_status) {
    if ($table_name != NULL && $idx != 0 && $update_status != NULL) {
        $update = SafeMysqliQuery($CONNECTION, "UPDATE " . $table_name . " SET `status`=? WHERE `idx`=" . $idx, "i", $update_status);
 
        if ($update) {
            echo "Entry updated";
        } else {
            echo "Failed to update entry";
        }
    } else {
        echo "Invalid POST fields";
    }
}

function ReceiveAjax($CONNECTION, $table_name, $auth) {
    $valid_auth = ValidateAuth($CONNECTION, $auth);

    $idx = intval($_POST['idx'] ?? 0);
    $update_status = $_POST['update_status'] ?? NULL;

    if ($valid_auth == false) {
        echo "Invalid authentication!";
        return;
    }

    if ($update_status != NULL) {
        UpdateStatus($CONNECTION, $table_name, $idx, $update_status);
    };
}







require "general/start.php";
require "config/db.php";

$table_name = $_GET['addon'] ?? NULL;
$auth = $_GET['auth'] ?? NULL;

$addon_tables = GetAddonTables($CONNECTION, $table_name);

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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ReceiveAjax($CONNECTION, $table_name, $auth);
    require "general/finish.php";
}

$status_colors = [
    [ "TO-DO"    , "36, 39, 41" ],
    [ "Critical" , "76, 0, 0"   ],
    [ "Fixed"    , "6, 58, 16"  ],
    [ "Wont Fix" , "74, 47, 16" ],
    [ "Unrelated", "16, 24, 74" ],
    [ "Ignored"  , "79, 6, 86"  ] 
];

$valid_auth = ValidateAuth($CONNECTION, $auth);

$auth_link = $valid_auth == true ? "&auth=$auth" : "";

$addons_list = GetAddonsList($CONNECTION, $addon_tables, $auth_link);

$err_query_arr = GetErrorData($CONNECTION, $table_name);








?>
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
:root {
    --odd-opacity: 0.7;
    --even-opacity: 1;
}

html {
    background-color: #101112;
    color: #e8e6e3;
}

/* #errors-table {
} */

#errors-table pre {
    text-wrap: wrap;
}

#errors-th {
    background-color: #000;
}

td, th {
    padding: 6px;
}

#header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

#header-text {
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
    background-color: black;
    color: #fff;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    white-space: nowrap;
}

.tooltip span:first-of-type {
    white-space: nowrap;
}

.status-form div label {
    padding-right: 5px;
}

.row-tooltip {
    left: 0%;
    top: -70px; 
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
    top: -5px;
    left: 102%;
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

.row-set-button {
    width: 100%;
    margin-top: 5px;
    font-weight: bold;
    font-size: 1.0em;
}

.status-form {
    margin-block-end: 0;
}

#gitbug-link {
    text-decoration: none;
}

#gitbug-link img {
    width: 25px;
    margin-left: 15px;
}

</style>
<script src="assets/js/jquery.min.3.7.1.js"></script>
</head>
<body>
<div id="header">
    <span id="header-text">Script Error Deck</span>

    <a id="gitbug-link" target='_blank' href='https://github.com/Xalalau/gerror'>
        <img alt='GitHub' src='assets/images/github.png'\>
    </a>

    <div>
        <?php if (count($addons_list) == 1) { ?>
            <span id='subheader'><a href='?addon=<?= $addons_list[0]['auth'] ?>'><?= $addons_list[0]['name'] ?></a></span>";
        <?php } else { ?>
            <div id="subheader-tooltip" class="tooltip">
                <span id="subheader"><a href="?addon=<?= $table_name ?><?= $auth_link ?>"><?= $table_name ?></a></span>
                <span class="tooltip-text">
                    <?php foreach($addons_list as $addon) { if ($addon['name'] != $table_name) { ?>
                        <a href='?addon=<?= $addon['auth'] ?>'><?= $addon['name'] ?></a><br/>
                    <?php } } ?>
                </span>
            </div>
        <?php } ?>
    </div>
</div>
<table id="errors-table">
<tr id='errors-th'>
    <th></th>
    <th>Info</th>
    <th>Error</th>
    <th>Status</th>
</tr>
<?php
$odd = false;
while ($error = mysqli_fetch_array($err_query_arr)) {
    $opacity = $odd ? 'odd-opacity' : 'even-opacity';
    $row_class= $odd ? 'row-odd' : 'row-even';

    $idx = $error['idx'];

    $disabled = $valid_auth == false ? "disabled=1" : "";

    if ($error['is_server'] == 1 && $error['is_client'] == 1) {
        $realm_img = "assets/images/shared.png";
    } elseif ($error['is_server'] == 1) {
        $realm_img = "assets/images/server.png";
    } else {
        $realm_img = "assets/images/client.png";
    }

    echo <<<EOD
    <tr id='row-$idx' class='$row_class' style='background-color: rgba({$status_colors[$error['status']][1]}, var(--{$opacity}));'>
        <td><img alt='realm' src='$realm_img'/></td>
        <td>{$error['datetime']} UTC</br>{$error['map']}</br>{$error['quantity']} time(s)</td>
        <td>
            <pre>{$error['message']}</br>{$error['stack']}</pre>
        </td>
        <td>
            <div class="tooltip">
                <span id='row-$idx-status'>{$status_colors[$error['status']][0]}</span>
                <span class="tooltip-text row-tooltip">
                    <form class='status-form' name='status-form'>
    EOD;
                        foreach($status_colors as $key => $error_type) {
                            $checked = $error['status'] == $key ? 'checked' : '';

                            echo 
                            "<div style='background-color: rgb({$error_type[1]}, 255);'>
                                <input $disabled type='radio' id='status-radio-{$key}-{$idx}' name='update_status' value='{$key}' $checked>
                                <label for='status-radio-{$key}-{$idx}'>
                                    {$error_type[0]}
                                </label>
                            </div>";
                        }
    echo <<<EOD
                        <input type='hidden' name='idx' value='$idx'\>
                        <input $disabled type='submit' class='row-set-button' value='Submit'>
                    </form>
                </span>
            </div>
        </td>
    </tr>
    EOD;

    $odd = !$odd;
}
?>
<script>
let last_hovered_tooltip
let last_hovered_tooltip_timer

let status_colors = [
    <?php foreach ($status_colors as $status_color) { echo "{ 'color' : '" . $status_color[1] . "', 'status' : '" . $status_color[0] . "' },"; } ?>
];

function UpdateErrorRow(idx, update_status) {
    let opacity = $('#row-' + idx).hasClass('row-odd') ? 'odd-opacity' : 'even-opacity'

    $('#row-' + idx).css({ 'background-color' : 'rgba(' + status_colors[update_status]['color'] + ', var(--' + opacity + '))' })
    $('#row-' + idx + '-status').html(status_colors[update_status]['status'])
}

$(".tooltip").mouseover(function(e) {
    let cur_hovered_element = $(this.lastElementChild)
    cur_hovered_element.css("visibility", 'visible')

    if (last_hovered_tooltip && last_hovered_tooltip[0] != cur_hovered_element[0]) {
        clearTimeout(last_hovered_tooltip_timer)
        last_hovered_tooltip.css("visibility", 'hidden')
        last_hovered_tooltip = null
    }
})

$(".tooltip").mouseout(function(e) {
    last_hovered_tooltip = $(this.lastElementChild)

    clearTimeout(last_hovered_tooltip_timer)

    last_hovered_tooltip_timer = setTimeout(function () { 
        last_hovered_tooltip.css("visibility", 'hidden')
        last_hovered_tooltip = null
    }, 1000)
})

$(".tooltip-text").mouseover(function(e) {
    clearTimeout(last_hovered_tooltip_timer)
})

$(".tooltip-text").mouseout(function(e) {
    clearTimeout(last_hovered_tooltip_timer)

    last_hovered_tooltip_timer = setTimeout(function () { 
        last_hovered_tooltip.css("visibility", 'hidden')
        last_hovered_tooltip = null
    }, 1000)
})

$('.status-form').submit(function (e) {
    e.preventDefault()

    let idx = -1
    let update_status = -1
    let form = $(this)[0]

    for (i = 0; i < form.length; i++) {
        if (form[i].type == "radio" && form[i].checked) {
            update_status = form[i].value
        }

        if (form[i].type == "hidden" && form[i].name == "idx") {
            idx = form[i].value
        }

        if (update_status != -1 && idx != -1) {
            break
        }
    }

    if (update_status != -1 && idx != -1) {
        $.ajax({
            context: this,
            url: 'index.php?addon=<?= $table_name ?><?= $auth_link ?>',
            type: 'POST',
            dataType: 'html',
            data: {
                "idx": idx,   
                "update_status": update_status
            }
        }).done(function(data) {
            UpdateErrorRow(idx, update_status)
        }).fail(function(data) {
            alert('Ajax failed: ' + data)
        })
    } else {
        console.error("Failed to select values from field.")
    }
})
</script>
</table>
</body>
</html>

<?php
require "general/finish.php";
?>