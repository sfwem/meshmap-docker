<?php

/**
 * @name        map_functions.inc
 * @category    MESH MAP
 * @subcategory Display Map routines
 * @package     Active Node List
 * @author      Eric Satterlee, KG6WXC with K6GSE
 * @copyright   Copyright (c) 2018 as Open Source
 * @license     GPLv3 or later
 * @version     $Id$
 * @abstract    Eric has written a tool called get-map-info which retrieves HAM Mesh network devices,
 *                     their configuration and Linkage information. These details are populated in several SQL tables.
 *
 *
 *             These are the working functions for the meshMap display.
 *             There are three major sections of functions in this file:
 *             MESHMAP Map       - These are used to build the Markers and Links
 *             DATABASE access   - Used for all read-only access to the Database
 *             HTML Page Setup   - Used to build the infrastructure needed for the map display
 *
 *
 ********************************************************************************************************************/

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

/*
* MESHMAP Map Functions
*
*********************************************************************************************************************/
/**
 * @param $NodeList
 * @param $TopoList
 */
function build_NodesAndLinks($NodeList, $TopoList, $MarkerList = null)
{
    global $STABLE_MESH_VERSION;
    global $useNodes;
    global $useLinks;
    global $useMarkers;
    //icon names: default (green), update firmware (red) and for the different bands

    $markerList = '';

    //then get info from the node info database
    //use it to:
    //1: create markers for the map
    //2: do lots of stuff, like, use the topology data to make the link lines,
    //try to detect the tunnels vs everything else,
    //check what band the device is on,
    //find distance and bearing info of linked nodes. :) yes really!
    //try and tell the dtd links apart from the ones that are not dtd links but appear to be,
    //*real* DTD links will be very close together, if not the same,
    //the others, (like what Ventura Mesh uses) will be far apart and therefore probably not a "real" dtd link.

    $display_NodeList = '';
    if (is_array($NodeList) && !empty($NodeList))
    {
        foreach ($NodeList as $node_info)
        {
            $node_FirmwareStatus = checkVersion($node_info['firmware_version'], $STABLE_MESH_VERSION);

            if ($node_info['lat'] && $node_info['lon']) // Are there nodes with locations?
            {
                /*
                * find the Linked nodes to list
                *********************/
                $node_LinkedList = '';
                $node_LinkedList = load_LinkedTO($node_info, $TopoList);

                /*
                * Find the Services for the node
                ********************************/
			//	$node_ServiceList = load_ServiceList($node_info['olsrinfo_json']);
			//	$node_ServiceList = '';
                $node_ServiceList = load_ServiceList($node_info['services']);

                /*
                * Build the Nodes Marker
                */
                $display_NodeList .= build_Marker($node_info, $node_ServiceList, $node_LinkedList, $node_FirmwareStatus);
            }
        }
        if (!empty($display_NodeList))
        {
            $display_NodeList = rtrim($display_NodeList); // Rtrim off an ending newline
        }
    }

    $display_MarkerList = '';
    if (isset($useMarkers) && $useMarkers)
    {
        foreach ($MarkerList as $Marker)
        {
            if ($Marker['lat'] && $Marker['lon']) //Are there Markers with location?
            {
                $display_MarkerList .= buildStationMarker($Marker); // Build Marker
            }
        }
        if (!empty($display_MarkerList))
        {
            $display_MarkerList = rtrim($display_MarkerList); // Rtrim off an ending newline
        }
    }

    /*
    * Create and Display the Link Lines
    ****************************************/
    $display_LinkList = '';
    if (isset($useLinks) && $useLinks)
    {
        $display_LinkList = load_LinkList($NodeList, $TopoList);
        if (!empty($display_LinkList))
        {
            $display_LinkList = rtrim($display_LinkList); // Rtrim off an ending newline
        }
    }
    $display_MarkersAndLinks = $display_NodeList . $display_MarkerList . $display_LinkList;
    if (!empty($display_MarkersAndLinks))
    {
        $display_MarkersAndLinks = rtrim($display_MarkersAndLinks, ','); // Trim off the very last comma
    }
    $display_MarkersAndLinks .= ";\n\n";

    return $display_MarkersAndLinks;
}

/**
 * loadServiceList
 * This function parses the json data obtained from the node.
 * It looks for all of the plugins/services installed on the node.
 * The Service_line format is:
 *         Link or host value | [TCP | UDP ] | Advertised  service
 *
 * This routine will return the last ( end ) field of the service line, which is the Advertised service
 *
 * @param $json_array
 *
 * @return string List of Services to Display
 */
function load_ServiceList($serviceList)
{
    $localServiceArray =  wxc_listServices($serviceList);
    $serviceList = '';

    if (is_array($localServiceArray))
    {
    	foreach ($localServiceArray as $key => $value)
        {
            //WXC 6-30-2017: removed links for services that have no link
            if ($value == NULL) {
                $serviceList .= '<br>'. $key;
            }else {
                //WXC 6-30-2017: Moved <br> from after link to before the link,
                //made the pop up look better.
                $serviceList .= "<br><a href='" . $value . "' target='service'>" . $key . "</a>";
            }
        }
    }
    return $serviceList;
}

/**
 * loadLinkList
 * This function iterates through the topology array to build a leaflet package polyline
 * There are four types of links defined, each is represented by a different color.
 * RF Link - Standard Mesh Node link
 * DTD Link - Mesh nodes connected by direct link ( typically via a switch )
 * Tunnel Link -
 * Infinite Link - Links reported by and marked as unusable by olsr
 *
 * @param $NodeList
 * @param $TopoList
 *
 * @return mixed
 */
