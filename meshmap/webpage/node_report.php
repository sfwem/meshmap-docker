<?php

/**
 * @name       MeshMap - a dynamic map for the mesh network
 * @category   Mesh
 * @author     Eric Satterlee, KG6WXC with K6GSE
 * @version    $Id$
 * @license    GPLv3 or later
 * @abstract   Eric has written a tool called get-map-info which retrieves HAM Mesh network devices,
 *                     their configuration and Linkage information. These details are populated in several SQL tables.
 *                     The map.php routine extracts the DB details and creates a dynamic map of those nodes and links.
 *
 *
 **************************************************************************/

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

/***************************************************************************
*It is very important to change the INCLUDE_DIR variable.                *
*
*The INCLUDE_DIR variable *must* be pointing the where you have the       *
*scripts directory, some users *
*may want to seperate them for whatever reason)                           *
*This page WILL NOT RUN otherwise!!! You have been warned!!!               *
***************************************************************************/


$INCLUDE_DIR = "..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
global $MESH_SETTINGS;
$MESH_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/meshmap-settings.ini");

require $INCLUDE_DIR . "/scripts/wxc_functions.inc";
require $INCLUDE_DIR . "/scripts/map_functions.inc";

date_default_timezone_set($USER_SETTINGS['localTimeZone']);
@include $INCLUDE_DIR . "/custom.inc";
/*
* SQL Connections
*********************/
wxc_connectToMySQL();


