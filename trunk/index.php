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

// Include files, including the database connection
include('includes/dbConfig.php');
include('includes/functions.php');

// Start the session. This checks whether someone is logged in and if not redirects them
session_start();

$sessionUserID = $_SESSION['userid'];
$userisBandAdmin = isBandAdmin($sessionUserID);
 
if (isset($_SESSION['is_logged_in']) || $_SESSION['db_is_logged_in'] == true) {
	// Just continue the code
} 

// Handle details from the header 
$removeEventID = $_GET['eventID'];
$removeWholeEvent = $_GET['wholeEventID'];
$showmyevents = $_GET['showmyevents'];
$removeSkillID = $_GET['skillID'];
$notifyIndividual = $_GET['notifyIndividual'];
$notifyEveryone = $_GET['notifyEveryone'];
$skillremove = $_GET['skillremove'];
$eventremove = $_GET['eventremove'];
$notifyOverview = $_GET['notifyOverview'];
$filter = $_GET['filter'];

if (isAdmin()) {

	// Method to remove  someone from the band
	if($eventremove == "true") {
		removeEvent($removeWholeEvent);
		header ( "Location: index.php#section" . $removeEventID);
	}

	if($skillremove == "true") {
		removeEventPeople($removeEventID, $removeSkillID);
		header ( "Location: index.php#section" . $removeEventID);
	}

	if($notifyOverview == "true") {
		//$msg = notifyOverview();
		header ( "Location: overview.php");
	}

	if($notifyEveryone == "true") {
		notifyEveryone($removeEventID);
		header ( "Location: index.php#section" . $removeEventID);
	}

	if($notifyIndividual != "") {
		notifyIndividual($notifyIndividual, $removeEventID, $removeSkillID);
		header ( "Location: index.php#section" . $removeEventID);
	} 
}

// If the form has been sent, we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$editeventID = $_GET['event'];
	$editskillID = $_POST['name'];
	$editbandID = $_POST['band'];
	
	if($editskillID != "") {
	
		$sql = ("INSERT INTO cr_eventPeople (eventID, skillID) VALUES ('$editeventID', '$editskillID')");
			if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}
		
		// After we have inserted the data, we want to head back to the main page
		 header ( "Location: index.php#section" . $editeventID);
     	 exit;
	}
	
	if($editbandID != "") {
		$sqlbandMembers = "SELECT * FROM cr_bandMembers WHERE bandID = '$editbandID'";
		$resultbandMembers = mysql_query($sqlbandMembers) or die(mysql_error());
			
		while($bandMember = mysql_fetch_array($resultbandMembers, MYSQL_ASSOC)) {
			$editskillID = $bandMember['skillID'];
				
			$sql = ("INSERT INTO cr_eventPeople (eventID, skillID) VALUES ('$editeventID', '$editskillID')");
			if (!mysql_query($sql))
 	 		{
  				die('Error: ' . mysql_error());
  			}
		}
		
		// After we have inserted the data, we want to head back to the main page
		 header ( "Location: index.php#section" . $editeventID); 
     	 exit;
	}
}
?>

<? $formatting = "light";
include('includes/header.php'); 

