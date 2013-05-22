<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2011 David Bunce

    Church Rota is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Church Rota is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Church Rota.  If not, see <http://www.gnu.org/licenses/>.
*/

function notifySubscribers($id, $type, $userid) {
	if($type == "category") {
		$sql = "SELECT *, 
		(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_subscriptions`.`userID`) AS `name`, 
		(SELECT email FROM cr_users WHERE `cr_users`.id = `cr_subscriptions`.`userID`) AS `email`, 
		(SELECT name FROM cr_discussionCategories WHERE `cr_discussionCategories`.id = `cr_subscriptions`.`categoryid`) AS `categoryname`, 
		(SELECT topicName FROM cr_discussion WHERE `cr_discussion`.id = `cr_subscriptions`.`topicid` GROUP BY topicname) AS topicname,
		(SELECT `adminemailaddress` FROM cr_settings) AS `siteadmin`
		FROM cr_subscriptions WHERE categoryid = '$id' AND userid != '$userid'";
		$message = "There has been a new post in the following category: ";
	} else if($type == "post") {
		$sql = "SELECT *, (SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_subscriptions`.`userID`)
		AS `name`, (SELECT email FROM cr_users WHERE `cr_users`.id = `cr_subscriptions`.`userID`) AS `email`, 
		(SELECT `adminemailaddress` FROM cr_settings) AS `siteadmin`,
		(SELECT name FROM cr_discussionCategories WHERE `cr_discussionCategories`.id = `cr_subscriptions`.`categoryid`) 
		AS `categoryname`, (SELECT topicName FROM cr_discussion WHERE `cr_discussion`.id = `cr_subscriptions`.`topicid` GROUP BY topicname) 
		AS topicname FROM cr_subscriptions WHERE topicid = '$id' AND userid != '$userid'";
		$message = "There has been a new post in the following discussion: ";
	}
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		$postname = $row['name'];
		if($type == "category") {
			$objectname = $row['categoryname'];
		} else if($type == "post") {
			$objectname = $row['topicname'];
		}
		$categoryname = $row['categoryname'];
		$to = $row['email'];
		$subject = "New post: " . $objectname;
		
		$headers = 'From: ' .$row['siteadmin'] . "\r\n" .
		'Reply-To: ' .$row['siteadmin'] . "\r\n";
		
		$finalmessage = "Dear " . $postname . "\n \n" . $message . $objectname . "\n \n" .
		"To see the post, please login using your username and password";
		
		mail($to, $subject, $finalmessage, $headers);
	 
	}
}

function mailNewUser($firstname, $lastname, $email, $username, $password) {
	$sql = "SELECT siteurl, newusermessage, adminemailaddress FROM cr_settings";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$message = $row['newusermessage'];
		$siteurl = $row['siteurl'];
		$sitadmin = $row['adminemailaddress'];
	}
		$name = $firstname . " " . $lastname;
		$message = str_replace("[name]", $name, $message);
		$message = str_replace("[username]", $username, $message);
		$message = str_replace("[password]", $password, $message);
		$message = str_replace("[siteurl]", $siteurl, $message);
		
		$headers = 'From: ' .$row['siteadmin'] . "\r\n" .
		'Reply-To: ' .$row['siteadmin'] . "\r\n";
		
		$subject = "Important information: New user account created";
		
		mail($email, $subject, $message, $headers);
		
		$adminemail = $siteadmin;
		$subject = "ADMIN COPY: " . $subject;
		mail($adminemail, $subject, $message, $headers);
		
}

function emailTemplate($message, $name, $date, $location, $rehearsal, $rotaoutput, $username, $siteurl) {
	$skillfinal = '';
	$message = str_replace("[name]", $name, $message);
	$message = str_replace("[date]", $date, $message);
	$message = str_replace("[location]", $location, $message);
	$message = str_replace("[rehearsal]", $rehearsal, $message);
	if(is_array($rotaoutput)):
		foreach ($rotaoutput as $key => $skill):
			$skillfinal = $skillfinal . $skill . '
		';
		endforeach;
	else:
		$skillfinal = $rotaoutput;
	endif;
	$message = str_replace("[rotaoutput]", $skillfinal, $message);
	$message = str_replace("[siteurl]", $siteurl, $message);
	$message = str_replace("[username]", $username, $message);
	// echo '<p>' . $message . '</p>';
	return $message;
}

