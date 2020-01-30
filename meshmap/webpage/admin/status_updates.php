<?php
/*Aug 13, 2017 9:31:21 PM
*status_updates.php
*Eric Satterlee - KG6WXC
*/
$INCLUDE_DIR = "../..";
$USER_SETTINGS = parse_ini_file($INCLUDE_DIR . "/scripts/user-settings.ini");
include $INCLUDE_DIR . "/scripts/wxc_functions.inc";
@include $INCLUDE_DIR . "/custom.inc";

$sqlSrvStatus;
$arname = "";

$sql_connection = wxc_connectToMySQL();

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
    mysqli_close($sql_connection);
}
$localTimeZone = new DateTimeZone($USER_SETTINGS['localTimeZone']);
$nodeInfoLastUpdate = new DateTime($lastUpdateNodeInfo['script_last_run'], $localTimeZone);
$linkInfoLastUpdate = new DateTime($lastUpdateLinkInfo['table_last_update'], $localTimeZone);
date_timezone_set($nodeInfoLastUpdate, $localTimeZone);
date_timezone_set($linkInfoLastUpdate, $localTimeZone);

?>
<table id="admin_sql_status_table">
<thead>
<tr>
<th colspan="3" class="admin_sql_status_table_background">
<strong>SQL server </strong>
<?php
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
//echo "<table id='admin_sql_status_table'>";
echo "<tbody>";
echo "<tr>\n";
echo "<td class=\"admin_sql_status_table_background\">Nodes:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">$totalNumNodes</td>\n";
echo "<td class=\"admin_sql_status_table_background\">With Locations:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">$totalNumNodesWithLocations</td>\n";
echo "<td colspan=\"2\" class=\"admin_sql_status_table_background\">Nodes Last Polled:</td>\n";
//echo "<td class=\"admin_sql_status_table_background\">&nbsp;</td>\n";
echo "<td class=\"admin_sql_status_table_background\">" . date_format($nodeInfoLastUpdate, 'Y-m-d H:i:s T') . "\n";
//echo "<td class=\"admin_sql_status_table_background\">" . $lastUpdateNodeInfo['script_last_run'] . "\n";
echo "<td class=\"admin_sql_status_table_background\"></td>\n";
echo "</tr>";
echo "<tr>";
echo "<td class=\"admin_sql_status_table_background\">Links:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">$totalNumLinks</td>\n";
echo "<td class=\"admin_sql_status_table_background\">With Locations:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">$totalNumLinksWithLocations</td>\n";
echo "<td colspan=\"2\" class=\"admin_sql_status_table_background\">Links Last Updated:</td>\n";
//echo "<td class=\"admin_sql_status_table_background\">Updated:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">" . date_format($linkInfoLastUpdate, 'Y-m-d H:i:s T') . "\n";
echo "<td class=\"admin_sql_status_table_background\">&nbsp;</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"admin_sql_status_table_background\">Expired:</td>\n";
echo "<td style=\"text-align: left;\" class=\"admin_sql_status_table_background\">$totalRemovedNodes</td>\n";
echo "<td style=\"text-align: right;\" class=\"admin_sql_status_table_background\">Ignored:</td>\n";
echo "<td class=\"admin_sql_status_table_background\">$totalIgnoredNodes</td>\n";
//echo "<td class=\"admin_sql_status_table_background\"></td>\n";
//echo "<td class=\"admin_sql_status_table_background\"></td>\n";
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
//echo "</table>";
// echo "<br>Nodes: " . $totalNumNodes . " \n";
// echo "&nbsp;&nbsp;With Locations: " . $totalNumNodesWithLocations . "&nbsp;\n";
// echo "<br>Links: " . $totalNumLinks . "\n";
// echo "&nbsp;&nbsp;With Locations: " . $totalNumLinksWithLocations . "\n";
// echo "<br>Expired: " . $totalRemovedNodes . "\n";
?>
</table>