if(isLoggedIn()) { 

	$sqlSettings = "SELECT * FROM cr_settings";
	$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
	$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
	
	if ($rowSettings[event_sorting_latest]==1) {
		$dateOrderBy = "date desc";
	}else{
		$dateOrderBy = "date asc";
	}
	
	if ($rowSettings[logged_in_show_snapshot_button]==1) {
		$logged_in_show_snapshot_button = "1";
	}else{
		$logged_in_show_snapshot_button = "0";
	}
	?>

	<div class="filterby">
		<h2>Filter events by:</h2>
		<p>
		<?php
		$filter_sql = "SELECT * FROM cr_eventTypes where id in 
			(select `cr_events`.`type` FROM cr_events 
			WHERE date >= DATE(NOW())
			AND cr_events.deleted = 0)
			ORDER BY description";
		$result = mysql_query($filter_sql) or die(mysql_error());
	
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			?>
			<a class="eventTypeButton
				<?php
					if($filter == $row['id']) {
						echo "activefilter"; 
					}
				?>" href="index.php?filter=<?php echo $row['id']; ?>"><?php echo $row['description']; ?></a>
			<?php
		} 
		?>
	</p>
	</div>
	<?

	if($showmyevents == "" && $filter == "") {
		
		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') AS sundayDate, DATE_FORMAT(rehearsalDate,'%m/%d/%Y %H:%i:%S') AS rehearsalDateFormatted 
			FROM cr_events 
			WHERE date >= DATE(NOW())
			AND cr_events.deleted = 0
			ORDER BY " . $dateOrderBy;
	} else if($filter != "") {

		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') AS sundayDate, DATE_FORMAT(rehearsalDate,'%m/%d/%Y %H:%i:%S') AS rehearsalDateFormatted 
			FROM cr_events 
			WHERE `cr_events`.`type` = '$filter'
			AND date >= DATE(NOW())
			AND cr_events.deleted = 0
			ORDER BY " . $dateOrderBy;
	} else {

		$sql = "SELECT *,
			(SELECT userID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents') AS skilluserID,
			(SELECT groupID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID) AS skillgroupID, 
			(SELECT `description` FROM cr_groups WHERE skillgroupID = `cr_groups`.`groupID`) AS `description`,
			(SELECT skill FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID) AS skill, 
			(SELECT date FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) AS date, 
			(SELECT DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID ORDER BY date DESC) AS sundayDate, 
			(SELECT DATE_FORMAT(rehearsalDate,'%m/%d/%Y %H:%i:%S') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID ) as rehearsalDateFormatted, 
			(SELECT location FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsLocation, 
			(SELECT comment FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as comment, 
			(SELECT type FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsType,
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = cr_eventsType) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = cr_eventsLocation) AS eventLocation,
			(SELECT rehearsal FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsRehearsal

		 	FROM cr_eventPeople 
		 	INNER JOIN cr_events
			ON cr_eventPeople.eventID = cr_events.id 
			WHERE EXISTS (SELECT userID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID)
			AND EXISTS (SELECT DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID)
			AND cr_events.date >= DATE(NOW())
			AND cr_events.deleted = 0
			GROUP BY eventID
			ORDER BY " . $dateOrderBy;
	}
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if($showmyevents == "") { 
			$eventID = $row['id'];
			$rehearsal = $row['rehearsal'];
		} else { 
			$eventID = $row['eventID'];
			$rehearsal = $row['cr_eventsRehearsal'];
		}
		
		?>
		<div class="elementBackground" id="event<? echo $eventID; ?>">
			<h2><? echo '<a name="section' . $eventID . '">';
			setlocale(LC_TIME, $rowSettings[lang_locale]);
			echo strftime($rowSettings[time_format_long],strtotime($row['sundayDate']));
			//echo $row['sundayDate'];
			echo "</a>";
			if(isAdmin()||$userisBandAdmin) { 
				echo " <a href='createEvent.php?action=edit&id=$eventID'><img src='graphics/tool.png' /></a> ";
			}
			if(isAdmin()) { 				
				if($row['notified'] == "1") {
					echo "<a href='index.php?notifyEveryone=true&eventID=$eventID'><img src='graphics/emailsent.png' /></a> ";
				} else {
					echo "<a href='index.php?notifyEveryone=true&eventID=$eventID'><img src='graphics/email.png' /></a> ";
				}
				
				echo "<a href='#' data-reveal-id='deleteModal".$eventID."'><img src='graphics/close.png' /></a>"; 
			}?></h2>
			<div class="elementHead arrowwaiting"><p><? if($rehearsal != "1") { ?><strong>Rehearsal:</strong> <? echo 
			strftime($rowSettings[time_format_normal],strtotime($row['rehearsalDateFormatted']));
			?><br /><? } ?>
			<strong>Location:</strong> <? echo $row['eventLocation']; ?><br />
			<strong>Type:</strong> <? echo $row['eventType']; ?></p>
			<? if($row['comment'] != "") {
				echo "<div class='shaded'>";
				echo "<strong>Comments:</strong><p>";
				echo $row['comment'];
				echo "</p></div>";
				
			} ?>
			<div id="deleteModal<?php echo $eventID; ?>" class="reveal-modal">
     			<h1>Really delete event?</h1>
				<p>Are you sure you really want to delete the event taking place on <?php echo strftime($rowSettings[time_format_normal],strtotime($row['sundayDate'])); ?>? There is no way of undoing this action.</p>
				<p><a class="button" href="index.php?eventremove=true&wholeEventID=<?php echo $eventID; ?>">Sure, delete the event</a></p>
     			<a class="close-reveal-modal">&#215;</a>
			</div>
			</div>
			<div class="elementContent">
			<table>
			<?
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) 
				AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`, 
				(SELECT notified FROM cr_eventPeople WHERE cr_skills.skillID = cr_eventPeople.skillID AND eventID = '$eventID') AS `notified`, 
				(SELECT `formatgroup` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `formatgroup`, 
				GROUP_CONCAT(skill) AS joinedskill
				FROM cr_skills 
				WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID' AND deleted = 0) 
				GROUP BY userID, groupID ORDER BY groupID, name";
				
				$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
				echo "<p>";
				$i = 1;
				$categoryheader = "";
				$formatgroup = 1;
				$identifier = "1";
				$firsttime = 1;
			?>
			<tr>
			<?php while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
					if ($viewPeople['formatgroup'] == $formatgroup) {
						// Do nothing, because they are all in the same group	
					} else {
						// Update the group heading
						$formatgroup = $viewPeople['formatgroup'];
						$identifier = "2";
					}
					
					
					if(isAdmin()) {
					
						
						if($viewPeople['notified'] == "1") {
							$notified = " <a href='index.php?notifyIndividual=$viewPeople[userID]&eventID=$eventID&skillID=$viewPeople[skillID]'>
							<img src='graphics/emailsent.png' /></a> ";
						} else {
							$notified = " <a href='index.php?notifyIndividual=$viewPeople[userID]&eventID=$eventID&skillID=$viewPeople[skillID]'>
							<img src='graphics/email.png' /></a> ";
						}
						$usefulBits = $notified . " <a class='delete' href='index.php?skillremove=true&eventID=$eventID&skillID=$viewPeople[skillID]'>
						<img src='graphics/close.png' /></a> <br />";
					} else {
						
						$usefulBits = "<br />";
						}
				
					if($categoryheader == $viewPeople['category']) {
						// Do nothing, because we don't need a second category header
						
					} else {
						$categoryheader = $viewPeople['category'];
						if(isset($firsttime) && $firsttime == 1) {
							echo "<td><p><strong>" . $categoryheader . "</strong><br />";
							$i = 2;
							$firsttime = 2;
						} else 
						if($i == 1) { 
							if($identifier == "2") {
								echo "</p></td></tr><tr><td class='break'></td><td class='break'></td>
								</tr><tr><td><p><strong>" . $categoryheader . "</strong><br />";
								$identifier = "3";
								$i = 1;
							} else {
							echo "</p></td></tr><tr><td><p><strong>" . $categoryheader . "</strong><br />";
							}
							$i = 2;
						} else {
							if($identifier == "2") {
								echo "</p></td><td></td></tr><tr><td class='break'></td><td class='break'></td>
								</tr><tr><td><p><strong>" . $categoryheader . "</strong><br />";
								$i = 2;
								$identifier = "3";
							} else {
							echo "</p></td><td><p><strong>" . $categoryheader . "</strong><br />";
							$i = 1;
							}
							
						}
					}
			
					
					echo $viewPeople['name'];
					
					if($viewPeople['joinedskill'] != "") {
						echo " - <em>" . $viewPeople['joinedskill'] . "</em>";
					} else {
						// If there is no skill, then we don't need to mention this fact.
					}
					echo $usefulBits; 
					
					
			}
			echo "</p>";
			if($i == 2) { 
				echo "<td></td>";
			}
			echo "</tr>	</table>";
			?>
				</div></div>
	<?
	}
} else {
?>
<div class="elementBackground">
<h2>Welcome to <? echo $owner; ?> Rota</h2>
<p>For privacy reasons, the rota is not available publically. If you attend <? echo $owner; ?>, please login using your user details. 

If you are unsure of your user details, please email <a href="mailto:<? echo $owneremail; ?>">the office</a> to request a reminder.</p>

</div>

<? } ?>

<div id="right">
<? if(isAdmin()) {?>
		<div class="item"><a href="createEvent.php">Create a new event</a></div>
<? }
   if((isAdmin()) || ($logged_in_show_snapshot_button=='1')) {?>		
		<div class="item"><a href="snapshot.php" target="_blank">Snapshot view</a></div>
<? } 
   if(isAdmin()) {?>
		<div class="item"><a href="index.php?notifyOverview=true">Send Mail Overview</a></div>
		<? } ?>
		<? if(isLoggedIn()) {
			if($showmyevents == "") { ?>
			<div class="item"><a href="index.php?showmyevents=<? echo $_SESSION['userid']; ?>">Show only my events</a></div>
			<? } else { ?>
			<div class="item"><a href="index.php">Show all events</a></div>
			<? } ?>
		<div class="item"><a href="logout.php">Logout</a></div>
		<? } ?>
		<? if(!isLoggedIn()) { ?>
		<div class="item"><form action="login.php" method="post" >
		<fieldset>
			<label for="username">Username:</label>
			<input name="username" id="username" type="text" placeholder="Enter your username" class="login" />
			
			<label for="password">Password:</label>
			<input name="password" id="password" type="password" placeholder="Enter your password" class="login" />
			
			<input type="submit" value="Login now" />
		</fieldset>
	</form></div>
		<? } ?>
	</div>
	
<? include('includes/footer.php'); ?>
