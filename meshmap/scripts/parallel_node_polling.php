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

$ipAddr = $argv[1];
$do_sql = $argv[2];
$testNodePolling = $argv[3];

//output error messages only in the cron job
//if your system has an MTA installed you should get local emails from
//this script if there are errors, you can turn this on or off in the ini file.
//(not quite done yet)
$errOut = "0";

if(isset($GLOBALS['USER_SETTINGS']['errorsInCron'])) {
	$errOut = $GLOBALS['USER_SETTINGS']['errorsInCron'];
}else {
	$errOut = "0";
}

if ($do_sql) {
	$sql_connection =  wxc_connectToMySql();
}
$sql_db_tbl = $USER_SETTINGS['sql_db_tbl'];
$sql_db_tbl_node = $USER_SETTINGS['sql_db_tbl_node'];
$sql_db_tbl_topo = $USER_SETTINGS['sql_db_tbl_topo'];

//get sysinfo.json fron node
//this is the heart of the mapping system
$sysinfoJson = @file_get_contents("http://$ipAddr:8080/cgi-bin/sysinfo.json?services_local=1");

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
	//get all the data from the json file and decode it
	//remove any "funny" characters from the sysinfo.json string *BEFORE* it gets decoded
	//these mainly occur when people get fancy with the description
	//use html name codes! do not use the hex codes!
	$sysinfoJson = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $sysinfoJson);
	$result = json_decode($sysinfoJson,true);
	
	//if data was returned from the node, it will be an array
	//if it's not an array we probably got nothing back (or incomplete data or something else)
	//this steps thru each item in the $results array (the json info)
	//if the value is another arry, it steps thru that
	//and so on (there are only 3 "levels" so far)
	if(is_array($result)) {
		
		//
		//parse json data (this is where the real heart is)
		//
		
		//variables and defaults
		$meshRF = "on";
		$ethInf = "eth0";
		$wlanInf = "wlan0";
		
		$node = "";
		$wlan_ip = "";
		$uptime = "Not Available";
		$loadavg = "Not Available";
		$model = "Not Available";
		$firmware_version = "Not Available";
		$ssid = "None";
		$channel = "None";
		$chanbw = "None";
		$tunnel_installed = "false";
		$active_tunnel_count = "0";
		$lat = 0.0;
		$lon = 0.0;
		$wifi_mac_address = "";
		$api_version = "0.0.0";
		$board_id = "Not Available";
		$firmware_mfg = "Not Available";
		$grid_square = "Not Available";
		$lan_ip = "Not Available";
		$services = "Not Available";
		$description = "";
		
		//new stuff for the future (not used yet)
		$signal = 0;
		$noise = 0;
		$freq = "";
		
		//quick hack for USB150 devices
		$eth3975 = 0;
		
		//check a couple of things at first
		if(isset($result['meshrf']['status']) && $result['meshrf']['status'] == "off") {
			$meshRF = "off";
		}
		//start looping through the json data array
		foreach($result as $key => $value) {
			
			//find the local services on the node
			if($key == "services_local") {
				$services = serialize($value); // <-- this is an array that has been serialized!!
			}
			
			if(is_array($value) && $key != "services_local") { //<- $value is an array
				
				foreach($value as $key2 => $value2) { //<- step thru the array
					
					//find node load averages
					if($key2 === "loads") {
						$loadavg = serialize($value2); // <-- this is an array that has been serialized!!
					}
					
					if($key == "interfaces") {
						if(is_numeric($key2)) {
							switch($value2['name']) {
								case $wlanInf:
									$wifi_mac_address = $value2['mac'];
									break;
							}
							if(isset($value2['ip']) && $value2['ip'] != "none" && !$eth3975) {
								switch($value2['name']) {
									case $wlanInf:
										$wlan_ip = $value2['ip'];
										break;
										//for the HaP Lite Models
									case "wlan1":
											$wlan_ip = $value2['ip'];
											break;
									case "eth1.3975":
										$wlan_ip = $value2['ip'];
										$eth3975 = 1;
										break;
									case "eth0.3975":
										$wlan_ip = $value2['ip'];
										$eth3975 = 1;
										break;
								}
							}
						}else { //<- interfaces array keys are *not* numeric (it's a very old version of the json file)
							if(isset($value2['ip']) && $value2['ip'] != "none") {
								switch($key2) {
									case "$wlanInf":
										$wifi_mac_address = $value2['mac'];
										$wlan_ip = $value2['ip'];
								}
							}
						}
					}
					else {
						if(isset($value2) && $value2 != '') {
							switch($key2) {
								
								//meshrf values
								case "status":
									$meshRF = $value2;
									break;
								case "chanbw":
									$chanbw = $value2;
									break;
								case "ssid":
									$ssid = $value2;
									break;
								case "channel":
									$channel = $value2;
									break;
									
									//tunnel values
								case "active_tunnel_count":
									$active_tunnel_count = $value2;
									break;
								case "tunnel_installed":
									$tunnel_installed = $value2;
									break;
									
									//uptime
								case "uptime":
									$uptime = $value2;
									break;
									
									//node info
								case "description":
									$description = $value2;
									break;
								case "model":
									$model = $value2;
									break;
								case "board_id":
									$board_id = $value2;
									break;
								case "firmware_mfg":
									$firmware_mfg = $value2;
									break;
								case "firmware_version":
									$firmware_version = $value2;
									break;
									
									//there was one "funny" version 1.5 of the json API
								case "node":
									$node = $value2;
									break;
								case "lat":
									if(isset($value2) && $value2 != '') {
										$lat = $value2;
									}
									break;
								case "lon":
									if(isset($value2) && $value2 != '') {
										$lon = $value2;
									}
									break;
							}
						}
					} // <- end $value2 is *not* array
					unset($key2);
				} //<- end $key2/$value2 foreach loop
				
			} // <-- end $value is_array
			else {
				//these values are in the root of the json file (for now)
				if(isset($value) && $value != '') {
					switch($key) {
						case "lon":
							$lon = $value;
							break;
						case "lat":
							$lat = $value;
							break;
						case "api_version":
							$api_version = $value;
							break;
						case "grid_square":
							$grid_square = $value;
							break;
						case "node":
							$node = $value;
							break;
							
							//catch old API entries
						case "ssid":
							$ssid = $value;
							break;
						case "model":
							$model = $value;
							break;
						case "board_id":
							$board_id = $value;
							break;
						case "firmware_version":
							$firmware_version = $value;
							break;
						case "firmware_mfg":
							$firmware_mfg = $value;
							break;
						case "channel":
							$channel = $value;
							break;
						case "chanbw":
							$chanbw = $value;
							break;
						case "active_tunnel_count":
							$active_tunnel_count = $value;
							break;
						case "tunnel_installed":
							$tunnel_installed = $value;
							break;
					}
				}
			} // <- end $value is *not* array
			unset($key);
		} // <-- end first layer foreach loop
		
		//test mode echo to screen
		if ($testNodePolling) {
			echo "Name: "; wxc_echoWithColor($node, "purple"); echo "\n";
			echo "MAC Address: ";
			if($wifi_mac_address) {
				wxc_echoWithColor($wifi_mac_address, "green");
				echo "\n";
			}else {
				wxc_echoWithColor("No MAC Address Found!", "red");
			}
			echo "Model: " . $model . "\n";
			if ($firmware_version !== $USER_SETTINGS['current_stable_fw_version']) {
				if (version_compare($firmware_version, $USER_SETTINGS['current_stable_fw_version'], "<")) {
					if ($firmware_version === "Linux" || $firmware_version === "linux") {
						echo "Firmware: " . $firmware_version . "  <- \033[1;32mViva Linux!!!\033[0m\n";
					}else {
						echo "Firmware: " . $firmware_mfg . " ";
						wxc_echoWithColor($firmware_version, "red");
						wxc_echoWithColor(" Should update firmware!", "red");
						echo "\n";
					}
				}
				if (version_compare($firmware_version, $USER_SETTINGS['current_stable_fw_version'], ">")) {
					//echo "Firmware: " . $result['firmware_mfg'] . " " . $result['firmware_version'] . "  <- \033[31mBeta firmware!\033[0m\n";
					echo "Firmware: " . $firmware_mfg . " ";
					wxc_echoWithColor($firmware_version, "orange");
					wxc_echoWithColor(" Beta firmware!", "orange");
					echo "\n";
				}
			}else {
				//echo "Firmware Version: " . $firmware_version . "\n";
				echo "Firmware: \033[32m" . $firmware_mfg . " " . $firmware_version . "\033[0m\n";
			}
			
			echo "LAN ip: ";
			if ($lan_ip == "Not Available") {
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
				//$lat = 0.0;
				//$lon = 0.0;
				//end
			}
			
			if($uptime != "Not Available") {
				echo "Uptime: ";
				wxc_echoWithColor($uptime, "green");
				echo "\n";
			}else {
				echo "Uptime: ";
				wxc_echoWithColor($uptime, "orange");
				echo "\n";
			}
			
			echo "Loads: ";
			if($loadavg != "Not Available") {
				$ldavgs = unserialize($loadavg);
				$ld1 = $ldavgs[0];
				$ld5 = $ldavgs[1];
				$ld15 = $ldavgs[2];
				if($ld1 > 1) {
					$ld1 = wxc_addColor($ld1, "red");
				}elseif ($ld1 < 1 && $ld1 > 0.5) {
					$ld1 = wxc_addColor($ld1, "orange");
				}else {
					$ld1 = wxc_addColor($ld1, "green");
				}
				if($ld5 > 1) {
					$ld5 = wxc_addColor($ld5, "red");
				}elseif ($ld5 < 1 && $ld5 > 0.5) {
					$ld5 = wxc_addColor($ld5, "orange");
				}else {
					$ld5 = wxc_addColor($ld5, "green");
				}
				if($ld15 > 1) {
					$ld15 = wxc_addColor($ld15, "red");
				}elseif ($ld15 < 1 && $ld15 > 0.5) {
					$ld15 = wxc_addColor($ld15, "orange");
				}else {
					$ld15 = wxc_addColor($ld15, "green");
				}
				
				echo "1 min: " . $ld1 . " 5 min: " . $ld5 . " 15 min: " . $ld15 . "\n";
			}else {
				wxc_echoWithColor($loadavg, "orange");
				echo "\n";
			}
			
			echo "Mesh RF: ";
			if($meshRF == "off") {
				wxc_echoWithColor($meshRF, "orange");
			}else {
				wxc_echoWithColor($meshRF, "green");
			}
			
			
			echo "  SSID: ";
			if($meshRF == "off") {
				wxc_echoWithColor($ssid, "orange");
			}else {
				wxc_echoWithColor($ssid, "green");
			}
			echo "\n";
			
			echo "API Version: ";
			switch($api_version) {
				case "1.6":
					wxc_echoWithColor($api_version, "green");
					break;
				case "1.5":
					wxc_echoWithColor($api_version, "orange");
					break;
				case "1.3":
					wxc_echoWithColor($api_version, "red");
					break;
				default:
					wxc_echoWithColor($api_version, "redBold");
					break;
			}
			echo "\n";
			
			echo "Tunnels: ";
			wxc_echoWithColor("$tunnel_installed", "orange");
			echo "  Count: ";
			wxc_echoWithColor($active_tunnel_count, "orange");
			echo "\n";
			
		} //<- end test mode echo to screen
		
		
		//SQL stuff
		if ($do_sql) {
			$removed_node = wxc_getMySql("SELECT node, wifi_mac_address FROM removed_nodes WHERE node = '$node' OR wifi_mac_address = '$wifi_mac_address'");
			
			if ($removed_node['node'] == $node || $removed_node['wifi_mac_address'] == $wifi_mac_address) {
				wxc_putMySql("DELETE FROM removed_nodes WHERE node = '$node' OR wifi_mac_address = '$wifi_mac_address'");
			}
			
		}
		
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
		if ($testNodePolling) {
			echo "\n";
		}
	} // <- end of json_decode into array
	else {
		if($errOut || $testNodePolling) {
			echo "There was a problem decoding the json file from node: " . $node . "(" . $ipAddr . ")\n";
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
} //<- end of if/else sysinfo.json good

//update the database with the time, so we know when this part of the script last ran
if($do_sql) {
	wxc_scriptUpdateDateTime("NODEINFO", "node_info");
	wxc_putMySql("UPDATE map_info SET currently_running = '0' WHERE id = 'NODEINFO'");
}
?>