function load_LinkList($NodeList, $TopoList)
{
    global $MESH_SETTINGS;
    $linkList = "\n";
    if (isset($TopoList))
    {
        foreach ($TopoList as $value)
        {
            if ($value['nodelat'] && $value['linklon'])
                //if there's no location info ignore the entry
            {
                $node_lat = $value['nodelat'];
                $node_lon = $value['nodelon'];
                $link_lat = $value['linklat'];
                $link_lon = $value['linklon'];
                $display_color = 'red';
                $display_weight = 2;
                $display_opacity = 1;
                $display_offset = 2;
                $map_LayerAssigned = '';
                $tunnel = 0;
                $dtd = 0;
                $moreThanTen = 0;
                $infinite = 0;
                $nodeHasTunnel = 0;
                $linkHasTunnel = 0;
                $nodeName = $value['node'];
                $linkName = $value['linkto'];
                if ($value['cost'] == 1.0 && !($tunnel)) //find the tunnels
                {
                    foreach ($NodeList as $searching_for_tunnels)
                    {
                        if ($searching_for_tunnels['node'] == $nodeName)
                        {
                            if ($searching_for_tunnels['active_tunnel_count'] >= 1)
                            {
                                $nodeHasTunnel = 1;
                            }
                        }
                        if ($searching_for_tunnels['node'] == $linkName)
                        {
                            if ($searching_for_tunnels['active_tunnel_count'] >= 1)
                            {
                                $linkHasTunnel = 1;
                            }
                        }
                        if (($linkHasTunnel) && ($nodeHasTunnel))
                        {
                            $tunnel = 1;
                            $nodeHasTunnel = 0;
                            $linkHasTunnel = 0;
                            $display_color = $MESH_SETTINGS['Link_Tunnel'];
                            $dtd = 1;
                            $display_opacity = 0.5;
                            $map_LayerAssigned = 'tunnelLinks';
                        }
                    }
                }
                if ($value['cost'] == 0.1 && $value['distance'] <= 0.7)
                    //find DTD links (always a cost of 0.1)
                {
                    $display_color = $MESH_SETTINGS['DTD_Link_Minus'];
                    $dtd = 1;
                    $display_opacity = 0.2;
                    $map_LayerAssigned = 'dtdLinks';
                }
                //this is for the "other" DTD links
                //using other wireless tech to create links that are seen as "DTD" by the AREDN software
                if ($value['cost'] == 0.1 && $value['distance'] > 0.7)
                {
                    $display_color = $MESH_SETTINGS['DTD_Link_Plus'];
                    $dtd = 1;
                    $display_opacity = 0.3;
                    $display_weight = 0.3;
                    $map_LayerAssigned = 'dtdLinks';
                }
                //find the "infinite cost" links (they show as "INFINITE" in the olsr files the other script changes that to 99.99)
                if ($value['cost'] == 99.99)
                {
                    $display_color = $MESH_SETTINGS['Link_Infinite'];
                    $infinite = 1;
                    $display_opacity = 0.2;
                    $map_LayerAssigned = 'infiniteLinks';
                }
                //links with a cost of less than 1.0 but NOT tunnels or DTD links
                //tunnels are always 1.0 and DTD is always 0.1
                if (($value['cost'] <= 1.000) && ($value['cost'] != 0.1) && !($tunnel))
                {
                    //these will always be a solid green
                    $display_color = $MESH_SETTINGS['Link_GOOD'];
                    $display_opacity = 0.5;
                    $map_LayerAssigned = 'rfLinks';
                }
                //links of greater than 10.000 ETX, but not "INFINITE" (these links are always red)
                if ($value['cost'] > 14.000 && $value['cost'] != 99.99)
                {
                    $display_color = $MESH_SETTINGS['Link_BAD'];
                    $moreThanTen = 1;
                    $display_opacity = 0.3;
                    $map_LayerAssigned = 'rfLinks';
                }
                //now for the more normal links
                //less than ETX 5.0 links
                if ($value['cost'] < 7 && !($dtd) && !($tunnel))
                {
                    $display_color = sprintf("#%02XFF00", ($value['cost'] - 1) * (255 / (5 - 1)));
                    $map_LayerAssigned = 'rfLinks';
                }
                //more than ETX 5.0 links
                if ($value['cost'] > 7 && !($moreThanTen) && !($infinite))
                {
                    $display_color = sprintf("#FF%02X00", ($value['cost'] - 1) * (255 / (5 - 1)) -
                        255);
                    $display_opacity = 0.5;
                    $map_LayerAssigned = 'rfLinks';
                }
                if ($map_LayerAssigned)
                {
                    $linkList .= 'L.polyline([[' . $node_lat . ',' . $node_lon . '],[' . $link_lat .
                        ',' . $link_lon . ']], {color: "' . $display_color . '", opacity: ' . $display_opacity .
                        ', weight: ' . $display_weight . ', offset: ' . $display_offset . '})
						.bindPopup("<strong>' .  $nodeName . '</strong> to <strong>' . $linkName . '</strong><br><strong>Cost</strong>: ' .
                        $value['cost'] . '")
						.addTo(' . $map_LayerAssigned . '),' . "\n";

                }
            }
        }
    }

    return $linkList;
}

/**
 * loadLinkedTO
 * This routine create a HTML formatted text list of link details.
 * The information gathered here is display as part of the node popup detail.
 *
 * @param $node_info
 * @param $TopoList
 *
 * @return string
 */
function load_LinkedTO($node_info, $TopoList)
{
    $linkedToList = '';
    $linkInfoForStationPopups = array($node_info['node'] => array());
    
    //don't init what is going to be an array as a string!!
    //$linkInfoForStationPopups = array($node_info['node'] => "");
    
    $node = $node_info['node'];
    
    //and build a big 3D array of it all
    //like  this:
    //array
    //	"NodeName"
    //		"1stLinkedNodesName"
    //			"CostFrom"
    //			"CostTo"
    //			"distance"
    //			"bearing"
    //		"2ndLinkedNodesName"
    //			"CostFrom"
    //			"CostTo"
    //etc..etc..
    
    if (isset($TopoList))
    {
        foreach ($TopoList as $value)
        {
            //if there's no location info (at each end of the link) ignore the entry
            if ($value['nodelat'] && $value['linklon'])
            {
                
                //this is the section that is exploding in php7.2.3
                if ($value['linkto'] == $node)
                {              
                    //this will be the cost FROM each linked station back TO the node we are currently looking at in the while loop
                    //had to create the array correctly! fixed 3-28-2018 - wxc
                    if (isset($linkInfoForStationPopups[$node_info['node']][$value['node']]) && is_array($linkInfoForStationPopups[$node_info['node']][$value['node']])) {
                        $linkInfoForStationPopups[$node_info['node']][$value['node']]['costFrom'] = $value['cost'];
                    }else {
                        $linkInfoForStationPopups[$node_info['node']][$value['node']] = array('costFrom' => $value['cost']);
                    }
                }
                if ($value['node'] == $node)
                {
                    //$linkedNodesKeyNameArray = array("costTo", "distance", "bearing");
                    //this will be the cost TO each linked station FROM the node we are currently looking at in the while loop
                    //distance and bearing added too for goo measure! :)
                    //$linkInfoForStationPopups[$node_info['node']][$value['linkto']] = array_fill_keys($linkedNodesKeyNameArray, "");
                    $linkInfoForStationPopups[$node_info['node']][$value['linkto']]['costTo'] = $value['cost'];
                    //add in the distance to that linked node
                    $linkInfoForStationPopups[$node_info['node']][$value['linkto']]['distance'] = $value['distance'];
                    //add in the bearing to that linked node
                    $linkInfoForStationPopups[$node_info['node']][$value['linkto']]['bearing'] = $value['bearing'];
                }
            }
        }
    }
    //now take all that info back out and put into a properly formatted string variable that we'll use later
    //this part of the function reformatted by kg6wxc.
    //the "kilometers" setting will default to "0"
    if (!isset($GLOBALS['USER_SETTINGS']['kilometers'])) {
    	$GLOBALS['USER_SETTINGS']['kilometers'] = "0";
    }
    foreach ($linkInfoForStationPopups as $nodeNameLinkArray => $linktoArray) {
        if (!empty($linktoArray)) {
            foreach ($linktoArray as $linkedNodeName => $costArray) {
            	if (!empty($costArray)) {
            		if (!empty($costArray['costTo']) && !empty($costArray['costFrom'])) {
		                //if ((isset($costArray['costTo']) == 0.1) && (isset($costArray['costFrom']) == 0.1)) {
            			if ($costArray['costTo'] == "0.1" && $costArray['costFrom'] == "0.1") {
		                    $display_cost = ' <Link_DTD>(DTD)</Link_DTD> ';
		                }else {
		                    $display_cost = ' (' . $costArray['costTo'] . '/' . $costArray['costFrom'] . ') ';
		                }
		                if ($GLOBALS['USER_SETTINGS']['kilometers']) {
		                	$linkedToList .= $linkedNodeName . $display_cost . $costArray['distance'] . 'km (' . round($costArray['distance'] * 0.62137, 2) . 'mi) ' . $costArray['bearing'] . '&deg;<br>';
		                }else {
		                	$linkedToList .= $linkedNodeName . $display_cost . round($costArray['distance'] * 0.62137, 2) . 'mi (' . $costArray['distance'] . 'km) ' . $costArray['bearing'] . '&deg;<br>';
		                }
            		}
            	}
            }
        }
    }

    return $linkedToList;
}


/**
 * buildMarker
 * This routine builds Mesh Markers.
 * These mesh markers are added to various map layer depending upon the type of node
 * The currently defined Mesh Markers are categorized by frequency
 *          2Ghz, 3Ghz, 5Ghz, 900Mhz, Linux systems
 *
 * Mesh Markers are stored in the node_info table, which is updated by the get-map-info.php routine
 *
 * @param $node_info
 * @param $node_ServiceList
 * @param $node_LinkedList
 * @param $node_FirmwareStatus
 *
 * @return string AssignedLayer
 */
function build_Marker($node_info, $node_ServiceList, $node_LinkedList, $node_FirmwareStatus)
{
	
	//timezone fixes - wxc 11-27-2018
	$nodeLastSeen = new DateTime($node_info['last_seen'], $GLOBALS['localTimeZone']);
	date_timezone_set($nodeLastSeen, $GLOBALS['localTimeZone']);
	
    
	$markerList = '';
    $nodeUrl = "<NodeTitle><a href='http://" . $node_info['node'] . ".local.mesh:8080' target='node'>" . $node_info['node'] .
        "</a></NodeTitle>";

    //Now we look at the 'channel' value, for each node, from the node_info database.
    //Then compare it to what we know about each bands channels
    //if we find a match, we assume we're in that band...

    if ($node_info['firmware_version'] == 'Linux' || $node_info['firmware_version'] == 'linux')
    { // Linux Devices ( Special for SNOOPY )
        $icon = 'linuxCircle';
        $band = '<Device_Linux>Linux</Device_Linux>';
        $AssignedLayer = 'otherStations';
    }
    else
    {
        switch ((wxc_checkBand($node_info['channel'], $node_info['board_id'])))
        {
            case '2GHz': // 2.4GHz devices
                $icon = 'twoRadioCircle';
                $band = '<Device_24GHz>2.4GHz</Device_24GHz>';
                $AssignedLayer = 'twoGHzStations';
                break;
            case '3GHz': // 3GHz devices
                $icon = 'threeRadioCircle';
                $band = '<Device_3GHz>3.4GHz</Device_3GHz>';
                $AssignedLayer = 'threeGHzStations';
                break;
            case '5GHz': // 5GHz devices
                $icon = 'fiveRadioCircle';
                $band = '<Device_5GHz>5.8GHz</Device_5GHz>';
                $AssignedLayer = 'fiveGHzStations';
                break;
            case '900MHz': // Note: Channel values borked for 900Mhz
                $icon = 'nineRadioCircle';
                $band = '<Device_900MHz>900MHz</Device_900MHz>';
                $AssignedLayer = 'nineHundredMHzStations';
                break;
            default: // unknown
                $icon = 'unknownRadioCircle';
                $band = '<unknown>?</unknown>';
                $AssignedLayer = 'otherStations';
        }
    }


    /*
    * check for Out of Date Firmware
    */
    switch ($node_FirmwareStatus)
    {
        case 1:
            $firmware = '<OutOfDate>Firmware: ' . $node_info['firmware_version'] . '</OutOfDate>';
            break;
        case 2:
            $firmware = '<Experimental>Firmware: ' . $node_info['firmware_version'] .
                '</Experimental>';
            break;
        default:
            $firmware = $node_info['firmware_version'];
    }


    /*
    * Now build the marker
    */
    if ($node_ServiceList)
    {
        $node_ServiceList = '<hr><Strong>Services:</Strong>' . $node_ServiceList;
    }
    if ($node_LinkedList)
    {
        $node_LinkedList =
            '<hr><Strong>Linked to:<br>Node Name (cost to/from) distance bearing</Strong><br>' . $node_LinkedList;
    }
    $markerList .= "L.marker([" . $node_info['lat'] . "," . $node_info['lon'] . "], {title: '" . $node_info['node'] .
        "', icon: " . $icon . "}).bindPopup(\" " . $nodeUrl . " (" . $band . ")<br>" . $node_info['lat'] .
        ", " . $node_info['lon'] . "<br>SSID: " . $node_info['ssid'] . "<br>Channel: " . $node_info['channel'] .
        ", Bandwidth: " . $node_info['chanbw'] . "<br>" . $node_info['model'] . "<br>" . $firmware .
        //"<br>Last Polled: " . $node_info['last_seen'] . '<br>' . "Uptime: " . $node_info['uptime'] . '<br>';
        
        //timezone fixes - wxc 11-26-2018
		"<br>Last Polled: " . date_format($nodeLastSeen, 'Y-m-d H:i:s T') . '<br>' . "Uptime: " . $node_info['uptime'] . '<br>';
    //dot
    if ($node_info['loadavg'] !== "Not Available") {
    	$loadavgs = unserialize($node_info['loadavg']);
    	//var_dump($loadavgs);
    	$markerList .= 'LoadAvg: 1 min ' . $loadavgs[0] . ', 5 min ' . $loadavgs[1] . ', 15 min ' . $loadavgs[2] . "<br>";
    }
    $markerList .= $node_ServiceList . $node_LinkedList . "\",{ maxWidth: 500 } ).addTo(" . $AssignedLayer . ") ,\n"; // Close out this node
    /*
    * If Upgrade Suggested add Node to that layer
    */
    if ($node_FirmwareStatus > 0)
    {
        $markerList .= "L.marker([" . $node_info['lat'] . "," . $node_info['lon'] . "], {title: '" .
            $node_info['node'] . "', icon: " . (($node_FirmwareStatus == 1) ? "redCircle" :
                "orangeCircle") . "}).bindPopup(\" " . $nodeUrl . " (" . $band . ")<br>" . $node_info['model'] .
            "<br>" . $firmware . "<br>" . (($node_FirmwareStatus == 1) ?
                "Software upgrade is highly recommended" : "Experimental") . " \").addTo(upgradeStations) ,\n";
    }

    return $markerList;
}

/**
 * buildStationMarker
 * This routine builds Station Markers. Station Markers are auxiliary (non-mesh) details to be displayed on the map.
 * These markers are added to the "Other Stations" layer.
 * The currently defined Station Markers are:
 *          Operator, Police Station, Fire Station, EOC, Hospital, Other
 *
 * Station Markers are stored in the station_info table. Station Markers are manually created.
 *
 * @param $station_info
 *
 * @return string AssignedLayer
 */
function buildStationMarker($Marker)
{

    //Now we look at the 'channel' value, for each node, from the node_info database.
    //Then compare it to what we know about each bands channels
    //if we find a match, we assume we're in that band...

    switch ($Marker['type'])
    {
        case "operator":
            // Operator Home Station
            $icon = "operatorIcon";
            $AssignedLayer = 'operatorsElements';
            break;
        case "police":
            $icon = "policeIcon";
            $AssignedLayer = 'policeElements';
            break;
        case "eoc":
            $icon = "eocIcon";
            $AssignedLayer = 'racesElements';
            break;
        case "firedepartment":
            $icon = "fireIcon";
            $AssignedLayer = 'fireElements';
            break;
        case "hospital":
            $icon = "hospitalIcon";
            $AssignedLayer = 'hospitalElements';
            break;
        default:
            // unknown
            $icon = "Red_Marker";
            $AssignedLayer = 'otherElements';
    }

    /*
    * Now build the marker
    */
    $markerList = "L.marker([" . $Marker['lat'] . "," . $Marker['lon'] . "], {title: '" . $Marker['name'] .
        "', icon: " . $icon . "}).bindPopup(\" " . $Marker['name'] . "<br>" . $Marker['description'] .
        "<br>" . $Marker['lat'] . ", " . $Marker['lon'] . "<br>TYPE: " . $Marker['type'] . "\")
        .addTo(" . $AssignedLayer . ") ,\n";

    return $markerList;
}


/*
* DATABASE access routines
**********************************************************************************************************************/
/**
 * load the Node data into an array.
 *
 * Where Clause and Orderby are optional
 * @param null $whereClause
 * @param null $orderBy
 *
 * @return array
 */
function load_Nodes($whereClause = null, $orderBy = null)
{
    /*
    * Node Table Query
    */
    global $USER_SETTINGS;
    global $useNodes;
    $NodeList = array();
    if (isset($USER_SETTINGS['sql_db_tbl_node']))
    {
        // Setup the Query ( replace WHERE and ORDER By as needed )
        $query = "SELECT * FROM " . $USER_SETTINGS['sql_db_tbl_node'] . " " .
            ($whereClause ? $whereClause : " WHERE 1=1 ") . " " .
            ($orderBy ? $orderBy : " ORDER BY node");
        // Retrieve the data
        $node_info_db = mysqli_query($GLOBALS['sql_connection'], $query)
        or die('Could not Select Items from Node Table: ' . mysqli_error());
        // Load the data into an array for ease of handling
        while ($node_info = mysqli_fetch_array($node_info_db, MYSQLI_ASSOC))
        {
            $NodeList[] = $node_info;
        }
        $useNodes = true;
    }
    else
    {
        $useNodes = false;
    }
    return $NodeList;
}


/**
 * load the Marker data into an array.
 *
 * Where Clause and Orderby are optional
 * @param null $whereClause
 * @param null $orderBy
 *
 * @return array
 */
function load_Markers($whereClause = null, $orderBy = null)
{
    /*
    * marker Table Query
    */
    global $USER_SETTINGS;
    global $useMarkers;
    $MarkerList = array();
    if (isset($USER_SETTINGS['sql_db_tbl_marker']))
    {
        // Setup the Query ( replace WHERE and ORDER By as needed )
        $query = "SELECT * FROM " . $USER_SETTINGS['sql_db_tbl_marker'] . " " .
            ($whereClause ? $whereClause : " WHERE 1=1 ") . " " .
            ($orderBy ? $orderBy : " ORDER BY name");
        // Retrieve the data
        $marker_info_db = mysqli_query($GLOBALS['sql_connection'], $query)
        or die('Could not Select Items from marker Table: ' . mysqli_error());
        // Load the data into an array for ease of handling
        while ($marker_info = mysqli_fetch_array($marker_info_db, MYSQLI_ASSOC))
        {
            $MarkerList[] = $marker_info;
        }
        $useMarkers = true;
    }
    else
    {
        $useMarkers = false;
    }
    return $MarkerList;
}


/**
 * load the Topology (or Link) Data into an array.
 *
 * Where Clause and Orderby are optional
 * @param null $whereClause
 * @param null $orderBy
 *
 * @return array
 */
function load_Topology($whereClause = null, $orderBy = null)
{
    /*
    * Topology Table Query
    */
    global $USER_SETTINGS;
    global $useLinks;
    $TopoList = array();
    if (isset($USER_SETTINGS['sql_db_tbl_topo']))
    {
        // Setup the Query ( replace WHERE and ORDER By as needed )
        $query = "SELECT * FROM " . $USER_SETTINGS['sql_db_tbl_topo'] . " " .
            ($whereClause ? $whereClause : " WHERE 1=1 ") . " " .
            ($orderBy ? $orderBy : " ORDER BY distance, cost");
        // Retrieve the data
        $topology_info_db = mysqli_query($GLOBALS['sql_connection'], $query)
        or die('Could not Select Items from Topology Table: ' . mysqli_error());
        $num = mysqli_num_rows($topology_info_db);
        // Load the data into an array for ease of handling
        for ($i = 0; $i <= $num; ++$i)
        //while ($topology_info = mysqli_fetch_array($topology_info_db, MYSQLI_ASSOC))
        {
            $TopoList[] = mysqli_fetch_array($topology_info_db, MYSQLI_ASSOC);
            //$TopoList[] = $topology_info;
        }
        $useLinks = true;
    }
    else
    {
        $useLinks = false;
    }
    return $TopoList;
}

/*
* HTML Page Setup routines
**********************************************************************************************************************/
/**
 * add_MapLayers
 *
 * This routine adds the MapLayers needed
 * the (navigator.online) controls the display for online or offline browsers
 * No it doesn't, it doesn't tell you crap really... being on a LAN makes navigator.online true!!! DO NOT USE IT.
 * It has no bearing whatsoever if internet access is available or not
 *
 *
 * @return string - This string will need to be outputed to the browser
 */
function add_MapLayers()
{
if($GLOBALS['mesh']) {
	$offline_map_tiles = $GLOBALS['USER_SETTINGS']['offlineMapTileDir'];
	$Content = <<< EOD
            var offlineMapTiles = "$offline_map_tiles";
            var defaultMap = new L.tileLayer(offlineMapTiles);

            var baseLayers = {"Offline Map": defaultMap};

        \n\n
EOD;
}else {
    $Content = "\n";
    $baseLayersString = "var baseLayers = {";
    $map_num = 0;
    foreach ($GLOBALS['USER_SETTINGS']['inetTileServer'] as $map_name => $map_url) {
        if(@strpos($map_name, "-Default")) {
            $map_name = preg_replace('/-Default/', '', $map_name);
            $Content .= "var defaultMap = new L.tileLayer('" . $map_url . "');\n";
            $baseLayersString .= "\"" . $map_name . "\": defaultMap,";
        }else {
            $map_num++;
            $Content .= "var map" . $map_num . " = new L.tileLayer('" . $map_url . "');\n";
            $baseLayersString .= "\"" . $map_name . "\": map" . $map_num . ",";
        }
    }
    $baseLayersString .= "};\n";
    $Content .= $baseLayersString;
/*
    $Content = <<< EOD
            var OSMMapURL = '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            var terrainMapURL = '//stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg';
            var topoMapURL = '//{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
            
            var OSMmap = new L.tileLayer(OSMMapURL);
            var defaultMap = new L.tileLayer(terrainMapURL);
            var topologyMap = new L.tileLayer(topoMapURL);
            
            var baseLayers = {"Topographic": topologyMap, "Street": OSMmap, "Terrain": defaultMap};

		\n\n
EOD;
*/
}
    return $Content;
}


/**
 * @param $numNodes
 * @param $numLinks
 * @param $numMarkers
 *
 * @return string
 */
function add_MapImages($numNodes, $numLinks, $numMarkers)
{
    global $MESH_SETTINGS;
    $Content = "
    // Node and Link Icons
        var greenRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_Default'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        // Out of Date node software
        var redCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_OOD'] . "',
            iconSize: [22, 22], iconAnchor: [11, 11], popupAnchor: [0, -9]
        });
        // Experiment node software
        var orangeCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_Experimental'] . "',
            iconSize: [22, 22], iconAnchor: [11, 11], popupAnchor: [0, -9]
        });
        // 900 Mhz
        var nineRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_900MHz'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        //2.4GHz
        var twoRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_24GHz'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        //3GHz
        var threeRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_3GHz'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        //5GHz
        var fiveRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_5GHz'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        //Unknown
        var unknownRadioCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_Unknown'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        
        //a special one
        var linuxCircle = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Device_Linux'] . "',
            iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });\n";

    if ($numMarkers > 0)
    {
        $Content .= "
        // Marker Icons
        var policeIcon = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_Police'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        var fireIcon = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_Fire'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        var operatorIcon = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_Operator'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        var hospitalIcon = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_Hospital'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        var eocIcon = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_EOC'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });
        var Red_Marker = L.icon({
            iconUrl: '" . $MESH_SETTINGS['Marker_Future'] . "',
            iconSize: [25, 25], iconAnchor: [9, 9], popupAnchor: [0, -9]
        });\n\n";
    }
    return $Content;
}


