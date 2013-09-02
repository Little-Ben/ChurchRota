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
		$siteadmin = $row['adminemailaddress'];
	}
		$name = $firstname . " " . $lastname;
		$message = str_replace("[name]", $name, $message);
		$message = str_replace("[username]", $username, $message);
		$message = str_replace("[password]", $password, $message);
		$message = str_replace("[siteurl]", $siteurl, $message);
		
		$headers = 'From: ' .$siteadmin . "\r\n" .
		'Reply-To: ' .$siteadmin . "\r\n";
		
		$subject = "Important information: New user account created for " . $name;
		
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


function notifyOverview($subject,$message) {

		//line seperator for mails
		//rfc sais \r\n, but this makes trouble in outlook. several spaces plus \n works fine in outlook and thunderbird.
		//spaces to suppress automatic removal of "unnecessary linefeeds" in outlook 2003
		$crlf="      \n"; 

		$sqlSettings = "SELECT * FROM cr_settings";
		$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
		$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
		$lang_locale = $rowSettings[lang_locale];
		$time_format_normal = $rowSettings[time_format_normal];
		//$userTZ="Europe/Berlin";
		$userTZ=$rowSettings[time_zone];
		$google_group_calendar=$rowSettings[google_group_calendar];
		$overviewemail = $rowSettings[overviewemail];
		$siteadmin = $rowSettings[adminemailaddress];
		
		if ($subject=="")
		{
			if ($message=="")
			{

				//$message = 'Testmail aus notifyUserList';
				//$siteurl = $row['siteurl'];
				//$username = $row['username'];
				//$name = $row['name'];
				//$location = $row['eventLocationFormatted'];
				//$rotaoutput = $skill;
				//$to = 'rota_test@schmittendrin.de';
				//$subject = "Rota Test: " ;
				
				$query="select id,DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') AS sundayDate,location,type,comment,group_concat(rota separator ' / ') as joinedskills
				from (
				select e.id,e.date, l.description location ,t.description type,e.comment,g.groupID,g.description, concat(u.firstname,' ',u.lastname) as name,CONCAT(substr(g.description,1,1), ': ', u.firstname,' ',u.lastname) as rota
				from cr_eventPeople ep, cr_events e, cr_skills s, cr_groups g, cr_users u, cr_locations l, cr_eventTypes t
				where ep.eventID=e.id
				and ep.skillID = s.skillID
				and s.groupID=g.groupID
				and s.userID=u.id
				and l.id=e.location
				and t.id=e.type
				and ((g.groupid in (10,11)) or (g.groupid=2 and u.firstname='Team'))
				AND Year(e.date) = Year(Now())
				AND (((Month(e.date) = Month(Now())) AND (Day(Now())<=20)) OR ((Month(e.date) = Month(Now())+1) AND (Day(Now())>20)))
				order by date asc, groupID desc
				) sub
				group by date,id,location,type,comment";
				$userresult = mysql_query($query) or die(mysql_error());
				
				//AND ((Month(e.date) = Month(Now())) OR (Month(e.date) = Month(Now())+1))
				
				setlocale(LC_TIME, $lang_locale); //de_DE
				
				$overview = "";
				$sundayDate;
				while($row = mysql_fetch_array($userresult, MYSQL_ASSOC)) {
					$sundayDate = $row['sundayDate'];
					$overview = $overview . strftime($time_format_normal,strtotime($row['sundayDate']));
					$overview = $overview . " - ";
					$overview = $overview . $row['joinedskills'];
					$overview = $overview . " - ";
					$overview = $overview . $row['type'];
					////$overview = $overview . $crlf;
					$overview = $overview . "\r\n";
				
				}
				$message = $overviewemail;
				////$message = str_replace("\r\n",$crlf,$message);		
				$message = str_replace("[OVERVIEW]",$overview,$message);
				
				
				$overviewMonth = strtoupper(strftime("%B",strtotime($sundayDate)));
				$overviewYear = strftime("%Y",strtotime($sundayDate));
				
				$message = str_replace("[MONTH]",$overviewMonth,$message);
				$message = str_replace("[YEAR]",$overviewYear,$message);
				
				
				$msgArray = splitSubjectMessage("Rota Overview ".$overviewMonth." ".$overviewYear,$message);
				$subject=$msgArray[0];
				$message=$msgArray[1];
				
				return $msgArray;
				//$message = emailTemplate($message, $name, $date, $location, $rehearsal, $rotaoutput, $username, $siteurl);
			}
		}
		
		$message = str_replace("\r\n",$crlf,$message); 	
		$bcc = "";
		$queryRcpt="select email from cr_users where isOverviewRecipient=1";
		$resultRcpt = mysql_query($queryRcpt) or die(mysql_error());
		$i=0;
		$err=0;
		$bcc_err="<br>";
		while($rowRcpt = mysql_fetch_array($resultRcpt, MYSQL_ASSOC)) {
		
			$teile = explode(",", $rowRcpt[email]);
			foreach ( $teile as $adr )
            {
				if (preg_replace("/([a-zA-Z0-9._%+-]+)(@)([a-zA-Z0-9.-]+)(\.)([a-zA-Z]+)/i","# # #",trim($adr))=="# # #") {
					$bcc = $bcc . 'Bcc: ' . trim($adr) . $crlf;
					$i=$i+1;
				} else {
					$bcc_err = $bcc_err . $adr . $crlf;
					$err = $err + 1;
				}
				
			}
		}
		
		//$to = '...';
		$headers = 'From: ' .$siteadmin . $crlf .
		'Reply-To: ' .$siteadmin . $crlf .
		'Mime-Version: 1.0' . $crlf .
		'Content-Type: text/plain; charset=ISO-8859-1' . $crlf .
		'Content-Transfer-Encoding: quoted-printable' . $crlf;
		
		$mailOk = FALSE;
		$mailOk = mail($siteadmin, $subject, $message, $headers.$bcc);
		//$mailOk = mail($siteadmin, $subject, $message, $headers);
		
		if ($bcc_err=="<br>")
			$bcc_err="none";
		
		if ($mailOk == TRUE)
		{
			mail($siteadmin, "ADMIN COPY: " . $subject, $message.$crlf.$bcc,$headers);
			if ($i==1)			
				return $i." mail have been sent.<br><br>".str_replace($crlf,"<br>","Address errors: ".$bcc_err."<br><br>".$headers.$bcc);
			else
				return $i." mails has been sent.<br><br>".str_replace($crlf,"<br>","Address errors: ".$bcc_err."<br><br>".$headers.$bcc);
			
			
		
		} else {
			return "Error: Error while sending mails! Please check addresses:<br><br>".str_replace($crlf,"<br>",$headers.$bcc);
		}
}

