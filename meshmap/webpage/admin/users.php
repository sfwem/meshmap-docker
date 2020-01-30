<?php
/*
* Aug 7, 2017 9:49:30 AM
* users.php
* Eric Satterlee - KG6WXC
* 
* admin page for user management
*/
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
	
	$sql_connection = wxc_connectToMySQL();
	
	//promote "normal" user to super user
	if (isset($_POST['promote_user'])) {
	//	$id = $_POST['id'];
	//	$user = $_POST['user'];
		if ($_POST['promote_user'] == "promote_user") {
			$query = "update users set admin = 1 where user = '" . $_POST['user'] . "' and id = '" . $_POST['id'] . "'";
			if (wxc_putMySql($query)) {
				echo "<strong><greenText>Succesfully promoted " . $_POST['user'] . " to a super-user!</greenText></strong><br>\n";
			}else {
				echo "<strong><redText>There was and error promoting the user: " . $_POST['user'] . "!!";
			}
		}elseif ($_POST['promote_user'] == "demote_user") {
			$query = "update users set admin = 0 where user = '" . $_POST['user'] . "' and id = '" . $_POST['id'] . "'";
			if (wxc_putMySql($query)) {
				echo "<strong><greenText>Succesfully demoted " . $user . " from super-user!</greenText></strong><br>\n";
			}else {
				echo "<strong><redText>There was and error demoting the user: " . $_POST['user'] . "!!";
			}
		}
	}
	//add new user
	if ((isset($_POST['add_new_user']) == "add_new_user") && (isset($_POST['new_user_name'])) && (isset($_POST['new_user_passwd']))) {
		$newUserName = $_POST['new_user_name'];
		$newUserPasswd = $_POST['new_user_passwd'];
		$super_user = 0;
		if (isset($_POST['super_user'])) {
			if ($_POST['super_user'] == "true") {
				$super_user = 1;
			}
		}
		
	//	$salt = bin2hex(random_bytes(16));
		$salt = bin2hex(openssl_random_pseudo_bytes(16));
		$cryptedPasswd = crypt($newUserPasswd, $salt);
		
		$query = "insert into users (user ,passwd, admin)
				  values ('$newUserName', '$cryptedPasswd', '$super_user')";
		
		//$addedToSql = wxc_putMySQL("INSERT INTO map_info (id, user, passwd) VALUES ('$newUserName', '$newUserName', encrypt('$newUserPasswd', '$newUserName'))");
		$addedToSql = wxc_putMySQL($query);
		if ($addedToSql = 1) {
			echo "<strong>Sucessfully added <greenText>" . $newUserName . "</greenText> to the database</strong>.<br>";
			//UNSET THE $_POST array
			$_POST = array();
		}
	}
	
	/*
	//convert an existing user to a super user
	if (isset($_POST['convert_to_super'])) {
		$id = $_POST['id'];
		$user = $_POST['user'];
		$super_user = 0;
		if (isset($_POST['super_user'])) {
			if ($_POST['super_user'] == "true") {
				$super_user = 1;
			}
		}
		$query = "update users set admin = " . $super_user . " where id = '" . $id . "' and user = '" . $user . "'";
		$addedToSql = wxc_putMySql($query);
		if ($addedToSql = 1) {
			echo "<strong>Sucessfully added <greenText>" . $newUserName . "</greenText> to the database as a super user!</strong>.<br>";
			//UNSET THE $_POST array
			$_POST = array();
		}
	}
	*/
	
	//remove a user
	//(only a super_user can remove the other users)
	if ((isset($_POST['remove_user'])) == "remove_user" && $_SESSION['super_user'] == 1) {
		$id = $_POST['id'];
		$user = $_POST['user'];
		
		if (!isset($_POST['choice'])) {
			echo "Are you sure you want to remove the user: " . $user . "?\n";
			echo "<form id='map_admin_remove_user' action='users.php' method='post'>\n";
			echo "<input type='hidden' name='id' value='" . $id . "'>\n";
			echo "<input type='hidden' name='user' value='" . $user . "'>\n";
			echo "<input type='hidden' name='remove_user' value='remove_user'>\n";
			echo "<input type='hidden' name='map_admin_remove_user' value='map_admin_remove_user'>\n";
			echo "<input name='choice' type='submit' value='Yes'>\n";
			echo "<input style='display: inline' name='choice' type='submit' value='No'>\n";
			echo "</form><br>\n";
		}
		if ((isset($_POST['choice'])) == "Yes") {
			$query = "delete from users where id = '" . $id . "' and user = '" . $user . "'";
			$user_removed = wxc_putMySql($query);
			$query = "";
			if ($user_removed) {
				echo "<strong><greenText>Success!</greenText></strong><br>\n";
				echo "User: " . $user . "was removed from the database.<br>\n";
			}else {
				echo "<strong><redText>There was an error while removing:</redText></strong> " . $user;
			}
		}elseif ((isset($_POST['choice'])) == "No") {
			echo "<strong>User:</strong> " . $user . " <strong>NOT removed from the database</strong><br>";
		}
		$_POST = array();		
	}
	
	//change a users passwd but with the "super user" account
	if ((isset($_POST['change_user_passwd'])) == "change_user_passwd" && $_SESSION['super_user'] == 1) {
		$id = $_POST['id'];
		$user = $_POST['user'];
		
		if (!isset($_POST['map_admin_change_passwd']) && !(isset($_POST['password_check']))) {
			echo "Are you sure you want to change " . $user . "'s password?\n";
			echo "<form id='map_admin_change_passwd' action='users.php' method='post'>\n";
			echo "<input type='hidden' name='id' value='" . $id . "'>\n";
			echo "<input type='hidden' name='user' value='" . $user . "'>\n";
			echo "<input type='hidden' name='change_user_passwd' value='change_user_passwd'>\n";
			echo "<input type='hidden' name='map_admin_change_passwd' value='map_admin_change_passwd'>\n";
			echo "<input name='choice' type='submit' value='Yes'>\n";
			echo "<input style='display: inline' name='choice' type='submit' value='No'>\n";
			echo "</form><br>\n";
		}
		if (isset($_POST['map_admin_change_passwd']) && !(isset($_POST['password_check']))) {
			if (isset($_POST['choice']) == "Yes") {
				echo "Input new password for user: " . $user . "<br>\n";
				echo "<form action='users.php' id='new_password_form' method='POST'>\n";
				echo "<input type='hidden' name='change_user_passwd' value='change_user_passwd'>\n";
				echo "<input type='hidden' name='password_check' value='yes'>\n";
				echo "<input type='hidden' name='id' value='" . $id . "'>\n";
				echo "<input type='hidden' name='user' value='" . $user . "'>\n";
				echo "<input type='password' name='passwd' value='' autofocus>\n";
				echo "<input type='submit' value='Submit'>\n";
				echo "</form><br>";
			}
		}
		if (isset($_POST['password_check']) == "yes") {
			$passwd = $_POST['passwd'];
		//	$salt = bin2hex(random_bytes(16));
			$salt = bin2hex(openssl_random_pseudo_bytes(16));
			$passwd = crypt($passwd, $salt);
			//	$query = "update map_info set passwd = encrypt('" . $passwd . "', '" . $id . "') where id = '" . $id . "' and user = '" . $user . "'";
			$query = "update users set passwd = '$passwd' where id = '" . $id . "' and user = '" . $user . "'";
			$success = wxc_putMySql($query);
			if ($success) {
				echo "<strong><greenText>Succesfully changed password for " . $user . "</greenText></strong><br>\n";
			}else {
				echo "<strong><redText>Unable to change password for " . $user . "</redText></strong><br>\n";
				echo "The Error that occurred was:<br>\n";
				echo mysqli_error($GLOBALS['sql_connection']);
				echo "<br>\n";
			}
			$_POST = array();
		}
	}
	
	//change a users password (you have to know the current password to change it)
	//this is for the non "super-user" accounts only
	if ((isset($_POST['change_user_passwd'])) == "change_user_passwd" && $_SESSION['super_user'] == 0) {
		$id = $_POST['id'];
		$user = $_POST['user'];
		
		if (!isset($_POST['change_user_passwd_passwd_test']) && (!isset($_POST['password_check']))) {
			echo "Changing password for user: " . $user . "<br>\n";
			echo "Input current password for " . $user . "<br>\n";
			echo "<form action='users.php' id='change_user_passwd_form' method='POST'>\n";
			echo "<input type='hidden' name='id' value='" . $id . "'>\n";
			echo "<input type='hidden' name='user' value='" . $user . "'>\n";
			echo "<input type='password' name='passwd' value='' autofocus>\n";
			echo "<input type='hidden' name='change_user_passwd' value='change_user_passwd'>\n";
			echo "<input type='hidden' name='change_user_passwd_passwd_test' value='change_user_passwd_passwd_test'>\n";
			echo "<input type='submit' value='Submit'>\n";
			echo "</form><br>\n";
		}
		if((isset($_POST['change_user_passwd_passwd_test'])) == "change_user_passwd_passwd_test") {
			$correct_passwd = 0;
			$passwd = $_POST['passwd'];
			$storedPasswd = wxc_getMySql("select passwd from users where id = '$id' and user = '$user'");
			$storedPasswd = $storedPasswd['passwd'];
			if (crypt($passwd, $storedPasswd) == $storedPasswd) {
				$correct_passwd = 1;
			}
			//$query = "select id, user from map_info where id = '" . $id . "' and user = '" . $user . "' and passwd = encrypt('" . $passwd . "' , '" . $id . "')";
			//$correct_passwd_query = mysqli_query($sql_connection, $query) or die("failed to get \"correct_passwd_query\"\n" . mysqli_error($GLOBALS['sql_connection']));
			//$correct_passwd = mysqli_num_rows($correct_passwd_query);
			if (!$correct_passwd) {
				echo "<strong><redText>Wrong Password!</redText></strong><br>\n";
			}elseif ($correct_passwd) {
				//$query = "update map_info where id = '" . $id . "' and user = '" . $user . "' and passwd = encrypt('" . $passwd . "' , '" . $id . "')";
				//wxc_putMySql($query);
				//echo "User: " . $user . " was removed from the database.<br>\n";
				echo "Input new password for user: " . $user . "<br>\n";
				echo "<form action='users.php' id='new_password_form' method='POST'>\n";
				echo "<input type='hidden' name='change_user_passwd' value='change_user_passwd'>\n";
				echo "<input type='hidden' name='password_check' value='yes'>\n";
				echo "<input type='hidden' name='id' value='" . $id . "'>\n";
				echo "<input type='hidden' name='user' value='" . $user . "'>\n";
				echo "<input type='password' name='passwd' value='' autofocus>\n";
				echo "<input type='submit' value='Submit'>\n";
				echo "</form><br>";
			}
			$_POST = array();
		}
		if ((isset($_POST['password_check'])) == "yes") {
			$passwd = $_POST['passwd'];
		//	$salt = bin2hex(random_bytes(16));
			$salt = bin2hex(openssl_random_pseudo_bytes(16));
			$passwd = crypt($passwd, $salt);
		//	$query = "update map_info set passwd = encrypt('" . $passwd . "', '" . $id . "') where id = '" . $id . "' and user = '" . $user . "'";
			$query = "update users set passwd = '$passwd' where id = '" . $id . "' and user = '" . $user . "'";
			$success = wxc_putMySql($query);
			if ($success) {
				echo "<strong><greenText>Succesfully changed password for " . $user . "</greenText></strong><br>\n";
			}else {
				echo "<strong><redText>Unable to change password for " . $user . "</redText></strong><br>\n";
				echo "The Error that occurred was:<br>\n";
				echo mysqli_error($GLOBALS['sql_connection']);
				echo "<br>\n";
			}
			$_POST = array();
		}
		
	}
	
	//change the default username
	//this can only be done by the super user account(s)
	if ((isset($_POST['change_user_name'])) == "change_user_name") {
		if (isset($_SESSION['super_user']) == 1) {
			if (!isset($_POST['new_user_name'])) {
				$id = $_POST['id'];
				$oldUserName = $_POST['user'];
				echo "Input new user name for " . $oldUserName . "<br>\n";
				echo "<form action='users.php' id='new_user_name_form' method='POST'>\n";
				echo "<input type='hidden' name='change_user_name' value='change_user_name'>\n";
				//echo "<input type='hidden' name='password_check' value='yes'>\n";
				echo "<input type='hidden' name='id' value='" . $id . "'>\n";
				echo "<input type='hidden' name='oldUser' value='" . $oldUserName . "'>\n";
				echo "<input type='text' name='new_user_name' value='' autofocus>\n";
				echo "<input type='submit' value='Submit'>\n";
				echo "</form><br>";
			}else {
				$oldUserName = $_POST['oldUser'];
				$newUserName = $_POST['new_user_name'];
				$id = $_POST['id'];
				$query = "update users set user = '$newUserName' where user = '$oldUserName' and id = '$id'";
				$result = wxc_putMySql($query);
				if ($result) {
					echo "<strong><greenText>Sucessfully changed user name from " . $oldUserName . " to " . $newUserName . "</greentText></strong><br>\n";
				}else {
					echo "<redText><strong>There was some error. Check the MySql/Apache logs.</strong></redText><br>\n";
				}
			}
		}
	}
	if ($_SESSION['super_user'] == 1) {
		echo "<form action='users.php' id='add_user' method='POST'>\n";
		echo "Add new Admin Interface user:<br>\n";
		echo "Username: <input type='text' name='new_user_name' value=''>\n";
		echo "Password: <input type='password' name='new_user_passwd' value=''>\n";
		echo "<br>\n";
		echo "Add as a super user?<input type='checkbox' id='super_user' name='super_user'><br>\n";
		echo "<input type='hidden' name='add_new_user' value='add_new_user'>\n";
		echo "<input type='submit' value='Add New User'>\n";
		echo "</form>\n";
	}
	
	$users = mysqli_query($GLOBALS['sql_connection'], "SELECT id, user, if(admin, 'Super User', 'User') as super_user, last_login FROM users where user is not NULL") or die ("Could not get list of users" . mysqli_error($GLOBALS['sql_connection']));
	if ($users) {
	    $users = mysqli_fetch_all($users, MYSQLI_ASSOC);
	    echo "<br>\n";
	    echo "Map Admin Users.<br>\n";
	    echo "<br>\n";
	    echo "<table id=\"users\">\n";
	    echo "<tr>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(0)\"><boldText>Type</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>ID</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(1)\"><boldText>User</boldText></th>\n";
	    echo "<th class=\"pointerCursor\" onclick=\"sortTable(2)\"><boldText>Last Login</boldText></th>\n";
	    echo "</tr>\n";
	    foreach ($users as $value) {
	    	$localTimeZone = new DateTimeZone($USER_SETTINGS['localTimeZone']);
	    	$lastLogin = new DateTime($value['last_login'], $localTimeZone);
	    	date_timezone_set($lastLogin, $localTimeZone);
	    	/*
	    	if ($value['id'] !== "map-admin") {
		    	echo "\n<tr><td>" . $value['id'] . "</td>" .
		 	    	"<td>" . $value['user'] . "</td>" .
		 	    	"<td>" . $value['last_login'] . "</td>" .
		 	    	//            "<td>" . $value['timestamp'] . "</td>" .
			    	"<td class='BackgroundColor'>" .
			    	"<form action='users.php' class='change_user_passwd_form' method='POST'>" .
			    	"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
			    	"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
			    	//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
			    	"<input type='hidden' name='change_user_passwd' value='change_user_passwd'>" .
			    	"<input type='submit' value='Change Password'>" .
			    	"</form>" .
			    	"</td>";
	    	}
	    	*/
	    	if ($_SESSION['super_user'] == 1) {
	    		echo "\n<tr>" .
	 	    		"<td>" . $value['super_user'] . "</td>" .
	 	    		"<td>" . $value['id'] . "</td>" .
	 	    		"<td>" . $value['user'] . "</td>" .
	 	    		"<td>" . date_format($lastLogin, 'Y-m-d H:i:s T') . "</td>" .
	 	    		//            "<td>" . $value['timestamp'] . "</td>" .
	    		//            "<td>" . $value['lon'] . "</td>" .
	    		"<td class='BackgroundColor'>" .
	    		"<form action='users.php' class='change_user_passwd_form' method='POST'>" .
	    		"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
	    		"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
	    		//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
	    		"<input type='hidden' name='change_user_passwd' value='change_user_passwd'>" .
	    		"<input type='submit' value='Change Password'>" .
	    		"</form>" .
	    		"</td>";
//	    		if ($value['id'] == "map-admin") {
//		    		echo "<td class='BackgroundColor'>" .
//		    		"<form action='users.php' class='change_user_name_form' method='POST'>" .
//		    		"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
//		    		"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
//		    		//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
//		    		"<input type='hidden' name='change_user_name' value='change_user_name'>" .
//		    		"<input type='submit' value='Change Name'>" .
//		    		"</form>" .
//		    		"</td>";
//	    		}

//	    		if ($value['id'] !== "map-admin") {
	    		if ($_SESSION['super_user'] == 1) {
		    		echo "<td class='BackgroundColor'>" .
		    		"<form action='users.php' class='remove_user_form' method='POST'>" .
		    		"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
		    		"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
		    		//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
		    		"<input type='hidden' name='remove_user' value='remove_user'>" .
		    		"<input type='submit' value='Remove'>" .
		    		"</form>" .
		    		"</td>";
	    		}
	    		if ($_SESSION['super_user'] == 1) {
	    			echo "<td class='BackgroundColor'>\n";
	    			echo "<form action='users.php' class='promote_user_form' method='post'>\n";
	    			echo "<input type='hidden' name='id' value='" . $value['id'] . "'>\n";
	    			echo "<input type='hidden' name='user' value='" . $value['user'] . "'>\n";
	    			if ($value['super_user'] == "Super User") {
	    				echo "<input type='hidden' name='promote_user' value='demote_user'>\n";
	    				echo "<input type='submit' value='Demote User'>\n";
	    			}else {
	    				echo "<input type='hidden' name='promote_user' value='promote_user'>\n";
	    				echo "<input type='submit' value='Promote User'>\n";
	    			}
	    			echo "</form>\n";
	    			echo "</td>\n";
	    		}
	    		echo "</tr>";
	    	}else {
	    		if ($value['user'] == $_SESSION['username']) {
	    		echo "\n<tr>" .
	 	    		"<td>" . $value['user'] . "</td>" .
	 	    		"<td>" . date_format($lastLogin, 'Y-m-d H:i:s T') . "</td>" .
	 	    		//            "<td>" . $value['timestamp'] . "</td>" .
	    		//            "<td>" . $value['lon'] . "</td>" .
	    		//"<td class='BackgroundColor'>" .
	    		//"<form action='users.php' class='change_user_name_form' method='POST'>" .
	    		//"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
	    		//"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
	    		//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
	    		//"<input type='hidden' name='change_user_name' value='change_user_name'>" .
	    		//"<input type='submit' value='Change Name'>" .
	    		//"</form>" .
	    		//"</td>" .
	    		"<td class='BackgroundColor'>" .
	    		"<form action='users.php' class='change_user_passwd_form' method='POST'>" .
	    		"<input type='hidden' name='id' value='" . $value['id'] . "'>" .
	    		"<input type='hidden' name='user' value='" . $value['user'] . "'>" .
	    		//            "<input type='hidden' name='passwd' value='" . $value['passwd'] . "'>" .
	    		"<input type='hidden' name='change_user_passwd' value='change_user_passwd'>" .
	    		"<input type='submit' value='Change Password'>" .
	    		"</form>" .
	    		"</td>" .
	    		"</tr>";
	    		}
	    	}
	    }
	    echo "\n\n</table>\n";
	}
	$js = <<< EOD
