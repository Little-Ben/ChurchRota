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

$filter = $_GET['filter'];

// Start the session. This checks whether someone is logged in and if not redirects them
session_start();
 
if (isset($_SESSION['is_logged_in']) || $_SESSION['db_is_logged_in'] == true) {
	// Just continue the code
	} 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Snapshot view</title>
	<link rel="stylesheet" href="includes/style.css" type="text/css" />
</head>
<div id="header">
		<a href="index.php" id="logo"><img src="graphics/logo.jpg" alt="Church Rota Logo" width="263" height="48" /></a>
		<ul>
			<? if(isLoggedIn()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='index.php'? 'class="active"' : '');?>><a  href="index.php">Home</a></li>
			
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='resources.php'? 'class="active"' : '');?> ><a href="resources.php">Resources</a></li>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='discussion.php'? 'class="active"' : '');?>
			<? if(!isAdmin()) { ?><li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='addUser.php'? 'class="active"' : '');?>><a href="addUser.php?action=edit&id=<? echo $_SESSION['userid']; ?>">My account</a></li><? } ?>
			<? }
			if(isAdmin()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='viewUsers.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='addUser.php'? 'class="active"' : ''); ?> ><a href="viewUsers.php">Users</a></li>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='settings.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='editeventtype.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='editSkills.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='locations.php'? 'class="active"' : '');?>><a  href="settings.php">Settings</a></li>
			<? }  ?>
		</ul>
	</div>
<div class="filtersnapshot">
	<h1>Snapshot view</h1>
		<h2>Filter events by:</h2>
		<p>
			<a class="eventTypeButton" href="snapshot.php">All</a>
		<?php
		$filter_sql = "SELECT * FROM cr_eventTypes ORDER BY id";
		$result = mysql_query($filter_sql) or die(mysql_error());
	
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			?>
			<a class="eventTypeButton
				<?php
					if($filter == $row['id']) {
						echo "activefilter"; 
					}
				?>" href="snapshot.php?filter=<?php echo $row['id']; ?>"><?php echo $row['description']; ?></a>
			<?php
		} 
		?>
	</p>
	</div>
<table class="snapshot">
<tr>
	<td ><strong>Date</strong></td>
	<?
	$sql = "SELECT * FROM cr_groups ORDER BY groupID";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo "<td><strong>";
		echo $row['description'];
		$categoryID[] = $row['groupID'];
		echo "</strong></td>";
	}
	

	if( $filter == "") {
		
		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') AS sundayDate, DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') AS rehearsalDateFormatted 
			FROM cr_events 
			WHERE cr_events.date >= DATE(NOW())
			AND cr_events.deleted = 0
			ORDER BY date";
	} else if($filter != "") {

		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%W, %M %e @ %h:%i %p') AS sundayDate, DATE_FORMAT(rehearsalDate,'%W, %M %e @ %h:%i %p') AS rehearsalDateFormatted 
			FROM cr_events 
			WHERE `cr_events`.`type` = '$filter'
			AND cr_events.date >= DATE(NOW())
			AND cr_events.deleted = 0
			ORDER BY date";
	} 
	$result = mysql_query($sql) or die(mysql_error());
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$eventID = $row['id'];
		echo "<tr>";
		echo "<td >" . $row['sundayDate'] . "<br /><em>" . $row['eventType'] . "</td><td >"; 
		$i = 0;
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) 
				AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`,  
				GROUP_CONCAT(skill) AS joinedskill
				FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID') 
				GROUP BY skillID ORDER BY groupID, name";
				
				$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
				
				while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
					$place = $categoryID[$i];
					$groupID = $viewPeople['groupID'];

					if($groupID == $place) { 
						echo $viewPeople['name'];
						echo "<br />";
					} else { 
						$i = $i + 1;
						$place = $categoryID[$i];
						echo "</td><td>";
	
						if($groupID == $place) { 
							echo $viewPeople['name'];
							echo "<br />";
						} else {
							while ($groupID != $place) {
								$i = $i + 1;
								$place = $categoryID[$i];
								echo "</td><td>";
							}
							echo $viewPeople['name'] . "<br />";
						}
					}
					
	
				}
		echo "</tr>";
	}
?>
</table>
<body>
</body>
</html>