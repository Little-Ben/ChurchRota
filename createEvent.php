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

$action = $_GET['action'];
$eventID = $_GET['id'];
$sessionUserID = $_SESSION['userid'];
$actionName='Create';
$userisBandAdmin = isBandAdmin($sessionUserID);


if($action == "edit") {
	$sql = "SELECT *, 
	(SELECT description FROM cr_locations WHERE cr_locations.id = cr_events.location) AS locationname, 
	(SELECT description FROM cr_eventTypes WHERE cr_eventTypes.id = cr_events.type) AS typename
	FROM cr_events WHERE id = '$eventID'";
	$result = mysql_query($sql) or die(mysql_error()); 
	
	while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {
		$id = $row['id'];
	 	$date = $row['date'];
		$rehearsalDate = $row['rehearsalDate'];
		$type = $row['type'];
		$typename = $row['typename'];
		$location = $row['location'];
		$locationname = $row['locationname'];
		$formaction = "?action=edit&id=" . $id;
		$norehearsal = $row['rehearsal'];
		$comment = $row['comment'];
		
	}
	
	$actionName='Edit';
} 
 
if (isset($_SESSION['is_logged_in']) || $_SESSION['db_is_logged_in'] == true) {
	// Just continue the code
	} else {
	header('Location: login.php');
}
	
// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$date = $_POST['date'];
	$rehearsaldate = $_POST['rehearsaldate'];
	$rehearsaldateactual = $_POST['rehearsaldateactual'];
	$location = $_POST['location'];
	$type = $_POST['type'];
	$norehearsal = $_POST['norehearsal'];
	$editskillID = $_POST['name'];
	$comment = $_POST['comment'];
	$comment = trim($comment);
	$comment = mysql_real_escape_string($comment);
	$editbandID = $_POST['band'];

	
	if($action == "edit") {
		$sql = "UPDATE cr_events SET date = '$date', rehearsalDate = '$rehearsaldateactual', location = '$location', 
		rehearsal = '$norehearsal', type = '$type', comment = '$comment' WHERE id = '$id'";
		mysql_query($sql) or die(mysql_error());
	} else {
		$sql = "INSERT INTO cr_events (date, rehearsalDate, type, location, rehearsal, comment)
		VALUES ('$date', '$rehearsaldateactual', '$type', '$location', '$norehearsal', '$comment')";
		mysql_query($sql) or die(mysql_error());
		$id = mysql_insert_id();
		$eventID = mysql_insert_id();
	}
	
	// Band population
if(isset($rehearsaldate)) { 
	if($action != "edit") { 
		//  This is if we are running through the first time, we just need to populate
		foreach ($rehearsaldate as $key => $rehearsaldatevalue) {
			addPeople($eventID, $rehearsaldatevalue);
		}
	} else {
		// If this is not the case, we need to compare two arrays. The first array is the people already there. The second array is submitted people
		
		// First array:
		$sql = "SELECT skillID FROM cr_eventPeople WHERE eventID = '$eventID'";
		if ($userisBandAdmin) $sql = $sql . " and skillID in (select skillID from cr_skills where groupid=2)";
		
		$result = mysql_query($sql) or die(mysql_error());
		
		while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {
			// We're going to put it all in a nice array called membersArray
			$membersArray[] = $row['skillID'];
				}
		
		// Compare the event one way to notice what's new	
		if(!empty($membersArray)) {
			// Don't add empty things. Delete all members if there have been none sent back.
	
				$addarray = array_diff($rehearsaldate, $membersArray);
				while (list ($key, $valadd) = each ($addarray)) {
					addPeople($eventID, $valadd);
				}
					// Compare the other way to notice what's disappeared	
				$deletearray = array_diff($membersArray, $rehearsaldate);
					
				while (list ($key2, $valdelete) = each ($deletearray)) {
						removeEventMember($eventID, $valdelete);
				}
			
		} else { 
			// Don't add empty things.
			if(!empty($rehearsaldate)) {
				$addarray = $rehearsaldate;
				while (list ($key, $valadd) = each ($addarray)) {
					addPeople($eventID, $valadd);
				}
			}
		} 
			
		
		
		
		
	
	} 
	// Otherwise there was nothing in rehearsal date and we should just delete all people from the event
	} else {
		$delete_all_sql = "DELETE FROM cr_eventPeople WHERE eventID = '$eventID'";
		mysql_query($delete_all_sql) or die(mysql_error());
	}
	header ( "Location: index.php#section" . $eventID);
}
$formatting = "true";

if ($userisBandAdmin)
	$hidden=true;
else
	$hidden=false;

include('includes/header.php');
?>


