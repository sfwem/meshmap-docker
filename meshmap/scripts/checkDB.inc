<?php
function wxc_check4Dupes() {
	//this function should clear out any duplicate entries in the node_info table.
	//sometimes, for various reasons, you'll end up with 2 nodes, with the same IP.
	//that causes the trigger topology_get_latlons_brg_dist to explode,
	//and you see no link lines on the map
	$findDupeQuery = mysqli_query($GLOBALS['sql_connection'], "select node, wlan_ip from node_info group by wlan_ip having count(wlan_ip) > 1");
	if(mysqli_num_rows($findDupeQuery) > 0) {
		echo "FOUND DUPLICATES IN NODE_INFO TABLE! NOW REPAIRING...\n";
		echo "Removed from Database:\n";
		$findDupeQuery = mysqli_fetch_all($findDupeQuery, MYSQLI_ASSOC);
		foreach($findDupeQuery as $dupe) {
			mysqli_query($GLOBALS['sql_connection'], "delete from node_info where wlan_ip = '" . $dupe['wlan_ip'] . "'");
			echo "\t" . $dupe['node'] . " (and it's duplicate) with wlan_ip's of " . $dupe['wlan_ip'] . "\n";
		}
	}
}
function wxc_checkDB () {
	//this function is now in its own file
	
	//removing the olsrinfo_json binary blob from the database tables
	//it's just taking up space and never getting used...
	$olsrinfoQuery = "select table_name from information_schema.columns where table_schema = '" .
	$GLOBALS['USER_SETTINGS']['sql_db'] . "' and column_name = 'olsrinfo_json'";
	$jsonBlobs = mysqli_query($GLOBALS['sql_connection'], $olsrinfoQuery);
	$olsrinfojsonBlobsNum = mysqli_num_rows($jsonBlobs);
	if ($olsrinfojsonBlobsNum != 0) {
		//$jsonBlobs = wxc_getMySql($sysinfoQuery);
		//var_dump($jsonBlobs);
		foreach ($jsonBlobs as $table) {
			foreach ($table as $table_name) {
				$alterQuery = 'alter table ' . $table_name . ' drop column olsrinfo_json';
				wxc_putMySql($alterQuery);
				$mesg = "olsrinfo_json binary blob removed from database table: " . $table_name . "!";
				wxc_echoWithColor($mesg, "red");
				echo "\n";
			}
		}
		unset($jsonBlobs);
		unset($olsrinfojsonBlobsNum);
		unset($olsrinfoQuery);
		wxc_echoWithColor("You had an old version of the Database tables. ", "orange");
		wxc_echoWithColor("Fixed now...", "green");
		echo "\nThis will make the database a lot smaller!\n";
		echo "\n";
	}else {
		unset($jsonBlobs);
		unset($olsrinfojsonBlobsNum);
		unset($olsrinfoQuery);
	}
			
	//removing the sysinfo_json binary blob from the database tables
	//it's just taking up space and never getting used...
	$sysinfoQuery = "select table_name from information_schema.columns where table_schema = '" .
	$GLOBALS['USER_SETTINGS']['sql_db'] . "' and column_name = 'sysinfo_json'";
	$jsonBlobs = mysqli_query($GLOBALS['sql_connection'], $sysinfoQuery);
	$sysinfojsonBlobsNum = mysqli_num_rows($jsonBlobs);
	if ($sysinfojsonBlobsNum != 0) {
		//$jsonBlobs = wxc_getMySql($sysinfoQuery);
		//var_dump($jsonBlobs);
		foreach ($jsonBlobs as $table) {
			foreach ($table as $table_name) {
				$alterQuery = 'alter table ' . $table_name . ' drop column sysinfo_json';
				wxc_putMySql($alterQuery);
				$mesg = "sysinfo_json binary blob removed from database table: " . $table_name . "!";
				wxc_echoWithColor($mesg, "red");
				echo "\n";
			}
		}
		unset($jsonBlobs);
		unset($sysinfojsonBlobsNum);
		unset($sysinfoQuery);
		wxc_echoWithColor("This is a good thing! It was just taking up space", "green");
		echo "\nThis will make the database smaller!\n";
		echo "\n";
	}else {
		unset($jsonBlobs);
		unset($sysinfojsonBlobsNum);
		unset($sysinfoQuery);
	}
					
	//people use the longest dang names for their nodes!
	//it's like they want to make you type for a long time just to get to their node. :)

	//$columnNodeNames = mysqli_num_rows(mysqli_query($GLOBALS['sql_connection'], $query))
	$nodeFieldsQuery = "select table_name from information_schema.columns where table_schema = '" .
	$GLOBALS['USER_SETTINGS']['sql_db'] . "' and column_name = 'node' and character_maximum_length <= 69";
	$nodeFields = mysqli_query($GLOBALS['sql_connection'], $nodeFieldsQuery);
	$nodeFieldsNum = mysqli_num_rows($nodeFields);
	if ($nodeFieldsNum != 0) {
		foreach ($nodeFields as $table) {
			foreach ($table as $table_name) {
				//if ($nodeFields['size'] <= 50) {
				//echo "\n\nFirst ECHO - Key: " .$key . " Value: " . $value . "\n\n";
				//echo "Table: " . $key['table_name'] . "\n";
				//echo "size: " . $key['size'] . "\n";
				wxc_putMySql("alter table $table_name modify column node varchar(70)");
				$mesg = "Altered column \"node\" to hold more characters in table: $table_name!";
				wxc_echoWithColor($mesg, "green");
				echo "\n";
				//need to change this one too, it has a different name, but it's the same thing.
				if ($table_name == "topology") {
					wxc_putMySql("alter table topology modify column linkto varchar(70)");
					$mesg = "... also altered column \"linkto\" to hold more characters in table: $table_name!";
					wxc_echoWithColor($mesg, "orange");
					echo "\n";
				}
			//}
			}
		}
		echo "\n";
		unset($nodeFields);
		unset($nodeFieldsQuery);
		unset($nodeFieldsNum);
	}else {
		unset($nodeFields);
		unset($nodeFieldsQuery);
		unset($nodeFieldsNum);
	}

	//Just like above (and for the same reasons)
	//there are 1 or 2 columns called "name" that are used also
	//This will also allow for longer names in the non-mesh markers area
	$nameFieldsQuery = "select table_name from information_schema.columns where table_schema = '" .
	$GLOBALS['USER_SETTINGS']['sql_db'] . "' and column_name = 'name' and character_maximum_length <= 69";
	$nameFields = mysqli_query($GLOBALS['sql_connection'], $nameFieldsQuery);
	$nameFieldsNum = mysqli_num_rows($nameFields);
	if ($nameFieldsNum != 0){
		foreach ($nameFields as $table) {
			foreach ($table as $table_name) {
				//if ($nameFields['size'] <= 50) {
				if ($table_name == "marker_info") {
					//$query = ;
					wxc_putMySql("alter table $table_name modify column name varchar(128)");
					$mesg = "Altered column \"name\" to hold more characters in table: $table_name!";
					wxc_echoWithColor($mesg, "green");
					echo "\n";
				}else {
					wxc_putMySql("alter table $table_name modify column name varchar(70)");
					$mesg = "Altered column \"name\" to hold more characters in table: $table_name!";
					wxc_echoWithColor($mesg, "green");
					echo "\n";
				}
									//}
			}
		}
		echo "\n";
		unset($nameFields);
		unset($nameFieldsQuery);
		unset($nameFieldsNum);
	}else {
		unset($nameFields);
		unset($nameFieldsQuery);
		unset($nameFieldsNum);
	}

	//new columns in the main database table
	//these new columns are for the additions to the sysinfo.json file
	//that are in the newer AREDN firmware builds.
	//this might be only the beginning for these
	$newColumns1dot2 = mysqli_num_rows(mysqli_query($GLOBALS['sql_connection'],
		"show columns from " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " where field='uptime'"));
	if ($newColumns1dot2 == 0) {
		wxc_putMySql("alter table " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " add column uptime varchar(50) after last_seen");
		wxc_putMySql("alter table " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " add column loadavg varchar(128) after uptime");
		$mesg = "Added new columns to " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " for api_version 1.2";
		wxc_echoWithColor($mesg, "green");
		echo "\n";
	}

	$newColumns1dot2 = mysqli_num_rows(mysqli_query($GLOBALS['sql_connection'],
		"show columns from removed_nodes where field='uptime'"));
	if ($newColumns1dot2 == 0) {
		wxc_putMySql("alter table removed_nodes add column uptime varchar(50) after last_seen");
		wxc_putMySql("alter table removed_nodes add column loadavg varchar(128) after uptime");
		$mesg = "Added new columns to removed_nodes for api_version 1.2";
		wxc_echoWithColor($mesg, "green");
		echo "\n\n";
	}

	$newColumns1dot3 = mysqli_num_rows(mysqli_query($GLOBALS['sql_connection'],
		"show columns from " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " where field='services'"));
	if ($newColumns1dot3 == 0) {
		wxc_putMySql("alter table " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " add column services varchar(2048) after lan_ip");
		$mesg = "Added new columns to " . $GLOBALS['USER_SETTINGS']['sql_db_tbl_node'] . " for api_version 1.3";
		wxc_echoWithColor($mesg, "green");
		echo "\n";
	}

	$newColumns1dot3 = mysqli_num_rows(mysqli_query($GLOBALS['sql_connection'],
		"show columns from removed_nodes where field='services'"));
	if ($newColumns1dot3 == 0) {
		wxc_putMySql("alter table removed_nodes add column services varchar(2048) after lan_ip");
		$mesg = "Added new columns to removed_nodes for api_version 1.3";
		wxc_echoWithColor($mesg, "green");
		echo "\n\n";
	}
	unset($newColumns1dot2);
	unset($newColumns1dot3);
	
	//check for old trigger based on miles and update to new trigger based on kilometers.
	//the conversion to miles will be done later in the code.
	$oldDistanceTriggerQuery = wxc_getMySql("select action_statement from information_schema.triggers where action_statement like '%3959%'");
	if ($oldDistanceTriggerQuery) {
		$sql = "drop trigger topology_get_latlons_brg_dist";
		mysqli_query($GLOBALS['sql_connection'], $sql)
			or die(wxc_addColor("Could not drop SQL Trigger!", "red") . "\n" .
				"Please try to use the \"" . wxc_addColor("updateDBtoKilometers.sql", "bold") . "\" file.\n" .
				"Example: " . wxc_addColor("sudo mysql -D node_map < /home/pi/meshmap/updateDBtoKilometers.sql", "bold") . "\n\n" .
				"or... try to fix the following,\n" .
				wxc_addColor("SQL Error:", "red") . "\n" .
				mysqli_error($GLOBALS['sql_connection']) . "\n\n");
		
		$sql = "CREATE TRIGGER `topology_get_latlons_brg_dist` BEFORE INSERT ON `topology` FOR EACH ROW BEGIN

			-- get locations and names for each end of the link
			IF NEW.node IS NOT NULL THEN
				SET NEW.nodelat = (SELECT lat FROM node_info WHERE wlan_ip = NEW.node AND (lat IS NOT NULL && lat != 0 && lat != 0.00));
				SET NEW.nodelon = (SELECT lon FROM node_info WHERE wlan_ip = NEW.node AND (lon IS NOT NULL && lon != 0 && lon != 0.00));
				SET NEW.node = (SELECT node FROM node_info WHERE wlan_ip = NEW.node);
			END IF;
			IF NEW.linkto IS NOT NULL THEN
				SET NEW.linklat = (SELECT lat FROM node_info WHERE wlan_ip = NEW.linkto AND (lat IS NOT NULL && lat != 0 && lat != 0.00));
				SET NEW.linklon = (SELECT lon FROM node_info WHERE wlan_ip = NEW.linkto AND (lon IS NOT NULL && lon != 0 && lon != 0.00));
				SET NEW.linkto = (SELECT node FROM node_info WHERE wlan_ip = NEW.linkto);
			END IF;

			-- figure out distance and bearing

			SET NEW.bearing = round(mod(degrees(atan2(sin(radians(NEW.linklon)-radians(NEW.nodelon))*cos(radians(NEW.linklat)), cos(radians(NEW.nodelat))*sin(radians(NEW.linklat))-sin(radians(NEW.nodelat))*cos(radians(NEW.linklat))*cos(radians(NEW.linklon)-radians(NEW.nodelon)))) + 360,360),1);
			SET NEW.distance = round(2*asin(sqrt(pow(sin((radians(NEW.linklat)-radians(NEW.nodelat))/2),2)+cos(radians(NEW.nodelat))*cos(radians(NEW. linklat))*pow(sin((radians(NEW.linklon)-radians(NEW.nodelon))/2),2)))*6371,2);

			END;";
		
		mysqli_query($GLOBALS['sql_connection'], $sql)
			or die(wxc_addColor("Could not recreate SQL Trigger to use Kilometers.", "red") . "\n" .
				wxc_addColor("The Trigger may have already been dropped as well!", "redBold") . "\n" .
				"Please try to use the \"". wxc_addColor("updateDBtoKilometers.sql", "bold") . "\" file.\n" .
				"Example: " . wxc_addColor("sudo mysql -D node_map < /home/pi/meshmap/updateDBtoKilometers.sql", "bold") . "\n\n" .
				"or... try to fix the following,\n" .
				wxc_addColor("SQL Error:", "red") . "\n" .
				mysqli_error($GLOBALS['sql_connection']) . "\n");
		
		unset($oldDistanceTriggerQuery);
		unset($sql);
		$mesg = "Changed database to have default distance in Kilometers.";
		wxc_echoWithColor($mesg, "green");
		echo "\n\n";
	}
}
?>
