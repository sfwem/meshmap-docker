<?php
$INCLUDE_DIR = "../..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
@include $INCLUDE_DIR . "/custom.inc";

$sql_connection = wxc_connectToMySQL();

$query = "update map_info set script_last_run = '2018-01-01 00:00:00' where id = 'NODEINFO'";

wxc_putMySql($query);
mysqli_close($sql_connection);
?>
