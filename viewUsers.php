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

// Handle details from the header 
$userEdID = $_GET['userID'];
$skillID = $_GET['skillID'];

// Method to remove a skill from someone
removeSkill($skillID);
$formatting = "light";
$hidefirst = true;
include ('includes/header.php');
?>

<?
	$sql = "SELECT * FROM cr_users ORDER BY firstName";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$id = $row['id'];
		?>
		
		<div class="elementBackground <? if(($row['isAdmin'] == "1") || ($row['isBandAdmin'] == "1")) { echo 'highlight'; }?>">
			<h2><div class="elementHead arrowwaiting"><a name="section<?php echo $row['id']; ?>"><? echo $row['firstName'] . " " . $row['lastName']; ?></a></div>
			<div class="elementContent"><p><strong>Email address:</strong> <a href="mailto:<? echo $row['email']; ?>"><? echo $row['email']; ?></a><br />
			<strong>Mobile:</strong> <? echo $row['mobile']; ?></p>
			<p><strong>Skills:</strong><br />
				<?
					$userID = $row['id'];
					$sqlskills = "SELECT * FROM cr_skills WHERE cr_skills.userID = '$userID'";
					$resultskills = mysql_query($sqlskills) or die(mysql_error());
					
					while($rowskills = mysql_fetch_array($resultskills, MYSQL_ASSOC)) {
						$groupID = $rowskills['groupID'];
						$skillID = $rowskills['skillID'];
						
						$query_r = "SELECT * FROM cr_groups WHERE groupID = '$groupID' ";
						$groups = mysql_query($query_r) or die("SQL error: ".mysql_error());
						
						while($row_r = mysql_fetch_array($groups, MYSQL_ASSOC)) {
							echo $row_r['description'];
						}
						if(isAdmin()) { 
						echo "<em> " . $rowskills['skill'] . "</em>" . " <a href='viewUsers.php?skillremove=true&skillID=$skillID'><img src='graphics/close.png' /></a><br />";
						} else { 
						echo "<br />";
						}
						

					}
					
					echo "</p>";	
					    if(isAdmin()) { 
						echo "<p><strong>User actions:</strong><br />";
							echo '<a href="addUser.php?action=edit&id=' . $id .'"><img src="graphics/tool.png" /></a> ' .
							'<a href="index.php?showmyevents=' . $id . '"><img src="graphics/zoom.png" /></a> ';
							?>
							<a ref='#' data-reveal-id='deleteModal<?php echo $id; ?>' ><img src="graphics/close.png" /></a>
							<br>
							<a href="addUser.php?action=reset&id=<?php echo $id; ?>">Reset password</a>
							</p>

							
							<div id="deleteModal<?php echo $id; ?>" class="reveal-modal">
     							<h1>Really delete user?</h1>
								<p>Are you sure you really want to delete <? echo $row['firstName'] . " " . $row['lastName']; ?> as a user? There is no way of undoing this action.</p>
								<p><a class="button" href="addUser.php?userremove=true&id=<?php echo $id; ?>">Sure, delete the user</a></p>
     							<a class="close-reveal-modal">&#215;</a>
							</div>
							<?
						}
			echo "<div class='isAdmin'>";
			echo "<p><strong>Permissions:</strong><br />";						
			if($row['isAdmin'] == '1') { echo "Administrator<br />"; } 
			if($row['isBandAdmin'] == '1') { echo "Band Administrator<br />"; } 
			if($row['isOverviewRecipient'] == '1') { echo "Overview Recipient"; } 
			echo "</p></div>";?>
		</div>		</div>
	<?
	}
	
	if(isAdmin()) {
?>

<div id="right">
		<div class="item"><a href="addUser.php">Add a new user</a></div>
		
	</div>
	<? } ?>
<? include('includes/footer.php'); ?>
