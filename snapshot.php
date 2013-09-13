<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2011 David Bunce, Benjamin Schmitt

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
	
	//check users maximal skillgroup (maxGroup), if logged in.
		$sql = "select max(formatgroup) as max_group from cr_skills s, cr_groups g where s.groupID=g.groupID and s.userID=". $_SESSION['userid'];
		$result = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$maxGroup = $row['max_group'];
		if ($maxGroup=='') $maxGroup=1;
	} 
	else
	{	
		//if not logged in, only show skills of skillgroup 1
		$maxGroup=1;
	}
	//echo "maxGroup=".$maxGroup."<br>";
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
	$lang_locale = $rowSettings['lang_locale'];
	$time_format_short = $rowSettings['time_format_short'];
	$userTZ=$rowSettings['time_zone'];
	$google_group_calendar=$rowSettings['google_group_calendar'];
	
	if ((isAdmin()) || ($rowSettings['snapshot_reduce_skills_by_group']=='0')) {
		$maxGroup=999;  //show all skill groups, if admin or option not used
	} 
	
	if ($rowSettings['snapshot_show_two_month']=='1') {
		$whereTwoMonth = "Year(date) = Year(Now()) AND ((Month(date) = Month(Now())) OR ((Month(date) = Month(Now())+1) AND (Day(Now())>20)))";
	}else{
		//$whereTwoMonth = "1=1";
		$whereTwoMonth = "cr_events.date >= DATE(NOW())";
	}

	if ($rowSettings['group_sorting_name']=='1') {
		$group_sorting_name = "formatgroup,description";
	}else{
		$group_sorting_name = "groupID";
	}	
	
	$sql = "SELECT count(*) as colcount FROM cr_groups where formatgroup<=" . $maxGroup;
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$colCnt = $row['colcount']+2;

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
			<? if(isLoggedIn()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='logout.php'? 'class="active"' : '');?>><a  href="logout.php">Logout</a></li>
			<? }  ?>
			
		</ul>
	</div>
<div class="filtersnapshot">
	<h1>Snapshot view</h1>
		<h2>Filter events by:</h2>
		<p>
			<a class="eventTypeButton" href="snapshot.php">All</a>
		<?php
		$filter_sql = "SELECT * FROM cr_eventTypes where id in 
			(select `cr_events`.`type` FROM cr_events 
			WHERE ".$whereTwoMonth."
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
	$sql = "SELECT * FROM cr_groups where formatgroup <= " . $maxGroup . " ORDER BY " . $group_sorting_name;
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
		$preacher="";
		$leader="";
		$band="";
		echo "<tr>";
		echo "<td >";
		setlocale(LC_TIME, $lang_locale); //de_DE
		echo strftime($time_format_short,strtotime($row['sundayDate'])); // %a, <strong>%e. %b</strong>, KW%V

		//$row['sundayDate'] 
		echo "<br /><em>&nbsp;&nbsp;&nbsp;" . $row['eventType'] . "<br /><em>&nbsp;&nbsp;&nbsp;" . $row['eventLocation']. "</td><td >"; 
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(if(`firstname`='Team',`firstname`,concat(LEFT(`firstname`,1),'.')), ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) 
				AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`,  
				GROUP_CONCAT(skill) AS joinedskill
				FROM cr_skills WHERE skillID IN (SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID' and groupID in (select groupID from cr_groups where formatgroup<=" . $maxGroup . ")) 
				GROUP BY skillID ORDER BY groupID, name";
								
				for ($i=0;$i<count($categoryID);$i++)
				{
					$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
					while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
						$groupID = $viewPeople['groupID'];
						if ($groupID==$categoryID[$i])
						{
							//writing name/s into snapshot cell
							echo $viewPeople['name'];
							echo "<br />";
							//no break or continue, because there could be other viewPeople with same categoryID	
						
						
							//variable to save name for google calendar subject
							//neede if user is admin
							//only append name, if not already in variable
							$separator = ", ";
							$currentName = substr($viewPeople['category'],0,1).": ".$viewPeople['name'];
							if (($groupID == 10) && (strpos($preacher,$currentName)===false))  
								$preacher = trim($preacher. $separator . $currentName, $separator);
								
							if (($groupID == 11) && (strpos($leader,$currentName)===false))  
								$leader = trim($leader . $separator . $currentName,$separator);
								
							if (($groupID == 2) && (strpos($band,$currentName)===false))  
								$band = trim($band . $separator . $currentName,$separator);
						}
					}
					echo "</td><td>";
				}

				//create subject for google calendar
				$separator = " / ";
				$calSubject = ltrim($leader . $separator , $separator);
				$calSubject = $calSubject . ltrim($preacher . $separator , $separator);
				$calSubject = $calSubject . ltrim($band , $separator);
				$calSubject = ltrim($calSubject . " ");
				//echo $calSubject;
								
				putenv("TZ=".$userTZ);
				$eventDate = $row['sundayDate'];
				$eventDateGMT = gmdate("Ymd\THis\Z",strtotime($eventDate." ".date("T",strtotime($eventDate))));
				//echo $eventDateGMT."<br>";
				$eventDate = $row['sundayEndDate'];
				$eventDateEndGMT = gmdate("Ymd\THis\Z",strtotime($eventDate." ".date("T",strtotime($eventDate))));
				//echo $eventDateEndGMT;
				
				if (isAdmin()) {
					echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".urlencode(utf8_wrapper($calSubject."(".$row['eventType'].")"))."&dates=".$eventDateGMT."/".$eventDateEndGMT."&details=&location=".urlencode(utf8_wrapper($row['eventLocation']))."&trp=false&sprop=&sprop=name:&src=".$google_group_calendar."&ctz=".$userTZ."\" target=\"_blank\">";
				}else{	
					echo "<a href=\"http://www.google.com/calendar/event?action=TEMPLATE&text=".urlencode(utf8_wrapper($row['eventType']))."&dates=".$eventDateGMT."/".$eventDateEndGMT."&details=&location=".urlencode(utf8_wrapper($row['eventLocation']))."&trp=false&sprop=&sprop=name:&ctz=".$userTZ."\" target=\"_blank\">";
				}
				echo "<img src=\"//www.google.com/calendar/images/ext/gc_button1.gif\" border=0></a>";
				
		echo "</td></tr>\r\n";
	}
?>
</table>
</body>
</html>
