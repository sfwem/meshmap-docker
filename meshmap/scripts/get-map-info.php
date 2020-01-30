#!/usr/bin/env php
<?php
/*************************************************************************************
 * get-map-info script v4 by kg6wxc\eric satterlee kg6wxc@gmail.com
 * March 2019
 * Licensed under GPLv3 or later
 * This script is the heart of kg6wxcs' mesh map system.
 * bug fixes, improvements and corrections are welcomed!
 *
 * One Script to rule them all!!
 *
 * see CHANGELOG.md for notes
 **************************************************************************************/

/******
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
$mtimeStart = microtime(true);

//Increase PHP memory limit to 128M (you may need more if you are connected to a "Mega Mesh" :) )
//this should be moved to the ini file maybe
ini_set('memory_limit', '128M');

$INCLUDE_DIR = "..";

//check for users user-settings.ini file and use it if it exists
//inform the user and exit if the file does not exist
global $USER_SETTINGS;
if (file_exists($INCLUDE_DIR . "/scripts/user-settings.ini")) {
    $USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
}else {
    exit("\n\nYou **must** copy the user-settings.ini-default file to user-settings.ini and edit it!!\n\n");
}

//kg6wxc's functions. (ALWAYS REQUIRED!, change path if you moved it!)
require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
require $INCLUDE_DIR . "/scripts/checkDB.inc";

//the custom include file
//the "@" just suppresses any errors if the file is not found, the file is optional
@include $INCLUDE_DIR . "/custom.inc";

$script_arg = "";
if (isset($argv[1])) {
    $script_arg = $argv[1];
}
if ($script_arg == "--help" || $script_arg == "--h" || $script_arg == "-help" || $script_arg == "-h" || $script_arg == "/?" || $script_arg == "?") {
    echo $argv[0] . " Usage:\n\n";
    echo $argv[1] . "\tThis help message\n\n";
    echo "--test-mode-no-sql\tDO NOT access database only output to screen\n";
    echo "(useful to make sure everything is working)\n\n";
    echo "--test-mode-with-sql\tDO access the database AND output to screen\n";
    echo "(useful to see if everything is working and there are no errors reading/writing to the database)\n\n";
    echo "No arguments to this script will run it in \"silent\" mode, good for cron jobs! :)\n";
    echo "\n";
    exit();
}
$TEST_MODE_NO_SQL = 0;
$TEST_MODE_WITH_SQL = 0;
if ($script_arg == "--test-mode-no-sql") {
    //output only to console, nothing saved. (great to just see what it does)
    $TEST_MODE_NO_SQL = 1;
}
if ($script_arg == "--test-mode-with-sql") {
    //output to console, but *with* calls to the database. (see what it's doing while saving data)
    $TEST_MODE_WITH_SQL = 1;
}

//are we in either test mode?
if ($TEST_MODE_NO_SQL) {
    $showRuntime = 1;
    $testLinkInfo = 1;
    $testNodePolling = 1;
    $do_sql = 0;
    $getLinkInfo = 1;
    $getNodeInfo = 1;
    echo "TEST MODE (NO SQL) ENABLED!\n";
}elseif ($TEST_MODE_WITH_SQL) {
    $showRuntime = 1;
    $testLinkInfo = 1;
    $testNodePolling = 1;
    $do_sql = 1;
    $getLinkInfo = 0;
    $getNodeInfo = 0;
    echo "TEST MODE (WITH SQL) ENABLED!\n";
}else {
    $showRuntime = 0;
    $testLinkInfo = 0;
    $testNodePolling = 0;
    $do_sql = 1;
    $getLinkInfo = 0;
    $getNodeInfo = 0;
}

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

//(WiP)checks for some things we need to run
//(currently only really checks for the mysqli php extension
wxc_checkConfigs();

//what time is it now? returns a DateTime Object with current time.
date_default_timezone_set($USER_SETTINGS['localTimeZone']);
$currentTime = wxc_getCurrentDateTime();
//setting our global TimeZone
global $localTimeZone;
$localTimeZone = new DateTimeZone($USER_SETTINGS['localTimeZone']);

//what do we want to use in the sql server
$sql_db_tbl = $USER_SETTINGS['sql_db_tbl'];
$sql_db_tbl_node = $USER_SETTINGS['sql_db_tbl_node'];
$sql_db_tbl_topo = $USER_SETTINGS['sql_db_tbl_topo'];

if ($do_sql) {
        //an sql connection that we can reuse...
        //$sql_connection       =       wxc_connectToMySQL();
        wxc_connectToMySQL();
        //check for new or changed database items (tables, columns,etc)
        wxc_checkDB();
        //find and remove dupes in node_info
        wxc_check4Dupes();
}else {
        if ($TEST_MODE_NO_SQL) {
                wxc_echoWithColor("SQL Server access disabled!", "red");
                echo "\n";
        }
}

//This controls when certain parts of the script run.
//We check the DB for the time we last checked and only run if we need to.
//intervals are now controled via the ini file thanks to K6GSE!
if ($do_sql) {
        //if $do_sql is set to 1, check when we last polled all the known nodes, if it was more than the interval set the variable to 1
        $lastRunGetNodeInfo = wxc_scriptGetLastDateTime("NODEINFO", "node_info");
        if ($lastRunGetNodeInfo) {
                if ($USER_SETTINGS['node_polling_interval'] > 0) {
                        $intervalNODE = date_diff($lastRunGetNodeInfo, $currentTime);
                        $intervalNodeInMinutes = $intervalNODE->days * 24 * 60;
                        $intervalNodeInMinutes += $intervalNODE->h * 60;
                        $intervalNodeInMinutes += $intervalNODE->i;
                        if ($intervalNodeInMinutes >= intval($USER_SETTINGS['node_polling_interval'])) {
                                if ($TEST_MODE_WITH_SQL) {
                                        echo "It has been " . $USER_SETTINGS['node_polling_interval'] . " or more minutes since this script polled all the nodes\n";
                                        echo "Set to poll nodes.\n";
                                }
                                $getNodeInfo = 1;
                        }
                }
        }else {
                //probably never run before, lets get some data!!
                echo "Set to poll nodes.\n";
                $getNodeInfo = 1;
        }

        //if $do_sql is set to 1, check when we last got link info, if it was more than the interval set the variable to 1
        $lastRunGetLinkInfo = wxc_scriptGetLastDateTime("LINKINFO", "topology");
        if($lastRunGetLinkInfo) {
                if ($USER_SETTINGS['link_update_interval'] > 0) {
                        $intervalLINK = date_diff($lastRunGetLinkInfo, $currentTime);
                        if ($intervalLINK->i >= intval($USER_SETTINGS['link_update_interval'])) {
                                if ($TEST_MODE_WITH_SQL) {
                                        echo "It has been " . $USER_SETTINGS['link_update_interval'] . " or more minutes since this script got the link info\n";
                                        echo "Set to get network linking info.\n\n";
                                }
                                $getLinkInfo = 1;
                        }
                }
        }else {
                //probably never run before, let's get some data!
                echo "Set to get network linking info.\n\n";
                $getLinkInfo = 1;
        }
}

//check the database to see if we are already polling nodes
//this trys to prevent 2 polling runs at once
//it does nothing in "--test-mode-no-sql"
if ($do_sql) {
        $currently_polling_nodes = wxc_getMySql("SELECT script_last_run, currently_running from map_info WHERE id = 'NODEINFO'");
        if (is_null($currently_polling_nodes['currently_running'])) {
                $currently_polling_nodes['currently_running'] = 0;
                $getNodeInfo = 1;
        }elseif ($currently_polling_nodes['currently_running'] == 1) {
                $getNodeInfo = 0;
        }
        //hopefully catch a stalled polling run after 3 * node_polling_interval has expired.
        //something may have gone wonky and the node polling run never completed and never had a chance
        //to unset the "currently_running" bit in the DB,
        //this *should* catch that and and just run the node polling again
        if ($currently_polling_nodes['currently_running'] == 1 && $intervalNodeInMinutes >= intval($USER_SETTINGS['node_polling_interval']) * 3) {
            $currently_polling_nodes['currently_running'] = 0;
                $getNodeInfo = 1;
        }
}

//check for old outdated node info (intervals are set in the ini file)
$do_expire = $USER_SETTINGS['expire_old_nodes'];
if ($do_sql && $do_expire) {
        wxc_checkOldNodes();
}
if ($do_sql) {
        wxc_removeIgnoredNodes();
}

if ($getNodeInfo) {
	//this section is what goes out to each node on the mesh and asks for it's info
	//this is really the heart of the mapping system, without this (and the sysinfo.json file),
	//none of this would even be possible.
	
	//tell the database we are actively polling nodes
	if ($do_sql && $currently_polling_nodes['currently_running'] == 0) {
		wxc_putMySql("INSERT INTO map_info (id, table_or_script_name, script_last_run, currently_running) VALUES ('NODEINFO', 'node_info', NOW(), '1') ON DUPLICATE KEY UPDATE table_or_script_name = 'node_info', script_last_run = NOW(), currently_running = '1'");
	}
	//get a new list of IP's to poll
	$meshNodes = wxc_netcat($USER_SETTINGS['localnode'], "2004", null, "ipOnly");
	if ($meshNodes) {
		
		//
		//parallel polling will have to at least start here
		//going to have to break the IP list up into sections
		//or create some kind of "container" that only allow so many to run at a time...
		//
		
		foreach (preg_split("/((\r?\n)|(\r\r?))/", $meshNodes) as $line) {
			list ($ipAddr) = explode("\n", $line);
			
			//check for nodes that we know will not have the info we are going to request and skip them
			if ($do_sql) {
				if (wxc_getMySql("SELECT ip FROM hosts_ignore WHERE ip = '$ipAddr'")) {
					continue;
				}
			}
			
			//copy to new var name (I dont feel like editing it all right now)
			$nodeName = $ipAddr;
			if ($USER_SETTINGS['node_polling_parallel']) {
				
				//it probably wont actually be this way in the end
				//newer PHP supports threads
				
				//disabled for now
				//shell_exec("php $INCLUDE_DIR/scripts/parallel_node_polling.php $ipAddr $do_sql 0 > /dev/null 2>/dev/null &");
				
			}else {
				
				//get sysinfo.json fron node
				//this is the heart of the mapping system
				$sysinfoJson = @file_get_contents("http://$ipAddr:8080/cgi-bin/sysinfo.json?services_local=1");
				
				if($sysinfoJson === FALSE) {
					$error = error_get_last();
					wxc_checkErrorMessage($error, $nodeName);
					//just skip to the next IP since there was an error
					continue;
				}else {
					//get all the data from the json file and decode it
					//remove any "funny" characters from the sysinfo.json string *BEFORE* it gets decoded
					//these mainly occur when people get fancy with the description field
					//use html name codes! do not use the hex codes!
					$sysinfoJson = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $sysinfoJson);
					$result = json_decode($sysinfoJson,true);
					
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
										}else { //<- interfaces array keys are *not* numeric (it's a very very old version of the json file)
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
								case "1.7":
									wxc_echoWithColor($api_version, "greenBold");
									break;
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
				
			} //<- end of if/else single or parrallel polling
			
		} //<- end of foreach for each IP
		
	} //<- end if($meshNodes)
	//update the database with the time, so we know when this part of the script last ran
	if($do_sql) {
		wxc_scriptUpdateDateTime("NODEINFO", "node_info");
		wxc_putMySql("UPDATE map_info SET currently_running = '0' WHERE id = 'NODEINFO'");
	}
		
} //<- end if($getNodeInfo)

if ($getLinkInfo) {
	if ($do_sql) {
		wxc_putMySQL("TRUNCATE TABLE $sql_db_tbl_topo");	//clear out the old info in the "topology" table first
	}
	
	$meshNodes = wxc_netcat($USER_SETTINGS['localnode'], "2004", null, "linkInfo");	//get the latest link info
	if ($meshNodes) {
		foreach (preg_split("/((\r?\n)|(\r\r?))/", $meshNodes) as $line) {	//split the string on the \n's (new line)
			//if ($line !== "") {
			list ($node, $linkto, $cost) = explode(" ", $line);
			//echo "Node: " . $node . " Linkto: " . $linkto . " Cost: " . $cost . "\n";
			//}
			
			$nodeIp = $node;
			$linktoIp = $linkto;
			//$nodeName = wxc_resolveIP($node);
			//$linktoName = wxc_resolveIP($linkto);
			$nodeName = $node;
			$linktoName = $linkto;
			
			if ($do_sql) {	//put the data into SQL, if we can.
				wxc_putMySQL("INSERT INTO $sql_db_tbl_topo(node, linkto, cost) VALUES('$nodeName', '$linktoName', '$cost')");
				//quick hacks to remove/change junk
				wxc_putMySQL("delete from $sql_db_tbl_topo where node is NULL and linkto is NULL");
				//	wxc_putMySql("update topology set nodelat = 0 where nodelat is NULL");
				//	wxc_putMySql("update topology set nodelon = 0 where nodelon is NULL");
				//	wxc_putMySql("update topology set linklat = 0 where linklat is NULL");
				//	wxc_putMySql("update topology set linklon = 0 where linklon is NULL");
			}
			
			if ($testLinkInfo) {	//output to screen if we are in "test mode".
				if ($cost > 0.1 && $cost <=2) {
					$cost = "\033[1;32m" . $cost . "\033[0m";
				}
				if ($cost >2 && $cost <4) {
					$cost = "\033[0;32m" . $cost . "\033[0m";
				}
				if ($cost >4 && $cost <6) {
					$cost = "\033[1;33m" . $cost . "\033[0m";
				}
				if ($cost >6 && $cost <10) {
					$cost = "\033[0;31m" . $cost . "\033[0m";
				}
				if ($cost >10) {
					$cost = "\033[1;31m" . $cost . "\033[0m";
				}
				echo "$nodeName -> $linktoName cost: $cost\n";
			}
			if($do_sql) {
				wxc_scriptUpdateDateTime("LINKINFO", "topology");
			}
		}
	}
}
$mtimeEnd = microtime(true);
$totalTime = $mtimeEnd-$mtimeStart;
if ($showRuntime) {
	echo "Time Elapsed: " . round($totalTime, 2) . " seconds ( " . round($totalTime/60, 2) . " minutes ).\n";
}
?>
