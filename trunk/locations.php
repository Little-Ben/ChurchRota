<?php
// Include files, including the database connection
include('includes/dbConfig.php');
include('includes/functions.php');

// Start the session. This checks whether someone is logged in and if not redirects them
session_start();
 
if (isset($_SESSION['is_logged_in']) || $_SESSION['db_is_logged_in'] == true) {
	// Just continue the code
	} else {
	header('Location: login.php');
}

// Get the query string
$locationID = $_GET["locationID"];
$editableaction = $_POST['editableaction'];
$locationremove = $_GET['locationremove'];

if ($locationremove == "true") {
 removelocation($locationID);
 }

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if($editableaction == "edit") { 
		$editid = $_POST['id'];
		$type = $_POST['type'];
		$description = $_POST['value'];
		$editid = str_replace("title", "", $editid);
		if ($type == "title") { 
			$sql = "UPDATE cr_locations SET description = '$description' WHERE id = '$editid'";
		} 
		if (!mysql_query($sql))
 	 		{
  				die('Error: ' . mysql_error());
  			}
		
	} else {
	
	$newlocation = $_POST['newlocation'];
	$newlocation = strip_tags($newlocation);
	
	$rehearsal = $_POST['rehearsal'];
	$rehearsal = strip_tags($rehearsal);
	
	$sql = ("INSERT INTO cr_locations (description) VALUES ('$newlocation')");
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	// After we have inserted the data, we want to head back to the main users page
	 header('Location: locations.php'); // Move to the home page of the admin section
      exit;
	  }
}
$formatting = "true";
$sendurl = "locations.php";
include('includes/header.php');
?>

<div class="elementBackground">
		<h2>Edit locations</h2>
		<p>
		<? $sql = "SELECT * FROM cr_locations ORDER BY description";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
		$locationID = $row['id'];
		echo "<span id='" . $locationID . "' class='edit'>" . $row['description'] . "</span> ";
		echo " <a href='locations.php?locationremove=true&locationID=" . $locationID . "'><img src='graphics/close.png' /></a><br />"; 
	 } ?>
	 <h2>Add a new location:</h2>
		<form action="#" method="post" id="addSkill">
		<fieldset>
		<label for="newlocation">New location type:</label>
		<input id="newlocation" name="newlocation" type="text" placeholder="Enter event type" />
		
			
			
<input type="submit" value="Add new location" />

		
		</fieldset>
	</form>	
</div>
<? 
if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="settings.php">Back to settings</a></div>
		<div class="item"><a href="createEvent.php">Create a new event</a></div>
</div>
<? } ?>
<? include('includes/footer.php'); ?>