/**
 * @param $numNodes
 * @param $numLinks
 * @param $numMarkers
 *
 * @return string
 */
function create_MapLayers($numNodes, $numLinks, $numMarkers)
{
    //overlay groups to help superposition order
    $Content = "
    // Node Groups
    var nineHundredMHzStations = new L.LayerGroup();
    var twoGHzStations = new L.LayerGroup();
    var threeGHzStations = new L.LayerGroup();
    var fiveGHzStations = new L.LayerGroup();
    var otherStations = new L.LayerGroup();
    var upgradeStations = new L.LayerGroup();\n
";

    if ($numLinks > 0)
    {
        $Content .= "
        // Link Groups
        var rfLinks = new L . LayerGroup();
        var tunnelLinks = new L . LayerGroup();
        var dtdLinks = new L . LayerGroup();
        var infiniteLinks = new L . LayerGroup();
        var stations = new L . LayerGroup();
        var linkLines = new L . LayerGroup();
        var legendLayer = new L . layerGroup();\n
";
    }
    if ($numMarkers > 0)
    {
        $Content .= "
        // Marker Groups
        var otherElements = new L . LayerGroup();
        var policeElements = new L . LayerGroup();
        var fireElements = new L . LayerGroup();
        var hospitalElements = new L . LayerGroup();
        var racesElements = new L . LayerGroup();
        var operatorsElements = new L . LayerGroup();
        var otherElements = new L . LayerGroup();
        \n\n
";
    }

    return $Content;
}


