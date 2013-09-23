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


?>