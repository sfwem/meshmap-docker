<?php
/******
* parallel_node_polling script v3 by kg6wxc\eric satterlee kg6wxc@gmail.com
* Licensed under GPLv3 or later
*
* This file is part of the Mesh Mapping System.
* The Mesh Mapping System is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* The Mesh Mapping System is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.  
*
* You should have received a copy of the GNU General Public License   
* along with The Mesh Mapping System.  If not, see <http://www.gnu.org/licenses/>.
******/

if (PHP_SAPI !== 'cli') {
    $file = basename($_SERVER['PHP_SELF']);
    exit("<style>html{text-align: center;}p{display: inline;}</style>
        <br><strong>This script ($file) should only be run from the
        <p style='color: red;'>command line</p>!</strong>
        <br>exiting...");
}
$INCLUDE_DIR = "..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
@include $INCLUDE_DIR . "/custom.inc";
//foreach (get_included_files() as $filename) {
//	if (strpos($filename, 'wxc_custom.inc')) {
//		$wxc_custom = 1;
//	}
//}

$ipAddr = $argv[1];
$do_sql = $argv[2];
$testNodePolling = $argv[3];
if ($do_sql) {
	$sql_connection =  wxc_connectToMySql();
}
$sql_db_tbl = $USER_SETTINGS['sql_db_tbl'];
$sql_db_tbl_node = $USER_SETTINGS['sql_db_tbl_node'];
$sql_db_tbl_topo = $USER_SETTINGS['sql_db_tbl_topo'];

$sysinfoJson = @file_get_contents("http://$ipAddr:8080/cgi-bin/sysinfo.json"); //get the .json file
if($sysinfoJson === FALSE) {
	$error = error_get_last();
	wxc_checkErrorMessage($error, $ipAddr);
	//just exit since there was an error
	//when running in parallel mode you wont see this
	//this script can be used to "target" a node and see what it returns tho
	if ($testNodePolling) {
		wxc_echoWithColor("There was an error retreiving the json file for: $ipAddr\n", "red");
	}
	exit();
}else {
	//node is there, get all the info we can
	//get all the data from the json file and decode it
	$result = json_decode($sysinfoJson,true);
	
	//if there's nothing really there just exit
	//this is the parallel script!
	if (!$result || empty($result)) {
		if ($testNodePolling) {
			wxc_echoWithColor("The json file is empty for node: $ipAddr\n", "red");
		}
		exit();
	}
	//is RF enabled? (default to "on")
	$meshRF = "on";
	
	//pull out API version first
	//some user-created json files leave out the api_version section
	//so lets set a default
	if (isset($result['api_version'])) {
		$api_version = $result['api_version'];
	}else {
		//just make it 0.0.0 by default, it will always fail a compare_version then.
		$api_version = "0.0.0";
	}
	
	//let's see what node we are dealing with
	//sometimes this might be blank, catch it
	if (version_compare($api_version, "1.5", "=")) {
		if (isset($result['node_details']['node'])) {
			$node = $result['node_details']['node'];
		}else {
			$node = $result['node'];
		}
		if (isset($result['meshrf']['status'])) {
			if ($result['meshrf']['status'] == "off") {
				$meshRF = "off";
			}
		}
	}else {
		$node = $result['node'];
	}
	
	//if it's nothing other than the node name, it's some other device
	//or something else entirely...
	//people hack things onto the mesh all the time
	//
	//kg6wxc is *not* guilty of such things... :)
	//
	//just a few checks for nothing usually catches it.
	if (version_compare($api_version, "1.5", "=")) {
		if (isset($result['location']['lat'])) {
			$lat  = $result['location']['lat'];
		}else {
			$lat = $result['lat'];
		}
	}else {
		$lat = $result['lat'];
	}
	if (version_compare($api_version, "1.5", "=")) {
		if (isset($result['location']['lon'])) {
			$lon  = $result['location']['lon'];
		}else {
			$lon = $result['lon'];
		}
	}else {
		$lon = $result['lon'];
	}
	if ($node && $lat == "" && $lon == "" && $result['api_version'] == "") {
				if ($testNodePolling) {
					wxc_echoWithColor("This is probably some linux device running OLSR: " . $result['node'] . "\n", "red");
				}
				exit();
	}

	//this only seems to affect some nodes.
	//if lat || lon is blank, make it "0"
	//this was sometimes screwing up the SQL writing function
	//but not always of course.
	if (empty($lat)) {
		//if ($result['lat'] == "") {
		$lat = 0.0;
	}
	//else {
	//    $lat = $result['lat;
	//}
	if (empty($lon)) {
		//if ($result['lon'] == "") {
		$lon = 0.0;
	}
	//else {
	//    $lon = $result['lon'];
	//}

	//save json data to some variables
	//probably don't really need to do this, but it is what it is for now...
	//check for new sysinfo.json API
	if (version_compare($api_version, "1.5", ">=")) {
		if (isset($result['meshrf']['chanbw'])) {
			$chanbw				=	$result['meshrf']['chanbw'];
		}
		else {
			$chanbw = "0";
		}
		if (isset($result['meshrf']['status'])) {
			if ($result['meshrf']['status'] == "off") {
				$meshRF = "off";
				$ssid = "NONE";
			}
		}else {
			$ssid				=	$result['meshrf']['ssid'];
		}
		if ($meshRF == "off") {
			$channel = "NONE";
		}else {
			$channel			=	$result['meshrf']['channel'];
		}
		$board_id				=	$result['node_details']['board_id'];
		$firmware_version		=	$result['node_details']['firmware_version'];
		$model					=	$result['node_details']['model'];
		$firmware_mfg			=	$result['node_details']['firmware_mfg'];
		$tunnel_installed		=	$result['tunnels']['tunnel_installed'];
		$active_tunnel_count	=	$result['tunnels']['active_tunnel_count'];
		if (isset($result['location']['grid_square'])) {
			$grid_square = $result['location']['grid_square'];
		}
		else {
			$grid_square = $result['grid_square'];
		}
	}else {
		if (isset($result['chanbw'])) {
			$chanbw = $result['chanbw'];
		}
		else {
			$chanbw = "0";
		}
		if (isset($result['ssid'])) {
			$ssid = $result['ssid'];
		}else {
			$ssid = "None";
		}
		if (isset($result['channel'])) {
			$channel = $result['channel'];
		}else {
			$channel = "N/A";
		}
		if (isset($result['board_id'])) {
			$board_id = $result['board_id'];
		}
		if (isset($result['firmware_version'])) {
			$firmware_version = $result['firmware_version'];
		}
		if (isset($result['model'])) {
			$model = $result['model'];
		}
		if (isset($result['firmware_mfg'])) {
			$firmware_mfg = $result['firmware_mfg'];
		}
		if (isset($result['tunnel_installed'])) {
			$tunnel_installed = $result['tunnel_installed'];
		}
		if (isset($result['active_tunnel_count'])) {
			$active_tunnel_count = $result['active_tunnel_count'];
		}
		if (isset($result['grid_square'])) {
			$grid_square = $result['grid_square'];
		}
		
		//catch some weird ones just in case...
		//If you make a custom json file, try to make sure you conform to the AREDN api_versions.
		//if not, this might catch it... maybe... we'll see.
		if (isset($result['node_details']['board_id'])) {
			$board_id = $result['node_details']['board_id'];
		}
		if (isset($result['node_details']['firmware_version'])) {
			$firmware_version = $result['node_details']['firmware_version'];
		}
		if (isset($result['node_details']['model'])) {
			$model = $result['node_details']['model'];
		}
		if (isset($result['tunnels']['tunnel_installed'])) {
			$tunnel_installed = $result['tunnels']['tunnel_installed'];
		}
		if (isset($result['tunnels']['active_tunnel_count'])) {
			$active_tunnel_count = $result['tunnels']['active_tunnel_count'];
		}
		if (isset($result['node_details']['firmware_mfg'])) {
			$firmware_mfg = $result['node_details']['firmware_mfg'];
		}
	}
	
	//had to screen scrape the status page for this info before
	//now it is here! :)
	$uptime = "NotAvailable";
	$loadavg = "NotAvailable";
	if (version_compare($api_version, "1.2", ">=")) {
		if (isset($result['sysinfo']['uptime'])) {
			$uptime = $result['sysinfo']['uptime'];
		}
		if (isset($result['sysinfo']['loads'])) {
			$loadavg = serialize($result['sysinfo']['loads']); // <-- this is an array that has been serialized!!
		}
	}
	
	//local service listing are now in the json file!!  yay!
	$services = "NotAvailable";
	if (version_compare($api_version, "1.3", ">=")) {
		if (isset($result['services_local'])) {
			$services = serialize($result['services_local']); // <-- this is an array that has been serialized!
		}
	}
	
	//this only seems to affect some nodes.
	//if grid_square is blank, make it "none"
	//this was sometimes screwing up the SQL writing function
	//but not always of course.
	if ($grid_square == "") {
		$grid_square = "none";
	}
	//else {
	//    $grid_square = $result['grid_square'];
	//}
			
	//W6BI requested this info to be added, so here it is now. :)
	//current ip/mac address info
	if ($result['interfaces']) {
		foreach($result['interfaces'] as $interface => $infInfo) {
			$eth = "eth0";
			$wlan = "wlan0";
			if ($model == "Ubiquiti Nanostation M XW" || $model == "AirRouter " || $model == "NanoStation M5 XW ") {
				//"AirRouter " model name bug caught and fixed by Mark, N2MH 13 March 2017.
				$eth = "eth0.0";
			}
			if ($model == "MikroTik RouterBOARD 952Ui-5ac2nD ") {
				$eth = "eth1.0";
			}
			
			//!!!THERE IS ONLY 1 Wireless interface available on this device so far!!!
			//this will be changed in the near future.
			if ($model == "MikroTik RouterBOARD 952Ui-5ac2nD ") {
				$wlan = "wlan1";
			}
			
			//
			// This should catch some of those pesky ones
			// finally!
			//
			if (is_numeric($interface)) {
				if ($infInfo['name'] == $eth) {
					if (isset($infInfo['ip'])) {
						if ($infInfo['ip'] == 'none') {
							$lan_ip = "NotAvailable";
						}else {
							$lan_ip = $infInfo['ip'];
						}
					}else {
						$lan_ip = "NotAvailable";
					}
				}elseif ($infInfo['name'] == $wlan && $meshRF == "on") {
					$wlan_ip = $infInfo['ip'];
					$wifi_mac_address = $infInfo['mac'];
				}elseif ($infInfo['name'] == $wlan && $meshRF == "off") {
					$wifi_mac_address = $infInfo['mac'];
				}elseif ($meshRF == "off") {
					if (strpos($infInfo['name'], ".3975") !== false) {
						$wlan_ip = $infInfo['ip'];
					}
				}
			}else {
				if ($interface == $eth) {
					$lan_ip = $infInfo['ip'];
				}elseif ($interface == $wlan) {
					$wlan_ip = $infInfo['ip'];
					$wifi_mac_address = $infInfo['mac'];
				}
			}
		}
		if (!isset($wlan_ip) || !isset($wifi_mac_address)) {
			$infInfo = end($result['interfaces']);
			if (!isset($wlan_ip)) {
				$wlan_ip = $infInfo['ip'];
			}
			if (!isset($wifi_mac_address)) {
				$wifi_mac_address = $infInfo['mac'];
			}
		}
	}
	//in true parallel mode you wont see anything output, there is no screen
	//but you can use this script to "target" a node and see what it is returning.
	if ($testNodePolling) {
		echo "Name: "; wxc_echoWithColor($node, "purple"); echo "\n";
		echo "MAC Address: " . $wifi_mac_address . "\n";
		echo "Model: " . $model . "\n";
		if ($firmware_version !== $USER_SETTINGS['current_stable_fw_version']) {
			if (version_compare($firmware_version, $USER_SETTINGS['current_stable_fw_version'], "<")) {
				if ($firmware_version === "Linux" || $firmware_version === "linux") {
					echo "Firmware: " . $firmware_version . "  <- \033[1;32mViva Linux!!!\033[0m\n";
				}else {
					echo "Firmware: " . $firmware_mfg . " " . $firmware_version;
					wxc_echoWithColor(" Should update firmware!", "red");
					echo "\n";
				}
			}
			if (version_compare($firmware_version, $USER_SETTINGS['current_stable_fw_version'], ">")) {
				//echo "Firmware: " . $result['firmware_mfg'] . " " . $result['firmware_version'] . "  <- \033[31mBeta firmware!\033[0m\n";
				echo "Firmware: " . $firmware_mfg . " " . $firmware_version;
				wxc_echoWithColor(" Beta firmware!", "red");
				echo "\n";
			}
		}else {
			//echo "Firmware Version: " . $firmware_version . "\n";
			echo "Firmware: \033[32m" . $firmware_mfg . " " . $firmware_version . "\033[0m\n";
		}
		
		echo "LAN ip: ";
		if ($lan_ip == "NotAvailable") {
			wxc_echoWithColor($lan_ip, "orange");
		}else {
			echo $lan_ip;
		}
		echo " WLAN ip: " . $wlan_ip . "\n";
		
		if (($lat) && ($lon)) {
			echo "Location: \033[32m" . $lat . ", " . $lon. "\033[0m\n";
			//}elseif ($nodeLocationFixed = 1) {
			//	echo "\033[31mNo Location Info Set!\033[0m (FIXED!)\n";
			//	$nodeLocationFixed = 0;
		}else {
			echo "\033[31mNo Location Info Set!\033[0m\n";
			//K6GSE's solution to deal with non-null values in the DB
			$lat = 0.0;
			$lon = 0.0;
			//end
		}
		
		if ($uptime !== "NotAvailable") {
			echo "Uptime: \033[32m" . $uptime . "\033[0m\n";
		}else {
			echo "Uptime: \033[33m" . $uptime . "\033[0m\n";
		}
		//if (!empty($sysinfoJson) || $sysinfoJson !== NULL) {
		//	echo "\033[32mSaved sysinfo.json\033[0m\n";
		//}else {
		//
		//}
		//echo "\n";
	}
			
	if ($do_sql) {
		$removed_node = wxc_getMySql("SELECT node, wifi_mac_address FROM removed_nodes WHERE node = '$node' OR wifi_mac_address = '$wifi_mac_address'");
		
		if ($removed_node['node'] == $node || $removed_node['wifi_mac_address'] == $wifi_mac_address) {
			wxc_putMySql("DELETE FROM removed_nodes WHERE node = '$node' OR wifi_mac_address = '$wifi_mac_address'");
		}
		
	}
	//our queries
				
	//this is saved in case it's actually needed later
	$sql_update_when_mac_addr_has_changed	=	"UPDATE $sql_db_tbl SET
	wifi_mac_address=$wifi_mac_address,model=$model,
	firmware_version=$firmware_version,
	lat=$lat,lon=$lon,ssid=$ssid,chanbw=$chanbw,
	api_version=$api_version,board_id=$board_id,
	tunnel_install=$tunnel_installed,
	active_tunnel_count=$active_tunnel_count,
	channel=$channel,firmware_mfg=$firmware_mfg,
	lan_ip=$lan_ip,wlan_ip=$wlan_ip,last_seen=NOW()
	WHERE node=$node";
				
	/* testing trying to make things more readable and
	 * not use extra variables...
	 * it's not really working, quite a bitch to get formated just right
	 * 
	$sql = "INSERT INTO $sql_db_tbl(wifi_mac_address, node, model, firmware_version,
	lat, lon, ssid, chanbw, api_version, board_id,
	tunnel_installed, active_tunnel_count, channel,
	firmware_mfg, lan_ip, wlan_ip, sysinfo_json, olsrinfo_json, last_seen) VALUES('" . 
	$wifi_mac_address . "','" .
	$result['node'] . "','" .
	$result['model'] . "','" .
	$result['firmware_version'] . "','" .
	$result['lat'] . "','" .
	$result['lon'] . "','" .
	$result['ssid'] . "','" .
	$result['chanbw'] . "','" .
	$result['api_version'] . "','" .
	$result['board_id'] . "','" .
	$result['tunnel_installed'] . "','" .
	$result['active_tunnel_count'] . "','" .
	$result['channel'] . "','" .
	$result['firmware_mfg'] . "','" .
	$lan_ip . "','" .
	$wlan_ip . "','" .
	$sysinfoJson . "','" .
	$olsrdInfo . "', NOW()) " .
	"ON DUPLICATE KEY UPDATE " .
	"node = '" . $result['node'] . "','" .
	"model = '" . $result['model'] . "','" .
	"firmware_version = '" . $result['firmware_version'] . "','" .
	"lat = '" . $result['lat'] . "','" .
	"lon = '" . $result['lon'] . "','" .
	"ssid = '" . $result['ssid'] . "','" .
	"chanbw = '" . $result['chanbw'] . "','" .
	"api_version = '" . $result['api_version'] . "','" .
	"board_id = '" . $result['board_id'] . "','" .
	"tunnel_installed = '" . $result['tunnel_installed'] . "','" .
	"active_tunnel_count = '" . $result['active_tunnel_count'] . "','" .
	"channel =' " . $result['channel'] . "','" .
	"firmware_mfg = '" . $result['firmware_mfg'] . "','" .
	"lan_ip = '" . $lan_ip . "','" .
	$wlan_ip . "','" .
	$sysinfoJson . "','" .
	$olsrdInfo . "', NOW())";
	*/
				
	$sql	=	"INSERT INTO $sql_db_tbl(
				wifi_mac_address, node, model, firmware_version, lat, lon, grid_square, ssid, chanbw, api_version, board_id,
				tunnel_installed, active_tunnel_count, channel, firmware_mfg, lan_ip, wlan_ip, uptime, loadavg, services, last_seen)
				VALUES('$wifi_mac_address', '$node', '$model', '$firmware_version',
				'$lat', '$lon', '$grid_square', '$ssid', '$chanbw', '$api_version', '$board_id',
				'$tunnel_installed', '$active_tunnel_count', '$channel',
				'$firmware_mfg', '$lan_ip', '$wlan_ip', '$uptime', '$loadavg', '$services', NOW())
				ON DUPLICATE KEY UPDATE wifi_mac_address = '$wifi_mac_address', node = '$node', model = '$model', firmware_version = '$firmware_version',
				lat = '$lat', lon = '$lon', grid_square = '$grid_square', ssid = '$ssid', chanbw = '$chanbw', api_version = '$api_version',
				board_id = '$board_id', tunnel_installed = '$tunnel_installed',
				active_tunnel_count = '$active_tunnel_count', channel = '$channel',
				firmware_mfg = '$firmware_mfg', lan_ip = '$lan_ip', wlan_ip = '$wlan_ip',
				uptime = '$uptime', loadavg = '$loadavg', services = '$services', last_seen = NOW()";
	
	$sql_no_location_info  =    "INSERT INTO $sql_db_tbl(
				wifi_mac_address, node, model, firmware_version, grid_square, ssid, chanbw, api_version, board_id,
				tunnel_installed, active_tunnel_count, channel, firmware_mfg, lan_ip, wlan_ip, uptime, loadavg, services, last_seen)
				VALUES('$wifi_mac_address', '$node', '$model', '$firmware_version',
				'$grid_square', '$ssid', '$chanbw', '$api_version', '$board_id',
				'$tunnel_installed', '$active_tunnel_count', '$channel',
				'$firmware_mfg', '$lan_ip', '$wlan_ip', '$uptime', '$loadavg', '$services', NOW())
				ON DUPLICATE KEY UPDATE wifi_mac_address = '$wifi_mac_address', node = '$node', model = '$model', firmware_version = '$firmware_version',
				grid_square = '$grid_square', ssid = '$ssid', chanbw = '$chanbw', api_version = '$api_version',
				board_id = '$board_id', tunnel_installed = '$tunnel_installed',
				active_tunnel_count = '$active_tunnel_count', channel = '$channel',
				firmware_mfg = '$firmware_mfg', lan_ip = '$lan_ip', wlan_ip = '$wlan_ip',
				uptime = '$uptime', loadavg = '$loadavg', services = '$services', last_seen = NOW()";
	
	$sql_update_when_node_name_has_changed	=	"UPDATE $sql_db_tbl SET
            	node = '$node', model = '$model',
            	firmware_version = '$firmware_version',
            	lat = '$lat', lon = '$lon', grid_square = '$grid_square', ssid = '$ssid', chanbw = '$chanbw',
            	api_version = '$api_version', board_id = '$board_id',
            	tunnel_installed = '$tunnel_installed',
            	active_tunnel_count = '$active_tunnel_count',
            	channel = '$channel', firmware_mfg = '$firmware_mfg',
            	lan_ip = '$lan_ip', wlan_ip = '$wlan_ip',
				uptime = '$uptime', loadavg = '$loadavg', services = '$services', last_seen=NOW()
            	WHERE wifi_mac_address = '$wifi_mac_address'";
	
	$sql_update_when_node_name_has_changed_no_location_info	=	"UPDATE $sql_db_tbl SET
            	node = '$node', model = '$model',
            	firmware_version = '$firmware_version',
            	grid_square = '$grid_square', ssid = '$ssid', chanbw = '$chanbw',
            	api_version = '$api_version', board_id = '$board_id',
            	tunnel_installed = '$tunnel_installed',
            	active_tunnel_count = '$active_tunnel_count',
            	channel = '$channel', firmware_mfg = '$firmware_mfg',
            	lan_ip = '$lan_ip', wlan_ip = '$wlan_ip',
				uptime = '$uptime', loadavg = '$loadavg', services = '$services', last_seen=NOW()
            	WHERE wifi_mac_address = '$wifi_mac_address'";
	
	//find the currently stored name and mac address of the node we are looking at
	if($do_sql) {
		$node_name_array	=	wxc_getMySql("SELECT node, wifi_mac_address FROM $sql_db_tbl WHERE wifi_mac_address = '$wifi_mac_address'");
		$existing_node_name	=	$node_name_array['node'];
		$existing_mac_addr	=	$node_name_array['wifi_mac_address'];
	}
	
	//check if we are updating this nodes location info or not
	if($do_sql) {
	    $fixedLocation = wxc_getMySql("SELECT location_fix FROM $sql_db_tbl_node WHERE node = '$node'");
	    if ($fixedLocation['location_fix'] == "1") {
	        //check if we have changed node name and have the same hardware.
	        //the database itself should handle if there is new hardware with the same node name.
	        //if name has not changed, update the DB as normal for each node
	        if ($do_sql && $sysinfoJson) {
	            if(!$existing_mac_addr) {
	                wxc_putMySql($sql_no_location_info);
	            }elseif($existing_mac_addr == $wifi_mac_address) {
	                if ($existing_node_name !== $node) {
	                    wxc_putMySql($sql_update_when_node_name_has_changed_no_location_info);
	                    //echo "";
	                }else {
	                    wxc_putMySQL($sql_no_location_info);
	                }
	            }
	        }
	        if ($testNodePolling) {
	        	wxc_echoWithColor('****This nodes location has been "fixed" in the Database!****', 'alert');
	        	echo "\n";
	        	wxc_echoWithColor('****It will not update from polling if the location changes****', 'alert');
	        	echo "\n";
	        }
	    }else {
    	    //check if we have changed node name and have the same hardware.
    	    //the database itself should handle if there is new hardware with the same node name.
    	    //if name has not changed, update the DB as normal for each node
    	    if ($do_sql && $sysinfoJson) {
    	        if(!$existing_mac_addr) {
    	            wxc_putMySql($sql);
    	        }elseif($existing_mac_addr == $wifi_mac_address) {
    	            if ($existing_node_name !== $node) {
    	                wxc_putMySql($sql_update_when_node_name_has_changed);
    	                //echo "";
    	            }else {
    	                wxc_putMySQL($sql);
    	            }
    	        }
    	    }
	    }
	}
	//Thanks to K6GSE
	// Clear Variables so they do not carry over
	$wifi_mac_address = NULL;
	$node = NULL;
	$model = NULL;
	$firmware_version = NULL;
	$lat = NULL;
	$lon = NULL;
	$ssid = NULL;
	$chanbw = NULL;
	$api_version = NULL;
	$board_id = NULL;
	$tunnel_installed = NULL;
	$active_tunnel_count = NULL;
	$channel = NULL;
	$firmware_mfg = NULL;
	$lan_ip = NULL;
	$wlan_ip = NULL;
	$uptime = NULL;
	$loadavg = NULL;
	$services = NULL;
	$sysinfoJson = NULL;
}

//update the database with the time, so we know when the last update is
if($do_sql) {
	wxc_scriptUpdateDateTime("NODEINFO", "node_info");
	wxc_putMySql("UPDATE map_info SET currently_running = '0' WHERE id = 'NODEINFO'");
}
?>
