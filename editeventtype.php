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
$eventtypeID = $_GET["eventtypeID"];
$eventtyperemove = $_GET['eventtyperemove'];
$method = $_GET['method'];

if ($eventtyperemove == "true") {
 removeEventType($eventtypeID);
 }

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if($method == 'newtype') {
	
	$neweventtype = $_POST['neweventtype'];
	$neweventtype = strip_tags($neweventtype);
	
	$rehearsal = $_POST['rehearsal'];
	$rehearsal = strip_tags($rehearsal);
	
	$sql = ("INSERT INTO cr_eventTypes (description, rehearsal) VALUES ('$neweventtype', '$rehearsal')");
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
	} else {
	// Otherwise we are dealing with edits, not new stuff 
		// Handle renaming of the titles
		$formindex = $_POST['formindex'];
		$description = $_POST['description'];
		 
		$formArray = array_combine($formindex, $description);
		
		
		while (list ($key, $valadd) = each ($formArray)) {
				updateEventType($key, $valadd);
			}
	
	}
		
	// After we have inserted the data, we want to head back to the main users page
	 header('Location: editeventtype.php'); // Move to the home page of the admin section
      exit;
}
include('includes/header.php');
?>

<div class="elementBackground">
		<h2>Edit event types</h2>
		<p>
        <form action="editeventtype.php" method="post">
        <fieldset>
        
		<? $sql = "SELECT * FROM cr_eventTypes ORDER BY description";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
		echo '<input type="hidden" name="formindex[]" value="' . $row['id'] . '" />';
		echo "<input name='description[]' value='" . $row['description'] . "' />";
		
		echo "<a href='editeventtype.php?eventtyperemove=true&eventtypeID=" . $row['id'] . "'><img src='graphics/close.png' /></a><br />"; 
	 } ?>
     </fieldset>
     <input type="submit" value="Update event types" />
	 </form></p>
		
	 <h2>Add a new event type:</h2>
		<form action="editeventtype.php?method=newtype" method="post" id="addSkill">
		<fieldset>
		<label for="neweventtype">New event type:</label>
		<input id="neweventtype" name="neweventtype" type="text" placeholder="Enter event type" />
		
			<label for="rehearsal">This event type has a rehearsal:</label>
			<input name="rehearsal" id="rehearsal" type="checkbox" value="1"  />
			
		<input type="submit" value="Add new event type" />

		
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