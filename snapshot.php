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
		$sql = "select max(formatgroup) as max_group from cr_skills s, cr_groups g where s.groupID=g.groupID and s.userID=". $_SESSION['userid'];
		$result = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$maxGroup = $row[max_group];
	} 
	else
	{	
		$maxGroup=1;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Snapshot view</title>
	<link rel="stylesheet" href="includes/style.css" type="text/css" />
</head>
<?php
	$sqlSettings = "SELECT * FROM cr_settings";
	$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
	$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
	$lang_locale = $rowSettings[lang_locale];
	$time_format_short = $rowSettings[time_format_short];
	//$userTZ="Europe/Berlin";
	$userTZ=$rowSettings[time_zone];
	$google_group_calendar=$rowSettings[google_group_calendar];
	
	
	if ($rowSettings[snapshot_show_two_month]=='1') {
		$whereTwoMonth = "Year(date) = Year(Now()) AND ((Month(date) = Month(Now())) OR ((Month(date) = Month(Now())+1) AND (Day(Now())>20)))";
	}else{
		//$whereTwoMonth = "1=1";
		$whereTwoMonth = "cr_events.date >= DATE(NOW())";
	}
	
	if ((isAdmin()) || ($rowSettings[snapshot_reduce_skills_by_group]=='0')) {
		$maxGroup=999;  //show all groups, backward compatibility
	} 

	$sql = "SELECT count(*) as Anzahl FROM cr_groups where formatgroup<=" . $maxGroup;
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$colCnt = $row[Anzahl]+2;

	if (isset($_GET['column_width'])) {
		$colWidth=$_GET['column_width'];
	}
	else{
		$colWidth=0; //full-size table, backward compatibility
	}
	
?>
<body>
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
<table class="snapshot" width='<? echo (($colCnt)*$colWidth); ?>'>
<tr>
	<td ><strong>Event</strong></td>
	<?
	$sql = "SELECT * FROM cr_groups where formatgroup<=" . $maxGroup . " ORDER BY groupID";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo "<td><strong>";
		echo $row['description'];
		$categoryID[] = $row['groupID'];
		echo "</strong></td>";
	}
	echo "<td><strong>Export</strong></td>";
	

	if( $filter == "") {
		
		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') AS sundayDate, DATE_FORMAT(rehearsalDate,'%m/%d/%Y %H:%i:%S') AS rehearsalDateFormatted ,
			DATE_FORMAT(DATE_ADD(date, INTERVAL 90 MINUTE),'%m/%d/%Y %H:%i:%S') AS sundayEndDate	
			FROM cr_events 
			WHERE " . $whereTwoMonth . "
			AND cr_events.deleted = 0
			ORDER BY date";
	} else if($filter != "") {

		$sql = "SELECT *, 
			(SELECT `description` FROM cr_eventTypes WHERE cr_eventTypes.id = `cr_events`.`type`) AS eventType,
			(SELECT `description` FROM cr_locations WHERE cr_locations.id = `cr_events`.`location`) AS eventLocation,
			DATE_FORMAT(date,'%m/%d/%Y %H:%i:%S') AS sundayDate, DATE_FORMAT(rehearsalDate,'%m/%d/%Y %H:%i:%S') AS rehearsalDateFormatted,
			DATE_FORMAT(DATE_ADD(date, INTERVAL 90 MINUTE),'%m/%d/%Y %H:%i:%S') AS sundayEndDate	 
			FROM cr_events 
			WHERE `cr_events`.`type` = '$filter'
			AND " . $whereTwoMonth . "
			AND cr_events.deleted = 0
			ORDER BY date";
	} 
	$result = mysql_query($sql) or die(mysql_error());
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$eventID = $row['id'];
		$preacher="-";
		$leader="-";
		$band="-";
		echo "<tr>";
		echo "<td >";
		setlocale(LC_TIME, $lang_locale); //de_DE
		echo strftime($time_format_short,strtotime($row['sundayDate'])); // %a, <strong>%e. %b</strong>, KW%V

		//$row['sundayDate'] 
		echo "<br /><em>&nbsp;&nbsp;&nbsp;" . $row['eventType'] . "<br /><em>&nbsp;&nbsp;&nbsp;" . $row['eventLocation']. "</td><td >"; 
		$i = 0;
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(if(`firstname`='Team',`firstname`,concat(LEFT(`firstname`,1),'.')), ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) 
				AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`,  
				GROUP_CONCAT(skill) AS joinedskill
				FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID' and groupID in (select groupID from cr_groups where formatgroup<=" . $maxGroup . ")) 
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

					if ($groupID == 10)  
						$preacher=substr($viewPeople['category'],0,1).": ".$viewPeople['name'];
					if ($groupID == 11)  
						$leader=substr($viewPeople['category'],0,1).": ".$viewPeople['name'];
					if ($groupID == 2)  
						$band=substr($viewPeople['category'],0,1).": ".$viewPeople['name'];
				} 	
				
				for ($i ; $i < (count($categoryID)-1); $i++) {
					echo "</td><td>";
				}
				
				echo "</td><td>";
				
				putenv("TZ=".$userTZ);
				$eventDate = $row['sundayDate'];
				$eventDateGMT = gmdate("Ymd\THis\Z",strtotime($eventDate." ".date("T",strtotime($eventDate))));
				//echo $eventDateGMT."<br>";
				$eventDate = $row['sundayEndDate'];
				$eventDateEndGMT = gmdate("Ymd\THis\Z",strtotime($eventDate." ".date("T",strtotime($eventDate))));
				//echo $eventDateEndGMT;
				
				
				////echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=VP%3A%20".$leader."%20/%20P%3A%20".$preacher."%20/%20M%3A%20".$band."%20(".$row['eventType'].")&dates=".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."/".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."&details=&location=".$row['eventLocation']."&trp=false&sprop=&sprop=name:&ctz=Europe/Berlin\" target=\"_blank\">";
				//echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".$row['eventType']."&dates=".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."/".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."&details=&location=".$row['eventLocation']."&trp=false&sprop=&sprop=name:&ctz=Europe/Berlin\" target=\"_blank\">";
				//&src=5vokrij4fv8k011dcmt38rt7ik@group.calendar.google.com
				if (isAdmin()) {
					//echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=VP%3A%20".$leader."%20/%20P%3A%20".$preacher."%20/%20M%3A%20".$band."%20(".$row['eventType'].")&dates=".strftime("%Y%m%dT%H%M%SZ",strtotime($row['gmtDate']))."/".strftime("%Y%m%dT%H%M%SZ",strtotime($row['gmtEndDate']))."&details=&location=".$row['eventLocation']."&trp=false&sprop=&sprop=name:&src=5vokrij4fv8k011dcmt38rt7ik@group.calendar.google.com&ctz=Europe/Berlin\" target=\"_blank\">";
					echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".urlencode(utf8_wrapper($leader." / ".$preacher." / ".$band." (".$row['eventType'].")"))."&dates=".$eventDateGMT."/".$eventDateEndGMT."&details=&location=".urlencode(utf8_wrapper($row['eventLocation']))."&trp=false&sprop=&sprop=name:&src=".$google_group_calendar."&ctz=".$userTZ."\" target=\"_blank\">";
				}else{	
					echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".urlencode(utf8_wrapper($row['eventType']))."&dates=".$eventDateGMT."/".$eventDateEndGMT."&details=&location=".urlencode(utf8_wrapper($row['eventLocation']))."&trp=false&sprop=&sprop=name:&ctz=".$userTZ."\" target=\"_blank\">";
				}
					//echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=VP%3A%20".$leader."%20/%20P%3A%20".$preacher."%20/%20M%3A%20".$band."%20(".$row['eventType'].")&dates=".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."/".strftime("%Y%m%dT%H%M%SZ",strtotime($row['sundayDate']))."&details=&location=".$row['eventLocation']."&trp=false&sprop=&sprop=name:&ctz=Europe/Berlin\" target=\"_blank\">";
				
				//echo "<img src=\"//www.google.com/calendar/images/ext/gc_button6.gif\" border=0></a>";
				//echo "GCal</a>";
				echo "<img src=\"//www.google.com/calendar/images/ext/gc_button1.gif\" border=0></a>";
				
		echo "</td></tr>\r\n";
	}
?>
</table>
</body>
</html>
