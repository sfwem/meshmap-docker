<?php
//simple page to change node locations on the map
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
	
	$nodeQuery = "SELECT wifi_mac_address, node, lat, lon, grid_square, wlan_ip, if(location_fix, 'yes', 'no') as location_fix, last_seen FROM node_info ORDER by location_fix DESC";
	
	$nodeInfo = mysqli_query($sql_connection, $nodeQuery) or die ("Could not get list of mesh nodes" . mysqli_error($GLOBALS['sql_connection']));
	
	if ($nodeInfo) {
	    $nodeInfo = mysqli_fetch_all($nodeInfo, MYSQLI_ASSOC);
	    //echo "Search by Node Name: <form> \n";
	    //echo "<input type='text' name='nodeName' value='Node Name'>\n";
	    //echo "<input type='submit' value='Search'>\n";
	    //echo "</form>\n";
	    //echo "<button onclick='searchForNode'>Search</button>\n";
	    echo "<s>Searching functions not implemented yet, sorry.</s><br>\n";
	    echo "Use your browsers search functions to find a node!<br><br>\n";
	    echo "Click on the column headers to sort (beware, it can take some time to sort by name... seriously!)<br>\n";
	    echo "To edit a nodes location, just click on the lat or lon and change it.<br>\n";
	    echo "When you are done editing just click outside of the table and the database will be updated.<br>\n";
		echo "When a nodes location is \"fixed\", it is no longer updated from the polling scripts.<br>\n";
	    echo "To have the node be updated by the polling scripts again, set \"location fixed\" to be \"no\" for that node.<br>\n";
	    echo "For now, you *MUST* type these values to avoid accidental editing.<br><br>\n";
	    echo "(When you edit either \"lat\" or \"lon\" the location_fix field *does* get updated in the database, but it does not yet automatically update here)<br>\n";
		echo "(Click on \"Change Node Location\" above to refresh this page. It'll bring any new \"fixed\" nodes to the top.)<br>\n";
	    echo "(Clicking on any of the \"navigation links\" will reload that page and you will see the updated values.)<br><br>\n";
	    echo "Nodes and locations currently in the database:<br>\n";
	    echo "<table id=\"node_table\">\n";
	    echo "<tr>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(0)\"><boldText>Name</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>Lat</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(2)\"><boldText>Lon</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(3)\"><boldText>Grid</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(4)\"><boldText>Location Fixed</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(5)\"><boldText>Last Seen</boldText></th>\n";
	    echo "</tr>\n";
	    foreach ($nodeInfo as $value) {
	    	$localTimeZone = new DateTimeZone($USER_SETTINGS['localTimeZone']);
	    	$lastSeen = new DateTime($value['last_seen'], $localTimeZone);
	    	date_timezone_set($lastSeen, $localTimeZone);
	//         if ($value['location_fix'] == 0) {
	//             $yesOrNo = "No";
	//         }elseif ($value['location_fix'] == 1) {
	//             $yesOrNo = "Yes";
	//         }
	        echo "\n<tr><td>" . $value['node'] . "</td>" .
	            //"<td>" . $value['description'] . "</td>" .
	            //"<td>" . $value['type'] . "</td>" .
	            "<td contenteditable='true' onBlur=\"saveToDatabase(this, 'lat', '" . $value['node'] ."')\" " .
	            "onClick=\"showEdit(this);\">" . $value['lat'] . "</td>" .
	            "<td contenteditable='true' onBlur=\"saveToDatabase(this, 'lon', '" . $value['node'] ."')\" " .
	            "onClick=\"showEdit(this);\">" . $value['lon'] . "</td>" .
	            "<td>" . $value['grid_square'] . "</td>" .
	            "<td contenteditable='true' onBlur=\"saveToDatabase(this, 'location_fix', '" . $value['node'] ."')\" " .
	            "onClick=\"showEdit(this);\">" . $value['location_fix'] . "</td>" .
	            "<td>" . date_format($lastSeen, 'Y-m-d H:i:s T') . "</td>" .
	            "<td class='BackgroundColor'>\n" .
				"<form class='remove_node_from_db' action='remove_node.php' method='post'>\n" .
				"<input type='hidden' name='wifi_mac_address' value='" . $value['wifi_mac_address'] . "'>\n" .
				"<input type='hidden' name='node' value='" . $value['node'] . "'>\n" .
				"<input type='hidden' name='wlan_ip' value='" . $value['wlan_ip'] . "'>\n" .
				"</form>\n" .				
	            "</td>" .
	            "</tr>";
	    }
	}
	
	//the extra Javascript stuff for this to all work
	
	//adapted from some site called PHP Pot (yeah, it's not that... I thought that too at first... :) )
	$jsEditing = <<< EOD
<script>
	function showEdit(editableObj) {
		$(editableObj).css("background", "#FFF");
	}
	function saveToDatabase(editableObj, column, node) {
		$(editableObj).css("background", "#FFF url(loaderIcon.gif) no-repeater right");

		//quick fix for a funny <br> that keeps showing up in the post data
		var edit = editableObj.innerHTML.replace(/<br>/, "");

		$.ajax({
			url: "update_location.php",
			type: "POST",

			//part of the quick fix
			//data: 'column='+column+'&editval='+editableObj.innerHTML+'&node='+node,

			data: 'column='+column+'&editval='+edit+'&node='+node,
			success: function(data) {
				//$(editableObj).css("background", "#FDFDFD");
	            $(editableObj).css("background-color", "inherit");
	            $(editableObj).html(data);
				$("#admin_content").load("update_node_location.php");
			}

		});
	}
</script>
EOD;
	echo $jsEditing . "\n";
	
	//and this was pretty much straight copied from w3schools.com (https://www.w3schools.com/howto/howto_js_sort_table.asp)
	$jsSorting = <<< EOD
<script>
	function sortTable(n) {
	  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	  table = document.getElementById("node_table");
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
	
	echo $jsSorting . "\n";
}
?>
