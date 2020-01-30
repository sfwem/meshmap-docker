<?php
/*
 * Edit Existing Non Mesh Markers
 * June 2018 KG6WXC
 */
session_start();
if (!isset($_SESSION['userLoggedIn'])) {
	echo 'You are not logged in!<br>' . "\n";
	echo 'This page should be run from within the admin interface!' . "\n";
	exit;
}else {
	//nothing as a test
	$INCLUDE_DIR = '../..';
	$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . '/scripts/user-settings.ini');
	require $INCLUDE_DIR . '/scripts/wxc_functions.inc';
	@include $INCLUDE_DIR . '/custom.inc';
}
$sql_connection = wxc_connectToMySQL();
if ($_POST['nonmesh'] == 'nonmesh') {
	$query = 'UPDATE marker_info SET ' .
			$_POST['column'] . ' = ' . "'" . strip_tags($_POST['editval'], '<br>') . "'" .
			'WHERE id = ' . "'" . $_POST['id'] . "'";
	$result = wxc_putMySql($query);
}
$result = wxc_getMySql($query);
mysqli_close($sql_connection);
echo $result[$_POST['column']];
?>