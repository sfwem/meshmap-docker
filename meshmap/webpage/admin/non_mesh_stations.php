<?php
/*Aug 12, 2017 8:52:20 PM
*non_mesh_stations.php
*Eric Satterlee - KG6WXC
*/
//simple page to add non-mesh stations to the map
$INCLUDE_DIR = "../..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
@include $INCLUDE_DIR . "/custom.inc";

$sql_connection = wxc_connectToMySQL();

?>
<script>
$("#add_non_mesh_station").submit(function(event) {
    event.preventDefault();
    
    var $form = $(this),
    	which = $form.find("input[type='hidden'][name='add_new_non_mesh_station']").val(),
        name = $form.find("input[name='station_name']").val(),
        description = $form.find("input[name='station_description']").val(),
        type = $form.find("select[name='station_type']").val(),
        lat = $form.find("input[name='station_lat']").val(),
        lon = $form.find("input[name='station_lon']").val(),
        url = $form.attr("action");
    
    var posting = $.post(url, {add_new_non_mesh_station: which,
        				station_name: name,
        				station_description: description,
        				station_type: type,
        				station_lat: lat,
        				station_lon: lon
        			});

	//posting.success: function(reponse) {
    //    $('#admin_content').html(reponse);
    //}
    //posting.done(function(data) {
    //    var content = $(data).find("#content");
    //    $("#admin_content").empty().append(content);
    //});
    posting.done(function(data) {
        //var content = $(data).html;
        $("#admin_content").html(data);
    });
});
</script>
<script>
$(".remove_non_mesh_station_form").submit(function(event) {
    event.preventDefault();
    
    var $form = $(this),
    	which = $form.find("input[type='hidden'][name='remove_non_mesh_station']").val(),
        name = $form.find("input[type='hidden'][name='station_name']").val(),
        description = $form.find("input[type='hidden'][name='station_description']").val(),
        type = $form.find("input[type='hidden'][name='station_type']").val(),
        lat = $form.find("input[type='hidden'][name='station_lat']").val(),
        lon = $form.find("input[type='hidden'][name='station_lon']").val(),
        url = $form.attr("action");
    
    var posting = $.post(url, {remove_non_mesh_station: which,
        				station_name: name,
        				station_description: description,
        				station_type: type,
        				station_lat: lat,
        				station_lon: lon
        			});

	//posting.success: function(reponse) {
    //    $('#admin_content').html(reponse);
    //}
    //posting.done(function(data) {
    //    var content = $(data).find("#content");
    //    $("#admin_content").empty().append(content);
    //});
    posting.done(function(data) {
        //var content = $(data).html;
        $("#admin_content").html(data);
    });
});
</script>
<script>
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("existing_non_mesh_stations");
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

<form action="non_mesh_stations.php" id="add_non_mesh_station" method="POST">
	Input a new "Non-MESH" station:<br>
	Name: <input type="text" name="station_name" value="">
	Description: <input type="text" name="station_description" value="">
	Type: <select name="station_type">
		<option value="operator">Radio Operator</option>
		<option value="firedepartment">Fire Department</option>
		<option value="police">Police Station</option>
		<option value="eoc">EOC</option>
		<option value="hospital">Hospital</option>
		<option value="futuremesh">Future Mesh</option>
	</select>
	Lat: <input type="text" name="station_lat" value="">
	Lon: <input type="text" name="station_lon" value="">
	<input type="hidden" name="add_new_non_mesh_station" value="add_new_non_mesh_station">
	<input type="submit" value="Add">
</form>


<?php
//add something new
if ((isset($_POST['add_new_non_mesh_station']) == "add_new_non_mesh_station") && (isset($_POST['station_name'])) && (isset($_POST['station_description'])) && (isset($_POST['station_type'])) && (isset($_POST['station_lat'])) && (isset($_POST['station_lon']))) {
    $newStationName = $_POST['station_name'];
    $newStationDescription = $_POST['station_description'];
    $newStationType = $_POST['station_type'];
    $newStationLat = $_POST['station_lat'];
    $newStationLon = $_POST['station_lon'];
    
    
    $addedToSql = wxc_putMySQL("INSERT INTO marker_info (name, description, type, lat, lon) VALUES ('$newStationName', '$newStationDescription', '$newStationType', '$newStationLat', '$newStationLon')");
    if ($addedToSql = 1) {
        echo "<boldText>Sucessfully added <greenText>" . $newStationName . "</greenText> to the database</boldText>.<br>";
        //UNSET THE $_POST array
        $_POST = array();
    }
}
//remove something already there
if (isset($_POST['remove_non_mesh_station']) == "remove_non_mesh_station") {
    $removedName = $_POST['station_name'];
    $removedDesc = $_POST['station_description'];
    $removedType = $_POST['station_type'];
    $removedLat = $_POST['station_lat'];
    $removedLon = $_POST['station_lon'];
    
    $removeFromSql = wxc_putMySQL("DELETE FROM marker_info WHERE name = '$removedName' AND description = '$removedDesc' AND type = '$removedType' AND lat = '$removedLat' AND lon = '$removedLon'");
    if ($removeFromSql = 1) {
        echo "<boldText>Removed <redText>" . $removedName . "</redText> from the database</boldText>.<br>";
        //UNSET THE $_POST array
        $_POST = array();
    }
}
?>

