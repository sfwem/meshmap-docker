<?php
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
	
	//downloaded file name
	$file_name = "meshmap_db_export-" . date('Y_m_d') . ".csv";
	
	// export csv
	function exportMysqlToCsv($filename = "meshmap-export.csv") {
	
	//   $conn = dbConnection();
	$conn = wxc_connectToMySQL();
	// Check connection
	    if ($conn->connect_error) {
	        die("Connection failed: " . $conn->connect_error);
	    }
	    $sql_query = "SELECT * FROM node_info";
	
	    // Gets the data from the database
	    $result = $conn->query($sql_query);
	
	    $f = fopen('php://memory', 'wt');
	    $first = true;
	    while ($row = $result->fetch_assoc()) {
	        if ($first) {
	            fputcsv($f, array_keys($row));
	            $first = false;
	        }
	        $row['sysinfo_json'] = json_encode(json_decode($row['sysinfo_json']));
	        fputcsv($f, $row);
	    } // end while
	
	    $conn->close();
	
	    $size = ftell($f);
	    rewind($f);
	
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Length: $size");
	    // Output to browser with appropriate mime type, you choose ;)
	    header("Content-type: text/x-csv");
	    header("Content-type: text/csv");
	    header("Content-type: application/csv");
	    header("Content-Disposition: attachment; filename=$filename");
	    fpassthru($f);
	    exit;
	
	}
	// call export function
	exportMysqlToCsv($file_name);
	
}
?>