/**
 * @param $numNodes
 * @param $numLinks
 * @param $numMarkers
 *
 * @return string
 */
function create_MapOverlays($numNodes, $numLinks, $numMarkers)
{
    global $MESH_SETTINGS;
    $Content = "// Node Overlays
    var groupedOverlays = {
        \"Stations\": {
                \"<img src='" . $MESH_SETTINGS['Device_24GHz'] . "' height='20'>2.4GHz\": twoGHzStations,
                \"<img src='" . $MESH_SETTINGS['Device_3GHz'] . "' height='20'>3.4GHz\": threeGHzStations,
                \"<img src='" . $MESH_SETTINGS['Device_5GHz'] . "' height='20'>5.8GHz\": fiveGHzStations,
                \"<img src='" . $MESH_SETTINGS['Device_900MHz'] . "' height='20'>900MHz\": nineHundredMHzStations,
                \"<img src='" . $MESH_SETTINGS['Device_Linux'] . "' height='20'>Additional Mesh\": otherStations,
                \"<img src='" . $MESH_SETTINGS['Device_OOD'] . "' height='20'>Check Versions\": upgradeStations
        }";

    if ($numLinks > 0)
    {
        $Content .= "
                ,\n
        // Link Overlays
    \"Links\": {
    \"<Link_RF_Only>RF Only</Link_RF_Only>\": rfLinks,
            \"<Link_DTD>DTD</Link_DTD>\": dtdLinks,
            \"<Link_Tunnel>Tunnels</Link_Tunnel>\": tunnelLinks,
            \"<Link_Infinite>Infinite</Link_Infinite>\": infiniteLinks
        }\n";
    }

    if ($numMarkers > 0)
    {
        $Content .= "
        ,\n
        // Marker Layers
        \"Additional\": {
            \"<img src='" . $MESH_SETTINGS['Marker_Police'] . "' height='20'>Police\": policeElements,
            \"<img src='" . $MESH_SETTINGS['Marker_Fire'] . "' height='20'>Fire Dept\": fireElements,
            \"<img src='" . $MESH_SETTINGS['Marker_Hospital'] . "' height='20'>Hospital\": hospitalElements,
            \"<img src='" . $MESH_SETTINGS['Marker_EOC'] . "' height='20'>EOC\": racesElements,
            \"<img src='" . $MESH_SETTINGS['Marker_Operator'] . "' height='20'>Operators\": operatorsElements,
            \"<img src='" . $MESH_SETTINGS['Marker_Future'] . "' height='20'>Future Mesh\": otherElements
        }\n";
    }
    $Content .= "};\n\n";

    return $Content;
}


