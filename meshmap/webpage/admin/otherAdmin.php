<?php
session_start();
if (!isset($_SESSION['userLoggedIn'])) {
	echo "You are not logged in!<br>\n";
	echo "This page should be run from within the admin interface!\n";
	exit;
}else {
$html = <<< EOD
<br>
<strong>Other Admin Tasks</strong>
<br>
<br>
<a href="export2csv.php">Download CSV file of the node database.</a>
<br>
<br>
<script>
var arname="";
function other_loadThePage(div_id, script) {
        if (script == "fixPolling" ) {
                $.ajax({
                type: "POST",
                url: "fixStuckPolling.php",
                data: "arname="+arname,
                success: function(msg){
			  alert('Polling should now resume.');
//                        $("#"+div_id).html(msg);
//                                                $('#nav_link_location').css("background-color", "#dddddd");
//                                                $('#nav_link_non_mesh').css("background-color", "");
//                                                $('#nav_link_removed').css("background-color", "");
//                                                $('#nav_link_report').css("background-color", "");
//                                                $('#nav_link_other').css("background-color", "");
                        }
                });
        }
}
</script>
<a href="javascript:onclick=other_loadThePage('admin_content','fixPolling');">Fix stuck polling run.</a>
<br>
(It should reset itself after 3 * node_polling_interval, but you can manually do it here also.)
EOD;

echo $html . "\n";
}

?>
