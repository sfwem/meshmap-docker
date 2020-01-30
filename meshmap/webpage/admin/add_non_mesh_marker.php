<?php
session_start();
if (!isset($_SESSION['userLoggedIn'])) {
	echo "You are not logged in!<br>\n";
	echo "This page should be run from within the admin interface!\n";
	exit;
}else {
	if ($_POST['action'] == "add_non_mesh_station") {
		echo "<form action=\"adminpage.php\" method=\"POST\">\n";
		echo "Input a new \"Non-MESH\" station:<br>";
		echo "Name: <input type=\"text\" name=\"new_station_name\" value=\"\"> ";
		echo "Description: <input type=\"text\" name=\"new_station_description\" value=\"\"> ";
		echo "Type: <select name=\"new_station_type\">";
		echo "<option value=\"operator\">Radio Operator</option>";
		echo "<option value=\"firedepartment\">Fire Department</option>";
		echo "<option value=\"police\">Police Station</option>";
		echo "<option value=\"futuremesh\">Future Mesh</option>";
		echo "</select>";
		echo "Lat: <input type=\"text\" name=\"new_station_lat\" value=\"\"> ";
		echo "Lon: <input type=\"text\" name=\"new_station_lon\" value=\"\"> ";
		echo "<input type=\"hidden\" name=\"action\" value=\"add_non_mesh_station\">";
		echo "<input type=\"hidden\" name=\"sub_action\" value=\"new_station\">";
		echo "<input type=\"hidden\" name=\"user\" value=\"" . $user . "\">";
		echo "<input type=\"hidden\" name=\"password\" value=\"" . $userInputPasswd . "\">";
		echo "<input type=\"submit\" value=\"Add\">";
		echo "</form>";
		if (($_POST['sub_action'] == "new_station") && (isset($_POST['new_station_name'])) && (isset($_POST['new_station_description'])) &&
				(isset($_POST['new_station_type'])) && (isset($_POST['new_station_lat'])) && (isset($_POST['new_station_lon']))) {
					$newStationName = strip_tags($_POST['new_station_name'], '<br>');
					$newStationDescription = strip_tags($_POST['new_station_description'], '<br>');
					$newStationType = $_POST['new_station_type'];
					$newStationLat = strip_tags($_POST['new_station_lat']);
					$newStationLon = strip_tags($_POST['new_station_lon']);
					
					$addedToSql = wxc_putMySQL("INSERT INTO marker_info (name, description, type, lat, lon) VALUES ('$newStationName', '$newStationDescription', '$newStationType', '$newStationLat', '$newStationLon')");
					if ($addedToSql = 1) {
						echo "Sucessfully added " . $newStationName . " to the database.<br>";
						unset($_POST['new_station_name']);
						unset($_POST['new_station_description']);
						unset($_POST['new_station_type']);
						unset($_POST['new_station_lat']);
						unset($_POST['new_station_lon']);
						unset($_POST['sub_action']);
					}
				}
				$nonMeshStationsAndMarkers = mysqli_query($GLOBALS['sql_connection'], "SELECT name, description, type, lat, lon FROM marker_info") or die ("Could not get list of non mesh stations" . mysqli_error($GLOBALS['sql_connection']));
				if ($nonMeshStationsAndMarkers) {
					$nonMeshStationsAndMarkers = mysqli_fetch_all($nonMeshStationsAndMarkers, MYSQLI_ASSOC);
					echo "This is what is in the database:<br>\n";
					echo "<table>";
					echo "<tr><th><b>Name</b></th><th><b>Description</b></th><th><b>Type</b></th><th><b>Lat</b></th><th><b>Lon</b></th></tr>";
					foreach ($nonMeshStationsAndMarkers as $value) {
						echo "<tr><td>" . $value['name'] . "</td>" .
								"<td>" . $value['description'] . "</td>" .
								"<td>" . $value['type'] . "</td>" .
								"<td>" . $value['lat'] . "</td>" .
								"<td>" . $value['lon'] . "</td></tr>";
					}
					echo "</table>";
				}
	}
}
?>
