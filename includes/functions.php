<?php
include('includes/functions.mail.php');
include('includes/functions.remove.php');
include('includes/functions.discussion.php');
include('includes/functions.event.php');
include('includes/functions.password.php');
include('includes/functions.users.php');
include('includes/functions.database.php');

if($holdQuery != true) {
	$sql = "SELECT * FROM cr_settings";
	$result = mysql_query($sql) or die(mysql_error());


	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$owner = $row['owner'];
		$owneremail = $row['adminemailaddress'];
		$version = $row['version'];	
	}
}else{
		$owner = 'A Church';
		$owneremail = '-';
		$version = '0.0.0';	
}



function isAdmin() {
	if($_SESSION['isAdmin'] == "1") {
		return true;
	} else {
		return false;
	}
}

function isLoggedIn() {
	if($_SESSION['db_is_logged_in'] == true) {
		return true;
	} else {
		return false;
	}
}

function subscribeto($userid, $categoryid, $topicid) {
	$query = "INSERT INTO cr_subscriptions(userid, categoryid, topicid) VALUES ('$userid', '$categoryid', '$topicid')";
	mysql_query($query) or die (mysql_error());
}

function unsubscribefrom($subscription) {
	$query = "DELETE FROM cr_subscriptions WHERE id = '$subscription'";
	mysql_query($query) or die (mysql_error());
}

function updateSkill($key, $description) {
	
	

	$sql = "UPDATE cr_groups SET description = '$description' WHERE groupID = '$key'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
	
	
	
}

function moveSkillGroups($skillID, $value) {
	$sql = "UPDATE cr_groups SET formatgroup = '$value' WHERE groupID = '$skillID'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function updateEventType($key, $description) {
	$sql = "UPDATE cr_eventTypes SET description = '$description' WHERE id = '$key'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function updateInstruments($key, $description) {
	$sql = "SELECT name FROM cr_instruments WHERE id = '$key'";
	
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
		$oldvalue = $row['name'];
	} 
	
	$sql = "UPDATE cr_instruments SET name = '$description' WHERE id = '$key'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
	
	$sql = "UPDATE cr_skills SET skill = '$description' WHERE skill = '$oldvalue'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}


function utf8_wrapper($txt) {
	if (!ini_get('default_charset')=='utf-8') {
		return utf8_encode($txt);
	}else{
		return $txt;
	}
}

?>