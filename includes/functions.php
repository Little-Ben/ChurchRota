<?php


function utf8_wrapper($txt) {
	if (!ini_get('default_charset')=='utf-8') {
		return utf8_encode($txt);
	}else{
		return $txt;
	}
}

include('includes/functions.mail.php');
include('includes/functions.remove.php');
include('includes/functions.discussion.php');
include('includes/functions.event.php');
include('includes/functions.password.php');
include('includes/functions.users.php');
include('includes/functions.database.php');

if((isset($holdQuery)) || ($holdQuery == true)) {
		//set variables during installtion to default values
		$owner = 'A Church';
		$owneremail = '-';
		$version = '0.0.0';
		$debug = 0;	

}else{
	//if call is not during installation, 
	//query real values from db for these variables
	$sql = "SELECT * FROM cr_settings";
	$result = mysql_query($sql) or die(mysql_error());

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$owner = $row['owner'];
		$owneremail = $row['adminemailaddress'];
		$version = $row['version'];	
		$debug = $row['debug_mode'];
	}
}

function isAdmin() {
	if($_SESSION['isAdmin'] == "1") {
		return true;
	} else {
		return false;
	}
}

function isBandAdmin($userid) {

	$sqlUsers = "SELECT * FROM cr_users WHERE id = '$userid'";
	//echo $sqlUsers;
	$resultUsers = mysql_query($sqlUsers) or die(mysql_error); 

	while($rowUsers =  mysql_fetch_array($resultUsers, MYSQL_ASSOC)) {
		$userisBandAdmin = $rowUsers['isBandAdmin'];
	}
	//echo "->".$userisBandAdmin;

	if($userisBandAdmin == "1") {
		return true;
	} else {
		return false;
	}
}

function isEventEditor($userid) {

	$sqlUsers = "SELECT * FROM cr_users WHERE id = '$userid'";
	//echo $sqlUsers;
	$resultUsers = mysql_query($sqlUsers) or die(mysql_error); 

	while($rowUsers =  mysql_fetch_array($resultUsers, MYSQL_ASSOC)) {
		$userisEventEditor = $rowUsers['isEventEditor'];
	}
	//echo "->".$userisEventEditor;

	if($userisEventEditor == "1") {
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

function insertStatistics($type,$script,$detail1="",$detail2="",$detail3="") {

	//if type=logout, then update login record (session-statitic-id) and exit 
	//fallthrough if no login_statistic_id in session 
	if (strtolower($detail1) == 'logout') {  
		$stat_id = $_SESSION['login_statistic_id'];
		if (($stat_id != "")&&($stat_id != "0")) {
			$sql =        "UPDATE cr_statistics ";
			$sql = $sql . "SET detail1=concat(detail1,'/','$detail1'), detail2=TIMEDIFF('" . date("Y-m-d H:i:s") . "',date)";
			$sql = $sql . "WHERE id=" . $stat_id;
			if (!mysql_query($sql))
			{
				die('Error: ' . mysql_error());
			}
			return;
		}
	}
	
	//if not type=logout (or missing login_statistic_id in session), 
	//then insert new record with given parameters to db
	
	//insert of statistic info
	$sql =        "INSERT INTO cr_statistics (userID,date,type,script,detail1,detail2,detail3) ";
	$sql = $sql . "VALUES ('".$_SESSION['userid']."','" . date("Y-m-d H:i:s") . "','$type','$script','$detail1','$detail2','$detail3')";
	if (!mysql_query($sql))
	{
		die('Error: ' . mysql_error());
	}
	
	//save auto-increment-id as session-statistic-id, when type=login 
	if (strtolower($detail1) == 'login') {
		//get inserted auto-increment-id
		$_SESSION['login_statistic_id'] = mysql_insert_id();
	}
	
}



?>