//added by kg6wxc may 2018
/*****START*****/
$node_removed = 0;
$node_removed_name = "";
if (isset($_POST['admin_page']) && isset($_POST['remove_node_from_main_db']) == "remove_node_from_main_db") {
	$wifi_mac_address = $_POST['wifi_mac_address'];
	$node = $_POST['name'];
	$wlan_ip = $_POST['wlan_ip'];
	
	$removeIt = wxc_putMySql("delete from node_info where wifi_mac_address = '$wifi_mac_address' and node = '$node' and wlan_ip = '$wlan_ip'");
	if ($removeIt) {
		$node_removed = 1;
		$node_removed_name = $node;
	}
}
$node_remove_js = <<< EOD
<script>
$(".remove_node_from_main_db_form").submit(function(event) {

	event.preventDefault();

	var form = $(this),
		remove_node_from_main_db = form.find("input[type='hidden'][name='remove_node_from_main_db']").val(),
		wifi_mac_address = form.find("input[type='hidden'][name='wifi_mac_address']").val(),
		ip = form.find("input[type='hidden'][name='ip']").val(),
		name = form.find("input[type='hidden'][name='name']").val(),
		wlan_ip = form.find("input[type='hidden'][name='wlan_ip']").val(),
		admin_page = form.find("input[type='hidden'][name='admin_page']").val(),
		url = form.attr("action");

	var posting = $.post(url, {
						admin_page: admin_page,
						remove_node_from_main_db: remove_node_from_main_db,
						wifi_mac_address: wifi_mac_address,
						ip: ip,
						name: name,
						wlan_ip: wlan_ip
						
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});
</script>
EOD;

/*****END*****/

$NodeList = load_Nodes();           // Get the Node Data

$STABLE_MESH_VERSION = $USER_SETTINGS['current_stable_fw_version'];

/*
 * HTML Header includes all of the scripts needed by jQueryUI and datatables
 * This report is designed to run while connected to the internet.
 *********************************************************************************************************************/
$page_header = <<< EOD
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mesh Map Report</title>
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.datatables.net/rss.xml">
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"> -->
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
	<style type="text/css" class="init"></style>
    <!-- <script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js"></script> -->
	<script src="javascripts/jquery-3.2.1.js"></script>
    <!-- <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script> -->
    <script src="javascripts/jquery.dataTables.min.js"></script>
	<script class="init">
        $(document).ready(function() {
            $('#meshdata').DataTable( {
            "scrollX": true,
            "pageLength": 25,
                "columnDefs": [ {
                    "visible": true,
                    "targets": -1, 
                } ]

            } );
        } );

    </script>
EOD;
echo $page_header;

echo "</head>\n";

/*
 * Modifications from here down to fit your specific needs
 **********************************************************************************************************************/

echo "\n\n<body class=\"wide comments meshdata\">";
echo "<a name=\"top\" id=\"top\"></a>\n";
echo "<h1 class=\"page_title\">Mesh Map Nodes</h1>\n";
echo "</div>\n";
echo "<div class=\"fw-container\">\n";
echo "<div class=\"fw-body\">\n";
echo "<div class=\"content\">\n";

//added kg6wxc may 2018
/*****START*****/
if ($node_removed) {
	echo "<strong><greenText>Succesfully removed " . $node_removed_name . " from the database.</greenText></strong><br>";
}
/*****END*****/

echo "<table id=\"meshdata\" class=\"display\" cellspacing=\"0\" width=\"100%\">\n\n"; // Define the Table

echo "<thead>\n";                                        // Build the Table Header
display_HeaderTitles();
echo "</thead>\n\n";

echo "<tfoot>\n";
display_HeaderTitles();
echo "</tfoot>\n\n";


/*
 * Load the Data into the table
 */
if (is_array($NodeList) && !empty($NodeList))
{
    echo "<tbody>\n\n";
    foreach ($NodeList as $Node)
    {
    	$tz = new DateTimeZone($USER_SETTINGS['localTimeZone']);
    	$datetime = new DateTime($Node['last_seen'], $tz);
    	date_timezone_set($datetime, $tz);
        $node_FirmwareStatus = checkVersion($Node['firmware_version'], $STABLE_MESH_VERSION);
        /*
         * If you add columns here, make sure to add them to display_HeaderTitles()
         */
        echo "<tr>\n";
        echo "<td>"
	. "<a href=\"http://"
	. $Node['node']
	. ":8080\" target=\"node\">"
 	. $Node['node']
	. "</a>"
	. "</td>\n";
        echo "<td>" . $Node['lat'] . "</td>\n";
        echo "<td>" . $Node['lon'] . "</td>\n";
        echo "<td>" . $Node['ssid'] . "</td>\n";
        echo "<td>" . $Node['model'] . "</td>\n";
        echo "<td>" . $Node['firmware_mfg'] . "</td>\n";

        switch ($node_FirmwareStatus)
        {
            case 1:
                $firmware = "<font color='red'>" . $Node['firmware_version'] . "</font>";
                break;
            case 2:
                $firmware = "<font color='orange'>" . $Node['firmware_version'] . "</font>";
                break;
            default:
                $firmware = $Node['firmware_version'];
        }
        echo "<td>" . $firmware . "</td>\n";
        echo "<td align=\"center\">"
    . date_format($datetime, 'F d Y H:i:s T')
	."</td>\n";
    //added kg6wxc may 2018
    /*****START*****/
	/*
	 * Remove Button (but only in the admin page)
	 ********************************/
	if (isset($_POST['admin_page'])) {
		echo "<td>";
		echo "<form class='remove_node_from_main_db_form' action='../node_report.php' method='post'>\n";
		echo "<input type='hidden' name='remove_node_from_main_db' value='remove_node_from_main_db'>\n";
		echo "<input type='hidden' name='admin_page' value='admin_page'>\n";
		echo "<input type='hidden' name='name' value='" . $Node['node'] . "'>\n";
		echo "<input type='hidden' name='wifi_mac_address' value='" . $Node['wifi_mac_address'] . "'>\n";
		echo "<input type='hidden' name='wlan_ip' value='" . $Node['wlan_ip'] . "'>\n";
		echo "<input type='submit' value='Remove'>\n";
		echo "</form>";
		echo "</td>\n";
	}
	//echo load_ServiceList($Node['olsrinfo_json']);
    /*****END*****/
        echo "</tr>\n\n";
    }
}

echo "</tbody>\n";
echo "</table>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class=\"fw-footer\">\n";
echo "<div class=\"copyright\">\n";
echo "KG6WXC/K6GSE/N2MH software provided as open source\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
if (isset($_POST['admin_page'])) {
	echo $node_remove_js;
	echo "\n";
}
echo "</body>\n";
echo "</html>\n";

/**
 * Display the standard Header and Footer headings
 */
function display_HeaderTitles()
{
    echo "<tr>\n";
    echo "<th>Name</th>\n";
    echo "<th>lat</th>\n";
    echo "<th>lon</th>\n";
    echo "<th>ssid</th>\n";
    echo "<th>model</th>\n";
    echo "<th>mfg</th>\n";
    echo "<th>Version</th>\n";
 //   echo "<th>Services</th>\n";
    echo "<th>Last Seen</th>\n";
    echo "</tr>\n";
}