<script>
$(".promote_user_form").submit(function(event) {
	event.preventDefault();
	var form = $(this),
		promote_user = form.find("input[type='hidden'][name='promote_user']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		url = form.attr("action");

	var posting = $.post(url, {
						promote_user: promote_user,
						id: id,
						user: user
	});
	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#add_user").submit(function(event) {

	event.preventDefault();

	var su = document.getElementById('super_user').checked;

	var form = $(this),
		which = form.find("input[type='hidden'][name='add_new_user']").val(),
		name = form.find("input[type='text'][name='new_user_name']").val(),
		passwd = form.find("input[type='password'][name='new_user_passwd']").val(),
		//super_user = form.find("input[type='checkbox'][name='super_user']").val(),
		super_user = su,
		url = form.attr("action");

	var posting = $.post(url, {add_new_user: which,
						new_user_name: name,
						new_user_passwd: passwd,
						super_user: super_user
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
				
});
$(".remove_user_form").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		which = form.find("input[type='hidden'][name='remove_user']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		passwd = form.find("input[type='hidden'][name='passwd']").val(),
		url = form.attr("action");
	
	var posting = $.post(url, {remove_user: which,
						id: id,
						user: user,
						passwd: passwd
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$(".remove_user_form").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		remove_user = form.find("input[type='hidden'][name='remove_user']").val(),
	//	map_admin_remove_user = form.find("input[type='hidden'][name='map_admin_remove_user']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
	//	choice = form.find("input[type='submit'][name='choice']").val(),
		url = form.attr("action");

	var posting = $.post(url, {remove_user: remove_user,
					//	map_admin_remove_user: map_admin_remove_user,
						id: id,
						user: user,
					//	choice: choice
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#map_admin_remove_user").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		remove_user = form.find("input[type='hidden'][name='remove_user']").val(),
		map_admin_remove_user = form.find("input[type='hidden'][name='map_admin_remove_user']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		choice = form.find("input[type='submit'][name='choice']").val(),
		url = form.attr("action");

	var posting = $.post(url, {remove_user: remove_user,
						map_admin_remove_user: map_admin_remove_user,
						id: id,
						user: user,
						choice: choice
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#map_admin_change_passwd").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		change_user_passwd = form.find("input[type='hidden'][name='change_user_passwd']").val(),
		map_admin_change_passwd = form.find("input[type='hidden'][name='map_admin_change_passwd']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		choice = form.find("input[type='submit'][name='choice']").val(),
		url = form.attr("action");

	var posting = $.post(url, {change_user_passwd: change_user_passwd,
						map_admin_change_passwd: map_admin_change_passwd,
						id: id,
						user: user,
						choice: choice
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$(".change_user_passwd_form").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		which = form.find("input[type='hidden'][name='change_user_passwd']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		passwd = form.find("input[type='hidden'][name='passwd']").val(),
		url = form.attr("action");
	
	var posting = $.post(url, {change_user_passwd: which,
						id: id,
						user: user,
						passwd: passwd
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#change_user_passwd_form").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		other = form.find("input[type='hidden'][name='change_user_passwd_passwd_test']").val(),
		which = form.find("input[type='hidden'][name='change_user_passwd']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		passwd = form.find("input[type='password'][name='passwd']").val(),
		url = form.attr("action");
	
	var posting = $.post(url, {change_user_passwd_passwd_test: other,
						change_user_passwd: which,
						id: id,
						user: user,
						passwd: passwd
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#new_password_form").submit(function(event) {

	event.preventDefault();

	var form = $(this),
		other = form.find("input[type='hidden'][name='password_check']").val(),
		which = form.find("input[type='hidden'][name='change_user_passwd']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		passwd = form.find("input[type='password'][name='passwd']").val(),
		url = form.attr("action");

	var posting = $.post(url, {change_user_passwd: which,
						password_check: other,
						id: id,
						user: user,
						passwd: passwd
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$(".change_user_name_form").submit(function(event) {
	
	event.preventDefault();

	var form = $(this),
		which = form.find("input[type='hidden'][name='change_user_name']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		passwd = form.find("input[type='hidden'][name='passwd']").val(),
		url = form.attr("action");
	
	var posting = $.post(url, {change_user_name: which,
						id: id,
						user: user,
						passwd: passwd
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
});

$("#new_user_name_form").submit(function(event) {

	event.preventDefault();

	var form = $(this),
		change_user_name = form.find("input[type='hidden'][name='change_user_name']").val(),
		id = form.find("input[type='hidden'][name='id']").val(),
		user = form.find("input[type='hidden'][name='user']").val(),
		oldUser = form.find("input[type='hidden'][name='oldUser']").val(),
		new_user_name = form.find("input[type='text'][name='new_user_name']").val(),
		url = form.attr("action");

	var posting = $.post(url, {change_user_name: change_user_name,
						id: id,
						oldUser: oldUser,
						new_user_name: new_user_name,
	});

	posting.done(function(data) {
		$("#admin_content").html(data);
	});
						
});

//pretty much copied from w3schools.com (https://www.w3schools.com/howto/howto_js_sort_table.asp)
	function sortTable(n) {
	  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	  table = document.getElementById("removed_nodes");
	  switching = true;
	  //Set the sorting direction to ascending:
	  dir = "asc";
	  /*Make a loop that will continue until
	  no switching has been done:*/
	  while (switching) {
	    //start by saying: no switching is done:
	    switching = false;
	    rows = table.getElementsByTagName("TR");
	    /*Loop through all table rows (except the
	    first, which contains table headers):*/
	    for (i = 1; i < (rows.length - 1); i++) {
	      //start by saying there should be no switching:
	      shouldSwitch = false;
	      /*Get the two elements you want to compare,
	      one from current row and one from the next:*/
	      x = rows[i].getElementsByTagName("TD")[n];
	      y = rows[i + 1].getElementsByTagName("TD")[n];
	      /*check if the two rows should switch place,
	      based on the direction, asc or desc:*/
	      if (dir == "asc") {
	        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      } else if (dir == "desc") {
	        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      }
	    }
	    if (shouldSwitch) {
	      /*If a switch has been marked, make the switch
	      and mark that a switch has been done:*/
	      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
	      switching = true;
	      //Each time a switch is done, increase this count by 1:
	      switchcount ++;
	    } else {
	      /*If no switching has been done AND the direction is "asc",
	      set the direction to "desc" and run the while loop again.*/
	      if (switchcount == 0 && dir == "asc") {
	        dir = "desc";
	        switching = true;
	      }
	    }
	  }
	}
</script>
EOD;
	
	echo $js . "\n";
}
mysqli_close($sql_connection);
?>