/**
 * @return string
 */
function create_MapLegend()
{
    //legend overlay
    $Content = <<< EOD
	var legendHidden = L.control({position: 'topright'});
	legendHidden.onAdd = function(map) {
		var div = L.DomUtil.create('div', 'info legendHidden', L.DomUtil.get('map'));
		div.innerHTML += '<button id="legendLinkButtonHidden" onclick="hideLegend()">Map Legend</button>';
		return div;
	};
    var legend = L.control({position: 'topright'});
    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend', L.DomUtil.get('map'));
        div.innerHTML +=
        '<div id="legend">' +
                '<div id="legendGradientRectangle">1.000&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Link Cost&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10.000</div>' +
                '<div id="legendDTDLink">DTD</div>' +
                '<div id="legendOtherWireless">RF DTD</div>' +
                '<div id="legendTunnelLink">Tunnel</div>' +
                '<div id="legendInfinite">Infinite</div>' +
				'<div id="legendUnknownOther">' +
						'<img class="legendBandIcon" src="images/mapMarkers/greyRadioCircle-icon.png">Other' +
				'</div>' +
                '<div id="legend900MHz">' +
                        '<img class="legendBandIcon" src="images/mapMarkers/magentaRadioCircle-icon.png">900MHz' +
                '</div>' +
                '<div id="legend2GHz">' +
                        '<img class="legendBandIcon" src="images/mapMarkers/purpleRadioCircle-icon.png">2GHz' +
                '</div>' +
                '<div id="legend3GHz">' +
                        '<img class="legendBandIcon" src="images/mapMarkers/blueRadioCircle-icon.png">3GHz' +
                '</div>' +
                '<div id="legend5GHz">' +
                        '<img class="legendBandIcon" src="images/mapMarkers/goldRadioCircle-icon.png">5GHz' +
                '</div>' +
				'<div id="legendHide"><button id="legendLinkButton" onclick="hideLegend()">Hide</button></div>' +
        '</div>';
//            '<img src="images/mapLegend/LinkCostLegend.jpg" alt="legend" width="175" height="25"><br>' +
//            '<img src="images/mapLegend/DTDLinkLegend.jpg" alt="legend" width="75" height="60" align="right">';
        return div;
    };\n\n
