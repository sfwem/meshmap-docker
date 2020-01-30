<?php
/*Aug 7, 2017 9:49:30 AM
*view_removed_nodes.php
*Eric Satterlee - KG6WXC
*/
//simple page to to view removed nodes
//nodes are removed from the main database table
//if they have not been heard from in 30 days
session_start();
if (!isset($_SESSION['userLoggedIn'])) {
	echo "You are not logged in!<br>\n";
	echo "This page should be run from within the admin interface!\n";
	exit;
}else {
	$INCLUDE_DIR = "../..";
	$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
	require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
	@include $INCLUDE_DIR . "/custom.inc";
	
	$sql_connection = wxc_connectToMySQL();
	
	$removedNodes = mysqli_query($GLOBALS['sql_connection'], "SELECT node, last_seen, time_removed FROM removed_nodes") or die ("Could not get list of removed nodes" . mysqli_error($GLOBALS['sql_connection']));
	if ($removedNodes) {
	    $removedNodes = mysqli_fetch_all($removedNodes, MYSQLI_ASSOC);
	    echo "Nodes that have been removed from the main table:<br>\n";
	    echo "(this happens after not hearing from them for over " . $USER_SETTINGS['node_expire_interval'] . " days)<br>\n";
	    echo "<table id=\"removed_nodes\">\n";
	    echo "<tr>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(0)\"><boldText>Name</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>Last Seen</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(2)\"><boldText>Time Removed</boldText></th>\n";
	//    echo "<th onclick=\"sortTable(3)\"><boldText>Lat</boldText></th>\n";
	//    echo "<th onclick=\"sortTable(4)\"><boldText>Lon</boldText></th>\n";
	    echo "</tr>\n";
	    foreach ($removedNodes as $value) {
	    	$tz = new DateTimeZone($GLOBALS['USER_SETTINGS']['localTimeZone']);
	    	$lastSeen = new DateTime($value['last_seen'], $tz);
	    	$timeRemoved = new DateTime($value['time_removed'], $tz);
	        echo "\n<tr><td>" . $value['node'] . "</td>" .
	 	        "<td>" . date_format($lastSeen, 'Y-m-d H:i:s T') . "</td>" .
	 	        "<td>" . date_format($timeRemoved, 'Y-m-d H:i:s T') . "</td>" .
	//            "<td>" . $value['lat'] . "</td>" .
	//            "<td>" . $value['lon'] . "</td>" .
	            "<td></td>" .
	            "</tr>";
	    }
	    echo "\n\n</table>\n";
	}
	//pretty much copied from w3schools.com (https://www.w3schools.com/howto/howto_js_sort_table.asp)
	$js = <<< EOD
<script>
	function sortTable(n) {
	  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	  table = document.getElementById("removed_nodes");
	  switching = true;
	  //Set the sorting direction to ascending:
	  dir = "asc";
	  /*Make a loop that will continue until
	  no switching has been done:*/
	  while (switching) {
	    //start by saying: no switching is done:
	    switching = false;
	    rows = table.getElementsByTagName("TR");
	    /*Loop through all table rows (except the
	    first, which contains table headers):*/
	    for (i = 1; i < (rows.length - 1); i++) {
	      //start by saying there should be no switching:
	      shouldSwitch = false;
	      /*Get the two elements you want to compare,
	      one from current row and one from the next:*/
	      x = rows[i].getElementsByTagName("TD")[n];
	      y = rows[i + 1].getElementsByTagName("TD")[n];
	      /*check if the two rows should switch place,
	      based on the direction, asc or desc:*/
	      if (dir == "asc") {
	        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      } else if (dir == "desc") {
	        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      }
	    }
	    if (shouldSwitch) {
	      /*If a switch has been marked, make the switch
	      and mark that a switch has been done:*/
	      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
	      switching = true;
	      //Each time a switch is done, increase this count by 1:
	      switchcount ++;
	    } else {
	      /*If no switching has been done AND the direction is "asc",
	      set the direction to "desc" and run the while loop again.*/
	      if (switchcount == 0 && dir == "asc") {
	        dir = "desc";
	        switching = true;
	      }
	    }
	  }
	}
</script>
EOD;
	
	echo $js . "\n";
}
?>