<?php
//display what is already there
$nonMeshStationsAndMarkers = mysqli_query($GLOBALS['sql_connection'], "SELECT id, name, description, type, lat, lon FROM marker_info") or die ("Could not get list of non mesh stations" . mysqli_error($GLOBALS['sql_connection']));
if ($nonMeshStationsAndMarkers) {
    $nonMeshStationsAndMarkers = mysqli_fetch_all($nonMeshStationsAndMarkers, MYSQLI_ASSOC);
    echo "These are the non-mesh stations already in the database:<br>\n";
    echo "<table id=\"existing_non_mesh_stations\">\n";
    echo "<tr>\n";
    echo "<th class=\"pointerCursor\" onclick=\"sortTable(0)\"><boldText>Name</boldText></th>\n";
    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>Description</boldText></th>\n";
    echo "<th class=\"pointerCursor\" onclick=\"sortTable(2)\"><boldText>Type</boldText></th>\n";
    echo "<th class=\"pointerCursor\" onclick=\"sortTable(3)\"><boldText>Lat</boldText></th>\n";
    echo "<th class=\"pointerCursor\" onclick=\"sortTable(4)\"><boldText>Lon</boldText></th>\n";
    echo "</tr>\n";
    foreach ($nonMeshStationsAndMarkers as $value) {
        echo "\n<tr><td contenteditable='true' onBlur=\"saveToDatabase(this, 'name', '" . $value['id'] ."')\" " .
	            "onClick=\"showEdit(this);\">" . $value['name'] . "</td>" .
            "<td contenteditable='true' onBlur=''>" . $value['description'] . "</td>" .
            "<td contenteditable='true' onBlur=''>" . $value['type'] . "</td>" .
            "<td contenteditable='true' onBlur=''>" . $value['lat'] . "</td>" .
            "<td contenteditable='true' onBlur=''>" . $value['lon'] . "</td>" .
            "<td class=\"BackgroundColor\">" .
            "<form action=\"non_mesh_stations.php\" class=\"remove_non_mesh_station_form\" method=\"POST\">" .
            "<input type=\"hidden\" name=\"station_name\" value=\"" . $value['name'] . "\">" .
            "<input type=\"hidden\" name=\"station_description\" value=\"" . $value['description'] . "\">" .
            "<input type=\"hidden\" name=\"station_type\" value=\"" . $value['type'] . "\">" .
            "<input type=\"hidden\" name=\"station_lat\" value=\"" . $value['lat'] . "\">" .
            "<input type=\"hidden\" name=\"station_lon\" value=\"" . $value['lon'] . "\">" .
            "<input type=\"hidden\" name=\"remove_non_mesh_station\" value=\"remove_non_mesh_station\">" .
            "<input type=\"submit\" value=\"Remove\">" .
            "</form>" .
            "</td>" .
            "</tr>";
    }
    echo "\n\n</table>\n";
    
    //adapted from some site called PHP Pot (yeah, it's not that... I thought that too at first... :) )
    $jsEditing = <<< EOD
<script>
	function showEdit(editableObj) {
		$(editableObj).css("background", "#FFF");
	}
	function saveToDatabase(editableObj, column, id) {
		$(editableObj).css("background", "#FFF url(loaderIcon.gif) no-repeater right");
		
		//quick fix for a funny <br> that keeps showing up in the post data
		var edit = editableObj.innerHTML.replace(/<br>/, "");
		
		$.ajax({
			url: "update_nonmesh_marker.php",
			type: "POST",
			
			//part of the quick fix
			//data: 'column='+column+'&editval='+editableObj.innerHTML+'&id='+id,
			
			data: 'nonmesh=nonmesh&column='+column+'&editval='+edit+'&id='+id,
			success: function(data) {
				//$(editableObj).css("background", "#FDFDFD");
	            $(editableObj).css("background-color", "inherit");
	            $(editableObj).html(data);
				$("#admin_content").load("non_mesh_stations.php");
			}
			
		});
	}
</script>
EOD;
    
    echo $jsEditing . "\n";
}
?>