EOD;

    return $Content;
}


/**
 * @return string
 */
function create_MapImage()
{
    global $USER_SETTINGS;
    //init the map with size, zoom level, center co-ords, etc...
    //http://leafletjs.com/reference-1.0.3.html#control-zoom
    $Content = "
	if (typeof rfLinks === 'undefined' || rfLinks === null) {
		var map = L.map('mapid', {
			//attributionControl: false,
	        center: [" . $USER_SETTINGS['map_center_coordinates'] . "],
	        zoom: [" . $USER_SETTINGS['map_initial_zoom_level'] . "],            
	        minZoom: 0,
			maxZoom: 18,
			//zoomSnap: 0,
			zoomSnap: 1,
			//zoomDelta: 0.25,
			zoomDelta: 1,
	        //layers: [defaultMap, twoGHzStations, threeGHzStations, fiveGHzStations, nineHundredMHzStations, otherStations, rfLinks, tunnelLinks, dtdLinks, infiniteLinks],
	        layers: [defaultMap, twoGHzStations, threeGHzStations, fiveGHzStations, nineHundredMHzStations, otherStations],
			//layers: [defaultMap],
			fullscreenControl: true
    	});\n\n
	}else {
	    var map = L.map('mapid', {
			//attributionControl: false,
	        center: [" . $USER_SETTINGS['map_center_coordinates'] . "],
	        zoom: [" . $USER_SETTINGS['map_initial_zoom_level'] . "],            
	        minZoom: 0,
			maxZoom: 18,
			//zoomSnap: 0,
			zoomSnap: 1,
			//zoomDelta: 0.25,
			zoomDelta: 1,
	        layers: [defaultMap, twoGHzStations, threeGHzStations, fiveGHzStations, nineHundredMHzStations, otherStations, rfLinks, tunnelLinks, dtdLinks, infiniteLinks],
	        //layers: [defaultMap, twoGHzStations, threeGHzStations, fiveGHzStations, nineHundredMHzStations, otherStations],
			//layers: [defaultMap],
			fullscreenControl: true
	    });\n\n
	}
";

    return $Content;
}