function notifyAttack($fileName,$attackType,$attackerID) {

	$query = "SELECT `siteurl`,
	`adminemailaddress` AS `siteadmin`,
	(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = $attackerID) AS `name`,
	Now() AS `attackTime`
	FROM cr_settings";
	
	$userresult = mysql_query($query) or die(mysql_error());
	
	while($row = mysql_fetch_array($userresult, MYSQL_ASSOC)) {

		$subject = "SECURITY-ALERT - Attack blocked successfully";
		$message =  "Type:\t\t " . $attackType . "<br>\r\n" . 
					"Attacker:\t " . $row[name] . "<br>\r\n" .
					"Date:\t\t " . date("Y-m-d H:i:s") . "<br>\r\n" .
					"Script:\t\t " . $fileName . "\r\n " . "<br>\r\n" ;
		
		$headers = 'From: ' . $row['siteadmin'] . "\r\n" .
		'Reply-To: ' . $row['siteadmin'] . "\r\n";
		
		$to = $row['siteadmin'];
		
		mail($to, $subject, strip_tags($message), $headers);
		
		echo $subject . "<br><br>";
		echo $message . "<br>";
		echo "An email about this incident was sent to administrator!";
	}
	header( 'Location: index.php' );	
		
}

function notifyInfo($fileName,$infoMsg,$userID) {

	$query = "SELECT `siteurl`,
	`adminemailaddress` AS `siteadmin`,
	(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = $userID) AS `name`
	FROM cr_settings";
	
	$userresult = mysql_query($query) or die(mysql_error());
	
	while($row = mysql_fetch_array($userresult, MYSQL_ASSOC)) {

		$subject = "[ChurchRota] Info - " . $infoMsg . " - " . $row[name] ;
		$message =  "Type:\t\t " . $infoMsg . "<br>\r\n" . 
					"User:\t\t " . $row[name] . "<br>\r\n" .
					"Date:\t\t " . date("Y-m-d H:i:s") . "<br>\r\n" .
					"Script:\t\t " . $fileName . "\r\n " . "<br>\r\n" ;
		
		$headers = 'From: ' . $row['siteadmin'] . "\r\n" .
		'Reply-To: ' . $row['siteadmin'] . "\r\n";
		
		$to = $row['siteadmin'];
		
		mail($to, $subject, strip_tags($message), $headers);
		
	}
	//header( 'Location: index.php' );	
		
}

function splitSubjectMessage($defaultSubject,$message) {
	if (preg_match("/(\{\{)((.)+){1}(\}\})/", $message, $matches)==1) 
		{
			$defaultSubject = $matches[2];
			//$subject = str_replace(array("{{","}}"),"",$matches[0]);
			$message = str_replace($matches[1].$matches[2].$matches[4],"",$message);
			//$message = $matches[4];
			
		//$message = $message . "\r\n\r\n";
		//$message = $message . "m0 ". $matches[0] . "\r\n";
		//$message = $message . "m1 ". $matches[1] . "\r\n";
		//$message = $message . "m2 ". $matches[2] . "\r\n";
		//$message = $message . "m3 ". $matches[3] . "\r\n";
		//$message = $message . "m4 ". $matches[4] . "\r\n";
		//$message = $message . "m5 ". $matches[5] . "\r\n";
		//$message = $message . "m6 ". $matches[6] . "\r\n";
		//$message = $message . "m7 ". $matches[7] . "\r\n";
		//$message = $message . "m8 ". $matches[8] . "\r\n";
		//$message = $message . "m9 ". $matches[9] . "\r\n";
		//$message = $message . "m10 ". $matches[10] . "\r\n";			
			
		}
		return array($defaultSubject,$message);
}

?>