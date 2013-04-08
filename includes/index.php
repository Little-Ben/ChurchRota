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

// Method to remove  someone from the band
if($skillremove == "true") {
	removeEvent($removeWholeEvent);
	removeEventPeople($removeEventID, $removeSkillID);
}

if($notifyEveryone == "true") {
	notifyEveryone($removeEventID);
}

notifyIndividual($notifyIndividual, $removeEventID, $removeSkillID);

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
		 header('Location: index.php'); 
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
		 header('Location: index.php'); 
     	 exit;
	}
}
?>

<? $formatting = "light";
include('includes/header.php'); 

if(isLoggedIn()) { 
	if($showmyevents == "") {
	$sql = "SELECT *, 
	(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
	(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
	DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') AS sundayDate, DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') AS rehearsalDateFormatted 
	FROM cr_events ORDER BY date";
	
	} else {
	$sql = "SELECT *,
	(SELECT userID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents') AS skilluserID,
	(SELECT groupID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID) AS skillgroupID, 
	(SELECT `description` FROM cr_groups WHERE skillgroupID = `cr_groups`.`groupID`) AS `description`,
	(SELECT skill FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID) AS skill, 
	(SELECT DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID ) AS sundayDate, 
	(SELECT DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID ) as rehearsalDateFormatted, 
	(SELECT location FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsLocation, 
	(SELECT comment FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as comment, 
	(SELECT type FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsType,
	(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = cr_eventsType) AS eventType,
	(SELECT `description` FROM cr_locations WHERE cr_locations.id = cr_eventsLocation) AS eventLocation,
	(SELECT rehearsal FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID) as cr_eventsRehearsal
 	FROM cr_eventPeople 
	WHERE EXISTS (SELECT userID FROM cr_skills WHERE skillID = cr_eventPeople.skillID AND cr_skills.userID = '$showmyevents' AND cr_skills.userID)
	AND EXISTS (SELECT DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') FROM cr_events WHERE cr_events.id = cr_eventPeople.eventID)
	GROUP BY eventID
	ORDER BY sundayDate";
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
			echo $row['sundayDate'];
			echo "</a>";
			if(isAdmin()) { echo " <a href='createEvent.php?action=edit&id=$eventID'><img src='graphics/tool.png' /></a> <a href='index.php?notifyEveryone=true&eventID=$eventID'><img src='graphics/email.png' /></a> <a class='delete' href='index.php?skillremove=true&wholeEventID=$eventID'><img src='graphics/close.png' /></a>"; }?></h2>
			<div class="elementHead"><p><? if($rehearsal != "1") { ?><strong>Rehearsal:</strong> <? echo $row['rehearsalDateFormatted']; ?><br /><? } ?>
			<strong>Location:</strong> <? echo $row['eventLocation']; ?><br />
			<strong>Type:</strong> <? echo $row['eventType']; ?></p>
			<? if($row['comment'] != "") {
				echo "<div class='shaded'>";
				echo "<strong>Comments:</strong>";
				echo $row['comment'];
				echo "</div>";
				
			} ?>
			
			</div>
			<div class="elementContent">
			<table>
			<?
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) 
				AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`, 
				(SELECT `formatgroup` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `formatgroup`, 
				GROUP_CONCAT(skill) AS joinedskill
				FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID') 
				GROUP BY userID, groupID ORDER BY groupID, name";
				
				$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
				echo "<p>";
				$i = 1;
				$categoryheader = "";
				$formatgroup = 1;
				$identifier = "1";
				$firsttimme = 1;
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
						$usefulBits = " <a href='index.php?notifyIndividual=$viewPeople[userID]&eventID=$eventID&skillID=$viewPeople[skillID]'><img src='graphics/email.png' /></a> <a class='delete' href='index.php?skillremove=true&eventID=$eventID&skillID=$viewPeople[skillID]'><img src='graphics/close.png' /></a> <br />";
						} else {
						$usefulBits = "<br />";
						}
				
					if($categoryheader == $viewPeople['category']) {
						// Do nothing, because we don't need a second category header
						
					} else {
						$categoryheader = $viewPeople['category'];
						if($firsttime == 1) {
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
			if(isAdmin() && $showmyevents == "") {
			$sqladdMembers = "SELECT *,
				(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) AS `name`
				FROM cr_skills ORDER BY groupID";
				$resultaddMembers = mysql_query($sqladdMembers) or die(mysql_error());
				
			?>
			
			<form id="viewEvents<?php echo $row['id']; ?>" action="index.php?event=<? echo  $row['id']; ?>" method="post">
				<fieldset>
					<label for="name">Add individuals:</label>
					<select name="name" id="name">
						<option></option>
						<?
			$sqladdMembers = "SELECT *,
(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) AS `name`, (SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`
FROM cr_skills  ORDER BY groupID, name";
				$resultaddMembers = mysql_query($sqladdMembers) or die(mysql_error());
			?>
						<?php while($addMember = mysql_fetch_array($resultaddMembers, MYSQL_ASSOC)) {
							$id = $addMember['groupID'];
							$name = $addMember['name'];
							
							if($addMember['skill'] != "") {
								$name = $name . " - " . $addMember['skill'];
							} else {
								// If there is no skill, then we don't need to mention this fact.
							}
							echo "<option value='" . $addMember['skillID'] . "'>" . $addMember['category'] . ": " .  $name . "</option>";
						} ?>
					</select>
					
					<label for="band">Add band:</label>
					<select name="band" id="band">
						<option></option>
					<?
					$sqlAddBands = "SELECT * FROM cr_bands";
					$resultaddBands = mysql_query($sqlAddBands) or die(mysql_error());
			
					 while($addBand = mysql_fetch_array($resultaddBands, MYSQL_ASSOC)) {
							echo "<option value='" . $addBand['bandID'] . "'>" . $addBand['bandLeader'] . "</option>";
							
						}
					
					?>
					</select>
					<br />
					<input type="submit" value="Add member" />
				</fieldset>		
			</form>
			<? 
			}
			 ?>
				</div></div>
	<?
	}
} else {
?>
<div class="elementBackground">
<h2>Welcome to the Kingdom Vineyard Rota</h2>
<p>For privacy reasons, the rota is not available publically. If you attend the Kingdom Vineyard, please login using your user details. 

If you are unsure of your user details, please email <a href="mailto:info@thekingdomvineyard.com">the office</a> to request a reminder.</p>

</div>

<? } ?>

<div id="right">
<? if(isAdmin()) {?>
		<div class="item"><a href="createEvent.php">Create a new event</a></div>
		<div class="item"><a href="viewBands.php">View all bands</a></div>
		<div class="item"><a href="viewBands.php?action=newBand">Add a new band</a></div>
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