function notifyIndividual($userID, $eventID, $skillID) {
	
	$query = "SELECT *,
	(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) AS `name`,
	(SELECT email FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `email`, 
	(SELECT id FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `userid`, 
	(SELECT username FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `username`,
	(SELECT `notificationemail` FROM cr_settings ) AS `notificationmessage`, 
	(SELECT `adminemailaddress` FROM cr_settings) AS `siteadmin`,
	(SELECT `norehearsalemail` FROM cr_settings) AS `norehearsalemail`,
	(SELECT `yesrehearsal` FROM cr_settings) AS `yesrehearsal`,
	(SELECT `siteurl` FROM cr_settings) AS `siteurl`,
	(SELECT `type` FROM cr_events WHERE id = '$eventID') AS `eventType`,
	(SELECT `location` FROM cr_events WHERE id = '$eventID') AS `eventLocation`,
	(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = eventType) AS eventTypeFormatted,
	(SELECT `rehearsal` FROM cr_eventTypes WHERE cr_eventTypes.id = eventType) AS eventRehearsal,
	(SELECT `rehearsal` FROM cr_events WHERE id = '$eventID') AS `eventRehearsalChange`,
	(SELECT `description` FROM cr_locations WHERE cr_locations.id = eventLocation) AS eventLocationFormatted,
	(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`, 
	(SELECT `rehearsal` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `rehearsal`, GROUP_CONCAT(skill) AS joinedskill 
	FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID') 
	AND skillID = '$skillID' GROUP BY userID, groupID ORDER BY groupID";
	$userresult = mysql_query($query) or die(mysql_error());
	
	while($row = mysql_fetch_array($userresult, MYSQL_ASSOC)) {
	
		$eventsql = "SELECT *, DATE_FORMAT(date,'%W, %M %e') AS sundayDate, DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') AS rehearsalDateFormatted FROM cr_events WHERE id = $eventID ORDER BY date";
		$eventresult = mysql_query($eventsql) or die(mysql_error());
	
		$location = $row['eventLocationFormatted'];
		
		while($eventrow = mysql_fetch_array($eventresult, MYSQL_ASSOC)) {
			$date = $eventrow['sundayDate'];
			
			$rehearsaldate = $eventrow['rehearsalDateFormatted'];
		}
			
		$identifier = $row['groupID'];
		if($row['rehearsal'] == "1") {
			if(($row['eventRehearsal'] == "0")  or ($row['eventRehearsalChange'] == "1")) { 
				$rehearsal = $row['norehearsalemail'];
			} else { 
				$rehearsal = $row['yesrehearsal'] . " on " . $rehearsaldate . " at " . $location;
			}
		}
	
		$skill = $row['category'];
		if($row['joinedskill'] != "") {
			$skill = $skill . " - " . $row['joinedskill'];
		} else {
			// If there is no skill, then we don't need to mention this fact.
		}
		$temp_user_id = $row['userid']; 
			
		$sql = "UPDATE cr_eventPeople SET notified = '1' WHERE skillID = '$skillID' AND eventID = '$eventID'"; 
		mysql_query($sql) or die(mysql_error());
			
			
		$message = $row['notificationmessage'];
		$siteurl = $row['siteurl'];
		$username = $row['username'];
		$name = $row['name'];
		$location = $row['eventLocationFormatted'];
		$rotaoutput = $skill;
		$to = $row['email'];
		$subject = "Rota reminder: " . $date;
		
		
		$message = emailTemplate($message, $name, $date, $location, $rehearsal, $rotaoutput, $username, $siteurl);
		
		
		$headers = 'From: ' .$row['siteadmin'] . "\r\n" .
		'Reply-To: ' .$row['siteadmin'] . "\r\n";
	
		mail($to, $subject, $message, $headers);
		header( 'Location: index.php' );
	}

}

function notifyEveryone($eventID) {
	
	$query = "SELECT *,
	(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) AS `name`, 
	(SELECT email FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `email`, 
	(SELECT id FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `updateid`, 
	(SELECT username FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID`) AS `username`, 
	(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`, 
	(SELECT `notificationemail` FROM cr_settings) AS `notificationmessage`, 
	(SELECT `norehearsalemail` FROM cr_settings) AS `norehearsalemail`,
	(SELECT `yesrehearsal` FROM cr_settings) AS `yesrehearsal`,
	(SELECT `siteurl` FROM cr_settings) AS `siteurl`,
	(SELECT `type` FROM cr_events WHERE id = '$eventID') AS `eventType`,
	(SELECT `location` FROM cr_events WHERE id = '$eventID') AS `eventLocation`,
	(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = eventType) AS eventTypeFormatted,
	(SELECT `rehearsal` FROM cr_eventTypes WHERE cr_eventTypes.id = eventType) AS eventRehearsal,
	(SELECT `rehearsal` FROM cr_events WHERE id = '$eventID') AS `eventRehearsalChange`,
	(SELECT `description` FROM cr_locations WHERE cr_locations.id = eventLocation) AS eventLocationFormatted,
	(SELECT `adminemailaddress` FROM cr_settings) AS `siteadmin`,
	(SELECT `rehearsal` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `rehearsal`, 
	GROUP_CONCAT(skill) AS joinedskill 
	FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID') 
	GROUP BY userID, groupID ORDER BY groupID";
	$userresult = mysql_query($query) or die(mysql_error());
	$countarray = array();
	
	while($row = mysql_fetch_array($userresult, MYSQL_ASSOC)) {
			$skill = '';
			$thisId = $row['updateid'];
			if(in_array($thisId, $countarray)) {
			
			} else {
			$eventsql = "SELECT *, 
			DATE_FORMAT(date,'%W, %M %e') AS sundayDate, 
			DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') AS rehearsalDateFormatted 
			FROM cr_events 
			WHERE id = $eventID  ORDER BY date";
			$eventresult = mysql_query($eventsql) or die(mysql_error());
			$location = $row['eventLocationFormatted'];
			while($eventrow = mysql_fetch_array($eventresult, MYSQL_ASSOC)) {
				$date = $eventrow['sundayDate'];
				
				$rehearsaldate = $eventrow['rehearsalDateFormatted'];
				$type = $row['eventTypeFormatted'];
			}

			$temp_user_id = $row['updateid']; 

			$skillssql = "SELECT *
			FROM cr_skills
			LEFT JOIN cr_eventPeople
			ON cr_skills.skillID = cr_eventPeople.skillID
			LEFT JOIN cr_groups
			ON cr_skills.groupID = cr_groups.groupID
			WHERE cr_skills.userID = '$temp_user_id' AND cr_eventPeople.eventID = '$eventID'";

			$skillsresult = mysql_query($skillssql) or die(mysql_error());


			while($skillsrow = mysql_fetch_array($skillsresult, MYSQL_ASSOC)) {
				if($skillsrow['skill'] == ''):
					$skill[] = $skillsrow['description'];
				else:
					$skill[] = $skillsrow['description'] . ' - ' . $skillsrow['skill'];
				endif;
				

			}
			

			$updateID = $row['updateid'];
			
					
			$rehearsal = "";
					if($row['rehearsal'] == "1") {
						if(($row['eventRehearsal'] == "0") or ($row['eventRehearsalChange'] == "1")) { 
							$rehearsal = $row['norehearsalemail'];
						} else { 
							$rehearsal = $row['yesrehearsal'] . " on " . $rehearsaldate . " at " . $location;
						}
					}
		$message = $row['notificationmessage'];
		$siteurl = $row['siteurl'];
		$username = $row['username'];
		$name = $row['name'];
		$location = $row['eventLocationFormatted'];
		$rotaoutput = $skill;
		$to = $row['email'];
		$subject = "Rota reminder: " . $date;
		
		
		$message = emailTemplate($message, $name, $date, $location, $rehearsal, $rotaoutput, $username, $siteurl);
		
		$headers = 'From: ' .$row['siteadmin'] . "\r\n" .
		'Reply-To: ' .$row['siteadmin'] . "\r\n";
		
		mail($to, $subject, $message, $headers);
		$countarray[] = $row['updateid'];
		}
	}
	
	$sql = "UPDATE cr_eventPeople SET notified = '1' WHERE eventID = '$eventID'"; 
	mysql_query($sql) or die(mysql_error());
			
	$sql = "UPDATE cr_events SET notified = '1' WHERE id = '$eventID'"; 
	mysql_query($sql) or die(mysql_error()); 
	header( 'Location: index.php' ) ;
}

?>