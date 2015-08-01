<?php
function addPeople($id, $skillid) {
	$sql = ("INSERT INTO cr_eventPeople (eventID, skillID) VALUES ('$id', '$skillid')");
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function removeEventMember($id, $skillid) {
	$sql = "DELETE FROM cr_eventPeople WHERE eventID = '$id' AND skillID = '$skillid'";
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}


function addPeopleBand($bandid, $skillid) {
	$sql = "INSERT INTO cr_bandMembers (bandID, skillID) VALUES ('$bandid', '$skillid')";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
	

}

function getEventDetails($eventID, $separator, $type, $apprev_description = true, $prefix="") {

	//type=0 -> all details
	//type=1 -> only groupID in 10,11,2
	//type=2 -> only event date and event type
	//type=4 -> only event type
	
	$sqlSettings = "SELECT * FROM cr_settings";
	$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
	$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
	$lang_locale = $rowSettings['lang_locale'];	
	$time_format_normal=$rowSettings['time_format_normal'];	
	setlocale(LC_TIME, $lang_locale);
	
	$sql = "SELECT e.id, e.date as eventDate, e.type as eventType, et.description as eventTypeName, g.groupID, g.description, s.skill, u.firstname, u.lastname ";
	$sql = $sql . "FROM cr_events e ";
	$sql = $sql . "LEFT OUTER JOIN cr_eventPeople ep ON e.id = ep.eventID ";
	$sql = $sql . "LEFT OUTER JOIN cr_skills s ON ep.skillID = s.skillID ";
	$sql = $sql . "LEFT OUTER JOIN cr_groups g ON s.groupID = g.groupID ";
	$sql = $sql . "LEFT OUTER JOIN cr_users u ON s.userID = u.id ";
	$sql = $sql . "INNER JOIN cr_eventTypes et ON e.type = et.id ";
	$sql = $sql . "WHERE e.id = $eventID ";
	if ($type==1)
		$sql = $sql . "AND ((g.groupid in (10,11)) OR (g.groupid=2 and u.firstname='Team')) ";
	$sql = $sql . "ORDER BY id, groupID desc, skill, firstname, lastname ";
	
	$result = mysql_query($sql) or die(mysql_error());
	
	$returnValue = "";
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$id = $row['id'];
		$eventDate = $row['eventDate'];
		$eventType = $row['eventTypeName'];
		$skill = $row['skill'];
		
		$description = $row['description'];
		if ($apprev_description) 
			$description = substr($description,0,1);
		
		$firstname = $row['firstname'];
		if ($firstname <> "Team") 
			$firstname = ltrim(substr($firstname,0,1). "." , ".");		
		
		$lastname = $row['lastname'];

		
		
		$returnValue = $returnValue . $separator;
		switch ($type) {
			case 0:
				//all persons of event
				//break;    ->  no special handling, fallthrough to case 1
			case 1:
				//only persons with groupID in 10,11,2  ->  handled in sql query
				$returnValue = $returnValue . $prefix . ltrim($description . ": "); 
				$returnValue = $returnValue . trim( $firstname . " " . $lastname);
				break;
			case 2:
				//only event date and event type
				$returnValue = $returnValue . strftime($time_format_normal,strtotime($eventDate));
				$returnValue = $returnValue . $separator;
				$returnValue = $returnValue . $eventType;
				return trim(substr($returnValue,strlen($separator)-1)); //ends while loop
				break;
			case 4:
				//only event type
				$returnValue = $returnValue . $eventType;
				return trim(substr($returnValue,strlen($separator)-1)); //ends while loop
				break;
			case 8:
				break;
		
		}
	}
	//return trim(substr($returnValue,strlen($separator)-1));
	return substr($returnValue,strlen($separator));
}


?>