/**
 * @param $numNodes
 * @param $numLinks
 * @param $numMarkers
 *
 * @return string
 */
function show_MapMarkerDetails($numNodes, $numLinks, $numMarkers, $numNodesTotal, $numLinksTotal)
{
    // Get the last time we updated the link info
    $filetime = wxc_scriptGetLastDateTime("LINKINFO", "topology");
    if ($filetime)
    {
    	date_timezone_set($filetime, $GLOBALS['localTimeZone']);
        $filetime = date_format($filetime, 'F d Y H:i:s T');
    }

    //$Content = "MeshMapDetails = '<strong>" . $numNodes . " Nodes shown, with " . $numLinks .
    //    " links " . " &nbsp;&nbsp;&nbsp;Data Last Acquired @ " . $filetime . "</strong>';\n";
    
    $Content = "MeshMapDetails = '<strong><a style=\"color: inherit;text-decoration: none;\" href=\"\" title=\"" .
    	$numNodesTotal . " Total nodes in the database." . '\n' . "Nodes without Location info cannot be shown.\">" .
    	$numNodes . "</a> Nodes shown, with <a style=\"color: inherit;text-decoration: none;\" href=\"\" title=\"" .
    	$numLinksTotal . " Total links in the database." . '\n' . "Nodes without Location info cannot have links shown.\">" . 
    	$numLinks . "</a> links " . "&nbsp;|&nbsp;Data Last Acquired @ " . $filetime . "</strong>';\n";

    $Content .= "WXCAttribution = '<a href=mailto:kg6wxc@gmail.com title=\'" .
        "MeshMap version 2.5" . '\n\n' .
    	"Map originally created by:" . '\n' .
    	"KG6WXC in Oct 2016" .'\n' .
		"With later contributions from:" . '\n' .
		"N2MH and K6GSE" . '\n' .
		"Official Testing Guinea Pig:" . '\n' .
		"N7QJK\'>KG6WXC</a>&nbsp;|&nbsp;';\n";

    $Content .= "MapAttribution = '<a href=http://leafletjs.com title=\'" .
        "Map data &copy; by OpenStreetMap (http://openstreetmap.org) with contributions from:" . '\n' .
        "CC-BY-SA (http://creativecommons.org/licenses/by-sa/2.0/" . '\n' .
        "Map tiles (http://maps.stamen.com) by Stamen Design (http://stamen.com), under CC BY 3.0" .
        '\n' . "Map style (http://viewfinderpanoramas.org)" . '\n' . "Icons (www.flaticon.com)" . '\n' .
        "OpenTopoMap (https://opentopomap.org)\'>Leaflet</a>&nbsp;';\n";

    $Content .= "attributionCtrl = map.attributionControl;\n";
    $Content .= "attributionCtrl.setPrefix(WXCAttribution + MapAttribution);\n";
    $Content .= "attributionCtrl.addAttribution(MeshMapDetails);\n";

    return $Content;
}