<div class="elementBackground">
<h2><?php echo $actionName ?> an Event</h2>

	<form action="createEvent.php<? if(isset($formaction)) echo $formaction; ?>" method="post" id="createEvent">
		<fieldset>
			<label for="date">Date: <? if(isset($date)&&($hidden)) echo $date; ?></label>
			<input name="date" id="date" type="<? if($hidden) {echo "hidden";} else {echo "text";}?>" value="<? if(isset($date)) echo $date; ?>" placeholder="Enter event date" />
			
			<label for="norehearsal">Have this event without a rehearsal:</label>
			<input name="norehearsal" id="norehearsal" type="checkbox" value="1"  <? if(isset($norehearsal) && $norehearsal != 0) { echo 'checked="checked"'; }  else {   } ?>  />
			
			<label for="rehearsaldateactual">Rehearsal Date:</label>
			<input name="rehearsaldateactual" id="rehearsaldateactual" value="<? if(isset($rehearsalDate)) echo $rehearsalDate; ?>" type="text" placeholder="Enter rehearsal date" />
			
			<label for="location">Location: <? if(isset($locationname)&&($hidden)) echo $locationname; ?></label>
			<select name="location" id="location" <? if($hidden) echo "hidden"; ?>>
				<option value="<? if(isset($location)) echo $location; ?>"><? if(isset($locationname)) echo $locationname; ?></option>
				<? 
				$sql = "SELECT * FROM cr_locations";
				$result = mysql_query($sql) or die(mysql_error());
	
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					if(isset($location) && $row['id'] == $location) { }
					else { echo "<option value='" . $row['id'] . "'>" . $row['description'] . "</option>"; }
				} ?>
			</select>
			
			<label for="type">Type: <? if(isset($typename)&&($hidden)) echo $typename; ?></label>
			<select name="type" id="type" <? if($hidden) echo "hidden"; ?>>
				<option value="<? if(isset($type)) echo $type; ?>"><? if(isset($typename)) echo $typename; ?></option>
				<? 
				$sql = "SELECT * FROM cr_eventTypes";
				$result = mysql_query($sql) or die(mysql_error());
	
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					if(isset($type) && $row['id'] == $type) { }
					else { 	echo "<option value='" . $row['id'] . "'>" . $row['description'] . "</option>"; }
				} ?>
			</select>
			
			<label for="comment">Comments: <? if(isset($comment)&&($hidden)) echo $comment; ?></label>
			<textarea name="comment" class="mceNoEditor" <? if($hidden) echo "hidden"; ?>><? if(isset($comment)) echo $comment; ?></textarea>
			
		</fieldset>
		<input type="submit" value="<?php echo $actionName ?> event" />
		
		<fieldset>
			<legend><h2>Add people to the event:</h2></legend>
			<?
				$sqlPeople = "SELECT *,
				(SELECT CONCAT(`firstname`, ' ', `lastname`) FROM cr_users WHERE `cr_users`.id = `cr_skills`.`userID` ORDER BY `cr_users`.firstname) AS `name`, 
				(SELECT `description` FROM cr_groups WHERE `cr_skills`.`groupID` = `cr_groups`.`groupID`) AS `category`, 
				(SELECT skillID FROM cr_eventPeople WHERE `cr_eventPeople`.`eventID` = '$eventID' AND `cr_eventPeople`.`skillID` = `cr_skills`.`skillID`
				LIMIT 1) AS `inEvent` 
				FROM cr_skills ORDER BY groupID, name";
				
				$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
				$i = 1;
				$position = 1;
			?><div>
			<?php while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
					$identifier = $viewPeople['groupID'];
					
					if(isAdmin()) {
						$usefulBits = " <a href='index.php?notifyIndividual=$viewPeople[userID]&eventID=$eventID&skillID=$viewPeople[skillID]'><img src='graphics/email.png' /></a> <a href='index.php?skillremove=true&eventID=$eventID&skillID=$viewPeople[skillID]'><img src='graphics/close.png' /></a> <br />";
						} else {
						$usefulBits = "<br />";
						}
					
					if(isset($categoryheader) && $categoryheader == $viewPeople['category']) {
						// Do nothing, because we don't need a second category header
						
					} else {
						$categoryheader = $viewPeople['category'];
						if ($position == 2) {
							echo "</div>";
							$position = 1;
						}
						echo '</div><legend>' . $categoryheader . '</legend><div class="checkboxlist">';
					}
					
					if($viewPeople['inEvent'] != '') 
					{ $checked =  'checked="checked" '; }  else { $checked = "";  }
					
					if ($userisBandAdmin) {
						if($viewPeople['groupID'] == '2') 
							{ $disabled =  ""; }  
						else 
							{ $disabled = 'disabled';  }
					} else 
						{ $disabled =  ""; }
					
					if($viewPeople['skill'] != "") {
						$skill = " - <em>" . $viewPeople['skill'] . "</em>";
					} else {
						$skill = "";
					}
					if($position == 2) {
						echo "<div class='checkboxitem right'><label class='styled' for='rehearsaldate[" .  $viewPeople['skillID'] . "]'>" .
						$viewPeople['name'] .  $skill . "</em></label><input class='styled' " . $disabled . " " . $checked . "type='checkbox' id='rehearsaldate[" . 
						$viewPeople['skillID'] . "]'	name='rehearsaldate[]' value='" .  
						$viewPeople['skillID'] . "' /></div></div>\r\n";
						
						$position = 1;
					} else {
						if($i == "1") { 
							$class = ""; 
							$i = "0"; 
						} else { 
						$class = ""; 
							$i = "1";
						}
						
						echo "<div class='row" . $class . "'>
						<div class='checkboxitem'><label class='styled' for='rehearsaldate[" .  $viewPeople['skillID'] . "]'>" .
						$viewPeople['name'] .  $skill . "</em></label><input class='styled' " . $disabled . " " . $checked . "type='checkbox' id='rehearsaldate[" . 
						$viewPeople['skillID'] . "]'	name='rehearsaldate[]' value='" .  
						$viewPeople['skillID'] . "' /></div>\r\n";
						
						$position = 2;
					}
					
					
					
					
		
					
					
					
			}
			if ($position == 2) {
							echo "</div>";
							$position = 1;
						}
			?>
			</div>
					<input type="submit" value="<?php echo $actionName ?> event" />
				</fieldset>	
			
	</form>
</div>


<? include('includes/footer.php'); ?>