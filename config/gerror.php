<?php
// Close connecion
if (isset($CONNECTION))
    CloseConnection($CONNECTION);

// Host setup
$sql = array(
    "host" => "gerror-mariadb",
    "login" => getenv('MYSQL_USER'),
    "password" => getenv('MYSQL_PASSWORD'),
    "database" => getenv('MYSQL_DATABASE')
);

// Database CONNECTION
$CONNECTION = OpenConnection($sql);

if ( ! isset($CONNECTION)) {
    echo "Database CONNECTION failed";
    exit(1);
}

// Charset
SetCharset($CONNECTION);
?>
