<?php
/*Aug 7, 2017 9:49:30 AM
*view_ignored_nodes.php
*Eric Satterlee - KG6WXC
*/
//updated to add the remove button may 2018
//
//simple page to to view/remove the ignored nodes/hosts
//Nodes end up here if they respond to the polling with an error
//(404, host not found, no route, refused, etc..)
//
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
	
	if (isset($_POST['remove_ignored_node']) == "remove_ignored_node") {
		$ip = $_POST['ip'];
		$name = $_POST['name'];
		$reason = $_POST['reason'];
		
		$removeTheNode = wxc_putMySql("delete from hosts_ignore where ip = '$ip' and name = '$name' and reason = '$reason'");
		if ($removeTheNode) {
			echo "<strong><greenText>Succesfully removed: " . $name . " from the list of ignored hosts.</greenText></strong><br>\n";
		}
		$_POST = array();
	}
	$ignoredNodes = mysqli_query($GLOBALS['sql_connection'], "SELECT ip, name, reason, timestamp FROM hosts_ignore") or die ("Could not get list of removed nodes" . mysqli_error($GLOBALS['sql_connection']));
	if ($ignoredNodes) {
	    $ignoredNodes = mysqli_fetch_all($ignoredNodes, MYSQLI_ASSOC);
	    echo "Nodes (or other devices) that have been ignored due to not responding in some expected way.<br>\n";
	    echo "Network issues can also cause nodes to show up here.<br>\n";
	    echo "<table id=\"ignored_nodes\">\n";
	    echo "<tr>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(0)\"><boldText>IP</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>Name</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(2)\"><boldText>Reason</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(3)\"><boldText>TimeStamp</boldText></th>\n";
	//    echo "<th onclick=\"sortTable(3)\"><boldText>Lat</boldText></th>\n";
	//    echo "<th onclick=\"sortTable(4)\"><boldText>Lon</boldText></th>\n";
	    echo "</tr>\n";
	    foreach ($ignoredNodes as $value) {
	    	$tz = new DateTimeZone($GLOBALS['USER_SETTINGS']['localTimeZone']);
	    	$datetime = new DateTime($value['timestamp'], $tz);
	        echo "\n<tr><td>" . $value['ip'] . "</td>" .
	            "<td>" . $value['name'] . "</td>" .
	            "<td>" . $value['reason'] . "</td>" .
	            "<td>" . date_format($datetime, 'Y-m-d H:i:s T') . "</td>" .
	//            "<td>" . $value['timestamp'] . "</td>" .
	//            "<td>" . $value['lon'] . "</td>" .
	            "<td>" .
	            "<form class='remove_ignored_node_form' action='view_ignored_nodes.php' method='post'>" .
	            "<input type='hidden' name='remove_ignored_node' value='remove_ignored_node'>\n" .
	            "<input type='hidden' name='ip' value='" . $value['ip'] . "'>\n" .
	            "<input type='hidden' name='name' value='" . $value['name'] . "'>\n" .
	            "<input type='hidden' name='reason' value='" . $value['reason'] . "'>\n" .
	            "<input type='submit' value='Remove'>\n" .
	            "</form>" .
	            "</td>" .
	            "</tr>";
	    }
	    echo "\n\n</table>\n";
	}
	//pretty much copied from w3schools.com (https://www.w3schools.com/howto/howto_js_sort_table.asp)
	$js = <<< EOD
<script>
$(".remove_ignored_node_form").submit(function(event) {

	event.preventDefault();

	var form = $(this),
	remove_ignored_node = form.find("input[type='hidden'][name='remove_ignored_node']").val(),
	ip = form.find("input[type='hidden'][name='ip']").val(),
	name = form.find("input[type='hidden'][name='name']").val(),
	reason = form.find("input[type='hidden'][name='reason']").val(),
	url = form.attr("action");

	var posting = $.post(url, {
						remove_ignored_node: remove_ignored_node,
						ip: ip,
						name: name,
						reason: reason
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});
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