/**
 * @return string
 */
function instantiate_Map()
{
    $Content = <<< EOD
// Create additional Control placeholders
function addControlPlaceholders(map) {
    var corners = map._controlCorners,
        l = 'leaflet-',
        container = map._controlContainer;

    function createCorner(vSide, hSide) {
        var className = l + vSide + ' ' + l + hSide;

        corners[vSide + hSide] = L.DomUtil.create('div', className, container);
    }

    createCorner('verticalcenter', 'left');
    createCorner('verticalcenter', 'right');
}
addControlPlaceholders(map);
map.zoomControl.setPosition('verticalcenterleft');
map.fullscreenControl.setPosition('verticalcenterleft');

EOD;

    $Content .= <<< EOD
//the ruler
var rulerOptions = {position: 'verticalcenterleft',
	lengthUnit: {
		factor: 0.621371,
		display: 'Miles',
		decimal: 3}
	};
    L.control.ruler(rulerOptions).addTo(map);
    document.getElementById('ruler').title = 'Ruler\\n(ESC 1x to stop, 2x to remove)';

//the watermark logo
L.Control.Watermark = L.Control.extend({
	onAdd: function(map) {
		var img = L.DomUtil.create('img');
		img.src = 'images/MESHMAP_LOGO.svg';
		img.style.width = '75px';
		//img.className += 'watermark';
		img.style.background = 'rgba(0,0,0,0.5)';
		img.style.border = '1px solid black';
		img.style.borderRadius = '5px';
		img.style.opacity = '0.3';
		img.style.filter = 'alpha(opacity=30)';

		return img;
	},
	OnRemove: function(map) {
		//nothing
	}
});
L.control.watermark = function(opts) {
	return new L.Control.Watermark(opts);
}
L.control.watermark({position: 'bottomright'}).addTo(map);

    var hash = new L.Hash(map);

    /*
     * MeshMap Legend
     */
    legend.addTo(map);
	legendHidden.addTo(map);
    /*
     * Layer Control
     */
    var layerControls = L.control.groupedLayers(baseLayers, groupedOverlays, {position: 'verticalcenterleft'}).addTo(map);
	L.DomEvent.disableClickPropagation(layerControls._container);
	L.DomEvent.disableScrollPropagation(layerControls._container);
    /*
     * Scale Control
     */
    L.control.scale().addTo(map);\n
/*
*The "help" button
*/
var contents = "";

EOD;

    if (!isset($GLOBALS['hide_admin']) == "1") {
    	$Content .= <<< EOD
	//admin icon (the gears)
	L.Control.Admin = L.Control.extend({
		options: {
			position: 'verticalcenterleft'
		},
		onAdd: function(map) {
			var adminControl = L.DomUtil.create('div', 'leaflet-control-custom leaflet-bar');
			adminControl.style.backgroundColor = 'white';
			//adminControl.content = "\F013";
			//adminControl.font-family = "FontAwesome";
			//adminControl.style.background = "fa-cog";
			adminControl.style.width = '30px';
			adminControl.style.height = '30px';
			adminControl.title = 'Admin';
			var link = L.DomUtil.create('a', 'leaflet-bar-part leaflet-bar-part-single', adminControl);
			var gear = L.DomUtil.create('span', 'fa fa-cogs', link);
			gear.style.fontSize = '15px';
			adminControl.onclick = function() {
				//alert('you clicked it');
				window.location = "admin/admin.php";
			}
			return adminControl;
		}
	});
	L.control.admin = function(opts) {
		return new L.Control.Admin(opts);
	}
	/*
	*admin control button
	*/
	L.control.admin().addTo(map);
EOD;
    }
    
//    $help_msg = file_get_contents('../webpage/help_msg.html');
//    $Content .= "L.Control.slideMenu(\"" . $help_msg . "\").addTo(map);\n";

    $help_msg_file = fopen("help_msg.html", "r");
    if ($help_msg_file) {
        while (($line = fgets($help_msg_file)) != false) {
            $Content .= "contents += \"" . trim(preg_replace('/\s\s+/', ' ', $line)) . "\";\n";
        }
        fclose($help_msg_file);
    }else {
        $Content .= "alert('Could not open help_msg.html');\n";
    }
    $Content .= "\nL.control.slideMenu(contents, {width: '400px', position: 'verticalcenterleft'}).addTo(map);\n";
	/* Attribution */
	//attributionCtrl({position: 'bottomleft'}).addTo(map);\n
    
    return $Content;
}
?>



