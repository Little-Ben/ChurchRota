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
	} else {
	header('Location: login.php');
	exit;
} 
if (!isAdmin()) {
	header('Location: error.php?no=100&page='.basename($_SERVER['SCRIPT_FILENAME']));
	exit;
}

$skillID = $_GET['id'];

	$sql = "SELECT *
	FROM cr_groups
	WHERE groupID = '$skillID'";
	$result = mysql_query($sql) or die(mysql_error()); 
	
	while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {
		$id = $row['id'];
	 	$skilldescription = $row['description'];
		
	}


// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$postdata = $_POST['rehearsaldate'];
	if(empty($postdata)) {
		$sql = "DELETE FROM cr_skills WHERE groupID = '$skillID'";
		mysql_query($sql) or die(mysql_error());
	} else {
		$sqlPeople = "SELECT *
		FROM cr_skills
		WHERE groupID = '$skillID'";

		$resultPeople = mysql_query($sqlPeople) or die('mysql_error()');
		$result = mysql_num_rows($resultPeople);

		while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
			$membersArray[] = $viewPeople['userID'];
		}
		$addarray = array_diff($postdata, $membersArray);
		while (list ($key, $userid) = each ($addarray)) {
			addPeopleSkill($skillID, $userid);
		}
		
		// Compare the other way to notice what's disappeared	
		$deletearray = array_diff($membersArray, $postdata);
			
		while (list ($key2, $userid) = each ($deletearray)) {
			removeEventMemberSkill($skillID, $userid);
		}
	}

	// header ( "Location: index.php#section" . $eventID);
}
$formatting = "true";

include('includes/header.php');
?>


<div class="elementBackground">
<h2>Edit skill: <?php echo $skilldescription; ?></h2>

	<form action="editsingleskill.php?id=<? if(isset($skillID)) echo $skillID; ?>" method="post" id="createEvent">
		
		<fieldset>
			<?
				$sqlPeople = "SELECT *,
				(SELECT groupID FROM cr_skills WHERE cr_skills.userid = cr_users.id AND cr_skills.groupID = '$skillID' LIMIT 1) as existing
				FROM cr_users
				ORDER BY firstName";
				
				$resultPeople = mysql_query($sqlPeople) or die(mysql_error());
				$result = mysql_num_rows($resultPeople);
				$half = $result / 2;
				$i = 0;
				$position = 1;
			?><div>
			<div class="checkboxlistleft">
			<?php while($viewPeople = mysql_fetch_array($resultPeople, MYSQL_ASSOC)) {
				?>
					<?php if($half == $i): ?>
						</div>
						<div class="checkboxlistright">
					<?php endif; ?>
						<div class='checkboxitem'>
							<label class='styled' for='rehearsaldate[<?php echo $viewPeople['id']; ?>]'><?php echo $viewPeople['firstName']; ?> <?php echo $viewPeople['lastName']; ?>
							<input class='styled' 
							<?php 
							if($viewPeople['existing'] != '') {
								echo 'checked="checked"';
							} ?>
							type='checkbox' id='rehearsaldate[<?php echo $viewPeople['id']; ?>]'	name='rehearsaldate[]' value='<?php echo $viewPeople['id']; ?>' /></div>

						<?php
						$i = $i + 1;
			}
			?>
		</div>
			</div>
					<input type="submit" value="Save" />
				</fieldset>	
			
	</form>
</div>


<? include('includes/footer.php'); ?>