<?php 
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();
$now = time();
if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
	//old session get rid of it
	session_unset();
	session_destroy();
	session_start();
}
if (isset($_POST['logMeOut'])) {
	session_unset();
	session_destroy();
	$_POST = array();
	header('Location: admin.php');
	die();
}
$INCLUDE_DIR = "../..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
include $INCLUDE_DIR . "/scripts/wxc_functions.inc";
@include $INCLUDE_DIR . "/custom.inc";
$sqlSrvStatus;
$arname = "";
$sql_connection = wxc_connectToMySQL();
$dateTime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
$localTime = $dateTime -> format('Y-m-d');
if (!isset($_SESSION['userLoggedIn'])) {
    if (!isset($_POST['username']) && !isset($_POST['password'])) {
        //does a "super user" exist yet in the DB?
        //if not create it, maybe this is the first time running
        //or it is an existing DB, but doesn't have any users yet
        //(I thought this would be better than to have to create a whole new
        // .sql file with the default user/pass in it)
        $doesAdminUserExistYet = mysqli_query($sql_connection, "select user, admin from users where admin = 1");
        
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<title>MeshMap Admin Page Login</title>\n";
        echo "<meta http-equiv='Pragma' content='no-cache'>\n";
        echo "<meta http-equiv='Expires' content='-1'>\n";
		if (file_exists("./admin.css")) {
			echo "<link rel='stylesheet' href='admin.css'>\n";
		}else {
			echo "<link rel='stylesheet' href='admin-default.css'>\n";
		}
        echo "</head>\n";
        echo "<body>\n";
        
        if (!mysqli_num_rows($doesAdminUserExistYet)) {
            //this only shows up if a super user does not exist yet
            echo "<center><strong>There is no super user yet!</strong></center>\n";
            echo "<br>\n";
            echo "<center>Please create one!</center>\n";
        //  echo "<br>";
        //  echo "";
        	echo "<form id='first_login_form' action='admin.php' method='post'>\n";
        	echo "<input type='hidden' name='first_login' value='first_login'>\n";
        	echo "<table>\n";
        	echo "<tr>\n";
        	echo "<td>Callsign/Username</td>\n";
        	echo "<td><input type='text' name='user' value=''></td>\n";
        	echo "</tr>\n";
		echo "<tr>\n";
        	echo "<td>Password</td>\n";
        	echo "<td><input type='password' name='passwd' value=''></td>\n";
        	echo "</tr>\n";
		echo "</table>\n";
        	echo "<br>\n";
        	echo "<center>You should be redirected to the login page after you click submit.</center>\n";
        	echo "<center><input type='submit' value='Submit'></center>\n";
        	echo "</form>\n";
        	echo "<br>\n";
        	echo "<p class='center'><a href='../map_display.php'>Back to the Map</a></p>\n";
        	echo "<br>\n";
        	if (isset($_POST['first_login']) && isset($_POST['user']) && isset($_POST['passwd'])) {
        		$user = $_POST['user'];
        		$passwd = $_POST['passwd'];
        //		$salt = bin2hex(random_bytes(16)); // random_bytes() is only in PHP7
        		$salt = bin2hex(openssl_random_pseudo_bytes(16));
        		$cryptedPasswd = crypt($passwd, $salt);
        		$add_initial_user = wxc_putMySql("insert into users (user, passwd, admin) values ('$user', '$cryptedPasswd', 1)");
        		if ($add_initial_user) {
        			$_POST = array();
        			header("Location: admin.php");
        			die;
        		}
        	}
        }else {
	        //login page
	        echo "<center><strong>Please login to use the MeshMap Admin interface.</strong></center>\n";
	        echo "<br>\n";
	        echo "<form name='mapAdminLogin' method='POST'>\n";
	        echo "<center>Username: <input type='text' name='username' value='' autofocus></center><br>\n";
	        echo "<center>Password: <input type='password' name='password' value=''></center><br>\n";
	        echo "<center>" . "<input type='submit' value='Submit'></center></form>\n";
	        echo "<br>\n";
	        echo "<p class='center'><a href='../map_display.php'>Back to the Map</a></p>\n";
	        echo "</body>\n";
	        echo "</html>\n";
        }
    }else {
    	$passwordCheckResult = 0;
        $usernameIsGood = 0;
        $passwordIsGood = 0;

        $username = $_POST['username'];
        $password = $_POST['password'];
        
        //check username
        $usernameCheckResult = mysqli_query($sql_connection, "SELECT id, user, admin from users where user='$username'");
        $userArray = mysqli_fetch_array($usernameCheckResult);
        $userID = $userArray['id'];
        $super_user = $userArray['admin'];
		$usernameCheckResult = mysqli_num_rows($usernameCheckResult);
        if ($usernameCheckResult) {
            $usernameIsGood = 1;
        }else {
        	echo "<!DOCTYPE html>\n";
        	echo "<html>\n";
        	echo "<head>\n";
        	echo "<title>MeshMap Admin Page Login</title>\n";
        	echo "<meta http-equiv='Pragma' content='no-cache'>\n";
        	echo "<meta http-equiv='Expires' content='-1'>\n";
			if (file_exists("./admin.css")) {
				echo "<link rel='stylesheet' href='admin.css'>\n";
			}else {
				echo "<link rel='stylesheet' href='admin-default.css'>\n";
			}
			echo "</head>\n";
        	echo "<body>\n";
        	echo "<br><br>\n";
        	echo "<center><strong><redText>No such user: " . $username . "!</redText></strong></center>\n";
        	echo "<br>\n";
        	echo "<center>Please try again...</center>\n";
        	echo "<br>\n";
        	//login page
        	echo "<center><strong>Please login to use the MeshMap Admin interface.<br>\n";
        	echo "<form name='mapAdminLogin' method='POST'>\n";
        	echo "<center>Username: <input type='text' name='username' value='' autofocus></center><br>\n";
        	echo "<center>Password: <input type='password' name='password' value=''></center><br>\n";
        	echo "<center>" . "<input type='submit' value='Submit'></center></form>";
        	echo "<br>";
        	echo "<p class='center'><a href='../map_display.php'>Back to the Map</a></p>\n";
        	echo "</body>\n";
        	echo "</html>\n";
        	die;
		}
        if ($usernameIsGood) {
        $query = "select passwd from users where id='" . $userID . "' and user='" . $username . "'";
        $storedPasswd = wxc_getMySql($query);
        $storedPasswd = $storedPasswd['passwd'];
        if (crypt($password, $storedPasswd) == $storedPasswd) {
        	$passwordCheckResult = 1;
        }else {
        	echo "<!DOCTYPE html>\n";
        	echo "<html>\n";
        	echo "<head>\n";
        	echo "<title>MeshMap Admin Page Login</title>\n";
        	echo "<meta http-equiv='Pragma' content='no-cache'>\n";
        	echo "<meta http-equiv='Expires' content='-1'>\n";
			if (file_exists("./admin.css")) {
				echo "<link rel='stylesheet' href='admin.css'>\n";
			}else {
				echo "<link rel='stylesheet' href='admin-default.css'>\n";
			}
        	echo "</head>\n";
        	echo "<body>\n";
        	echo "<br><br>\n";
        	echo "<center><strong><redText>Wrong password for: " . $username . "!</redText></strong></center>\n";
        	echo "<br>\n";
        	echo "<center>Please try again...</center>\n";
        	echo "<br>\n";
        	//login page
        	echo "<center><strong>Please login to use the MeshMap Admin interface.<br>\n";
        	echo "<form name='mapAdminLogin' method='POST'>\n";
        	echo "<center>Username: <input type='text' name='username' value='' autofocus></center><br>\n";
        	echo "<center>Password: <input type='password' name='password' value=''></center><br>\n";
        	echo "<center>" . "<input type='submit' value='Submit'></center></form>";
        	echo "<br>";
        	echo "<p class='center'><a href='../map_display.php'>Back to the Map</a></p>\n";
        	echo "</body>\n";
        	echo "</html>\n";
		}}
        if ($passwordCheckResult) {
            $passwordIsGood = 1;
            session_regenerate_id(FALSE);
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['password'] = $_POST['password'];
        }
        if ($usernameIsGood && $passwordIsGood) {
            $_SESSION['userLoggedIn'] = 1;
            $_SESSION['id'] = $userID;
            $_SESSION['super_user'] = $super_user;
            wxc_putMySql("update users set last_login = now() where id='$userID' and user='$username'");
        }
    }
}
if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] = 1) {
    if (!$sql_connection) {
        $sqlSrvStatus = 0;
    }else {
        $sqlSrvStatus = 1;
        $totalNumNodes = mysqli_num_rows(mysqli_query($sql_connection, "SELECT node from node_info"));
        $totalNumNodesWithLocations = mysqli_num_rows(mysqli_query($sql_connection, "SELECT node FROM node_info WHERE lat != '0' AND lon != '0'"));
        $totalNumLinks = mysqli_num_rows(mysqli_query($sql_connection, "SELECT node from topology"));
        $totalNumLinksWithLocations = mysqli_num_rows(mysqli_query($sql_connection, "SELECT node from topology where ((linklat != '0' OR linklat IS NOT NULL) AND (linklon != '0' OR linklon IS NOT NULL)) AND ((nodelat != '0' OR nodelat IS NOT NULL) AND (nodelon != '0' OR nodelon IS NOT NULL));"));
        $totalRemovedNodes = mysqli_num_rows(mysqli_query($sql_connection, "SELECT node from removed_nodes"));
        $totalIgnoredNodes = mysqli_num_rows(mysqli_query($sql_connection, "SELECT ip FROM hosts_ignore"));
        $lastUpdateNodeInfo = wxc_getMySql("SELECT currently_running, table_last_update, script_last_run FROM map_info WHERE id = 'NODEINFO'");
        $lastUpdateLinkInfo = wxc_getMySql("SELECT table_last_update FROM map_info WHERE id = 'LINKINFO'");
        $totalNumNonMeshMarkers = mysqli_num_rows(mysqli_query($sql_connection, "SELECT name from marker_info"));
            
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<title>MeshMap Admin Page</title>\n";
    
    if (file_exists("./admin.css")) {
    	echo "<link rel='stylesheet' href='admin.css'>\n";
    }else {
    	echo "<link rel='stylesheet' href='admin-default.css'>\n";
    }

    echo "<script src='../javascripts/jquery-3.2.1.js'></script>\n";

    $mainJavaScript = <<< EOD
<script>
	var arname = '';
	function loadThePage(div_id, script) {
		$("#"+div_id).html('<center><br><strong>Loading...</strong><br></center>');
    		if (script == "changeLocation" ) {
            	$.ajax({
                    type: "POST",
                    url: "update_node_location.php",
                    data: "arname="+arname,
                    success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "#dddddd");
    						$('#nav_link_non_mesh').css("background-color", "");
    						$('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "");
    						$('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "");
                    	}
            	});
            }
            if (script == "nonMeshStations") {
                $.ajax({
                        type: "POST",
                        url: "non_mesh_stations.php",
                        data: "arname="+arname,
                        success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "");
    						$('#nav_link_non_mesh').css("background-color", "#dddddd");
    						$('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "");
    						$('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "");
    
                        }
                });
            }
            if (script == "removedNodes") {
                $.ajax({
                        type: "POST",
                        url: "view_removed_nodes.php",
                        data: "arname="+arname,
                        success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "");
    						$('#nav_link_non_mesh').css("background-color", "");
    						$('#nav_link_removed').css("background-color", "#dddddd");
                            $('#nav_link_ignored').css("background-color", "");
    						$('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "");
    
                        }
                });
            }
            if (script == "ignoredNodes") {
                $.ajax({
                        type: "POST",
                        url: "view_ignored_nodes.php",
                        data: "arname="+arname,
                        success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "");
    						$('#nav_link_non_mesh').css("background-color", "");
    						$('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "#dddddd");
    						$('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "");
    
                        }
                });
            }
            if (script == "nodeReport") {
                $.ajax({
                        type: "POST",
                        url: "../node_report.php",
                        //data: "arname="+arname,
						//data: "admin_page=admin_page",
						data: {admin_page: "admin_page"},
                        success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "");
    						$('#nav_link_non_mesh').css("background-color", "");
    						$('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "");
    						$('#nav_link_report').css("background-color", "#dddddd");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "");
    
    					}
                });
            }
            if (script == "users") {
                $.ajax({
                        type: "POST",
                        url: "users.php",
                        data: "arname="+arname,
                        success: function(msg){
                            $("#"+div_id).html(msg);
    						$('#nav_link_location').css("background-color", "");
    						$('#nav_link_non_mesh').css("background-color", "");
    						$('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "");
    						$('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "#dddddd");
    						$('#nav_link_other').css("background-color", "");
    
    					}
                });
            }
    	if (script == "otherAdmin") {
                $.ajax({
                        type: "POST",
                        url: "otherAdmin.php",
                        data: "arname="+arname,
                        success: function(msg){
                            $("#"+div_id).html(msg);
                            $('#nav_link_location').css("background-color", "");
                            $('#nav_link_non_mesh').css("background-color", "");
                            $('#nav_link_removed').css("background-color", "");
                            $('#nav_link_ignored').css("background-color", "");
                            $('#nav_link_report').css("background-color", "");
							$('#nav_link_users').css("background-color", "");
    						$('#nav_link_other').css("background-color", "#dddddd");
    
                                            }
                });
            }
            if (script == "viewMap") {
                $.ajax({
                        type: "POST",
                        url: "../map.php",
                        data: "arname="+arname,
                        success: function(msg){
                                $("#"+div_id).html(msg);
                        }
                });
            }
    }
    //auto updating values in the "status" area (hopefully)
    var db_stats_ajax_call = function() {
    		//ajax query code
    		$.ajax({
    			type: "GET",
    			url: "status_updates.php",
    			data: "arname="+arname,
    			success: function(msg) {
    				$("#admin_status").html(msg);
    			}
    		});
    }
    var interval = 1000 * 60 * 1; //every 1 minute
    setInterval(db_stats_ajax_call, interval);
</script>
EOD;
    if (isset($_SESSION['userLoggedIn'])) {
		echo $mainJavaScript ."\n";
    }
    
	echo "</head>\n";
	echo "<body>\n";
	echo "<div id='admin_wrapper'>\n";
	
	echo "<div id='admin_header'>\n";
	
	echo "<img style='height: 1.5em;' src='../images/MESHMAP_LOGO.svg'>\n";
	echo "<strong><span class='em1-5Text'><a class='normalTextLink' id='admin_main_link' href=''>MeshMap Admin</a></span></strong>\n";
	echo "<span class='emDot5Text'>(beta)</span>\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	//echo "<span style='display: inline-block' class='emDot5Text'>Logged in as: ";
	echo "<span style='display: block' class='emDot5Text'>Logged in as: ";
	
	if (isset($_SESSION['username'])) {
	    echo "<strong>" . $_SESSION['username'] . "</strong>\n";
	}
	echo "&nbsp;";
	if (isset($_SESSION['super_user']) && $_SESSION['super_user'] == 1) {
		echo "<greenText>  superuser!</greenText>\n";
	}
	echo "&nbsp;";
	echo "<form style='display: inline;' name='mapAdminLogout' method='POST'>\n";
	echo "<input type='hidden' name='logMeOut' value='1'>\n";
	echo "<input type='submit' class='linkButton' value='(Logout)'>\n";
	echo "</form>\n";
	
	echo "<br>\n";
//	if (isset($_SESSION['username']) && $_SESSION['username'] == "map-admin") {
//		echo "<redText><strong>Using default username, you might want to change it.</strong></redText>\n";
//	}
//	if (isset($_SESSION['id']) == "map-admin") {
//		$username = $_SESSION['username'];
//		$query = "select passwd from map_info where id='map-admin' and user='" . $username . "'";
//		$storedPasswd = wxc_getMySql($query);
//		$storedPasswd = $storedPasswd['passwd'];
//		if (crypt("meshmap", $storedPasswd) == $storedPasswd) {
//			echo "<br><strong><redText>DEFAULT PASSWORD!! GO CHANGE IT WITH THE USERS TAB! NOW!</redText></strong>\n"; 
//		}
//	}
	echo "</span>\n";
//	echo "<br>\n";
	echo "Running on " . $_SERVER['HTTP_HOST'] . "\n";
	echo "<br>\n";
	echo "<a href='../map_display.php'>Back to the Map</a>\n";
	
	// The links at the lower part of the "header"
	echo "<div class='admin_nav_links' id='admin_nav_links'>\n";
	echo "<a id='nav_link_location' href=\"javascript:onclick=loadThePage('admin_content', 'changeLocation');\">Node Locations</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_non_mesh' href=\"javascript:onclick=loadThePage('admin_content', 'nonMeshStations');\">Non-Mesh Markers</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_removed' href=\"javascript:onclick=loadThePage('admin_content', 'removedNodes');\">Expired Nodes</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_ignored' href=\"javascript:onclick=loadThePage('admin_content', 'ignoredNodes');\">Ignored Nodes</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_report' href=\"javascript:onclick=loadThePage('admin_content', 'nodeReport');\">Node Report</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_users' href=\"javascript:onclick=loadThePage('admin_content', 'users');\">Users</a>\n";
	echo "&nbsp;&nbsp;\n";
	echo "<a id='nav_link_other' href=\"javascript:onclick=loadThePage('admin_content', 'otherAdmin');\">Other Tasks</a>\n";
	echo "&nbsp;&nbsp;\n";
	
	// <!-- <s>View Map</s>(not yet, reload map page to see changes) -->
	// <!-- <a href="javascript:onclick=loadThePage('admin_content', 'viewMap');">View Map</a> -->
	echo "</div> <!-- end admin_nav_links inner div -->\n";
	
	echo "<div id='admin_status'>\n";
	echo "<table id='admin_sql_status_table'>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th colspan='3' class='admin_sql_status_table_background'>\n";
	echo "<strong>SQL server </strong>\n";
	
	echo "&nbsp;( " . $USER_SETTINGS['sql_server'] . " ): \n";
	if($sqlSrvStatus == 1) {
	    echo '<img class="img-valign emDot75Text" src="../images/greenDot.png">' . "\n";
	}else {
	    echo '<img class="img-valign emDot75Text" src="../images/redDot.png">' . "\n";
	}
	
	echo "</th>";
	echo "<th class=\"admin_sql_status_table_background\"></th>\n";
	echo "<th class=\"admin_sql_status_table_background\"></th>\n";
	echo "<th style=\"text-align: right;\" colspan=\"3\" class=\"admin_sql_status_table_background\">\n";
	echo "Currently Polling Nodes: ";
	if($lastUpdateNodeInfo['currently_running'] == 1) {
	    echo '<img class="img-valign emDot75Text" src="../images/greenDot.png">' . "\n";
	}else {
	    echo '<img class="img-valign emDot75Text" src="../images/redDot.png">' . "\n";
	}
	echo "</tr>";
	echo "</thead>\n";
	echo "<tbody>";
	echo "<tr>\n";
	echo "<td class=\"admin_sql_status_table_background\">Nodes:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalNumNodes</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">With Locations:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalNumNodesWithLocations</td>\n";
	echo "<td colspan=\"2\" class=\"admin_sql_status_table_background\">Nodes Last Polled:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">" . $lastUpdateNodeInfo['script_last_run'] . "\n";
	echo "<td class=\"admin_sql_status_table_background\"></td>\n";
	echo "</tr>";
	echo "<tr>";
	echo "<td class=\"admin_sql_status_table_background\">Links:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalNumLinks</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">With Locations:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalNumLinksWithLocations</td>\n";
	echo "<td colspan=\"2\" class=\"admin_sql_status_table_background\">Links Last Updated:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">" . $lastUpdateLinkInfo['table_last_update'] . "\n";
	echo "<td class=\"admin_sql_status_table_background\">&nbsp;</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class=\"admin_sql_status_table_background\">Expired:</td>\n";
	echo "<td style=\"text-align: left;\" class=\"admin_sql_status_table_background\">$totalRemovedNodes</td>\n";
	echo "<td style=\"text-align: right;\" class=\"admin_sql_status_table_background\">Ignored:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalIgnoredNodes</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">Non Mesh Icons:</td>\n";
	echo "<td class=\"admin_sql_status_table_background\">$totalNumNonMeshMarkers</td>\n";
	echo "<td colspan=\"2\" style=\"text-align: right;\" class=\"admin_sql_status_table_background\">Parallel Mode: ";
		if ($USER_SETTINGS['node_polling_parallel'] == "1") {
		    echo "Yes";
		}else {
		    echo "No";
		}
	echo "</td>\n";
	echo "</tr>\n";
	echo "</tbody>";
	echo "</table>\n";
	echo "</div> <!-- end admin_status inner div -->\n";
	
	echo "</div> <!-- end admin_header div -->\n";
	
	echo "<div id='admin_content'>\n";
	echo "<br>\n";
	echo "Please use the tabs/links above to navigate to the different sections.\n";
	echo "<br>\n";
	echo "<br>\n";
	echo "Most tables are sortable, just click on the header.\n";
	echo "<br>\n";
	echo "<br>\n";
	echo "Eventually, you should be able to have different users and actually have to login to use most of this.\n";
	echo "<br>\n";
	echo "<br>\n";
	echo "Have fun and let me know any issues or ideas you find.\n";
	echo "<br>\n";
	echo "(especially any \"metrics\" you can think of to watch in the status area)\n";
	echo "<br>\n";
	echo "-wxc\n";
	echo "</div>\n";
	
	echo "</div> <!-- wrapper close -->\n";
	echo "<a href='../map_display.php'>Back to the Map</a>\n";
	echo "</body>\n";
	echo "</html>\n";
}
mysqli_close($sql_connection);
?>
