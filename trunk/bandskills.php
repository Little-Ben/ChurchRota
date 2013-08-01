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
	
		$neweventtype = $_POST['description'];
		$neweventtype = strip_tags($neweventtype);
	
		$sql = ("INSERT INTO cr_instruments (name) VALUES ('$neweventtype')");
	
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
				updateInstruments($key, $valadd);
			}
	
	}
		
	// After we have inserted the data, we want to head back to the main users page
	 header('Location: bandskills.php'); // Move to the home page of the admin section
      exit;
}
include('includes/header.php');
?>

<div class="elementBackground">
		<h2>Edit band skills</h2>
		<p>
        <form action="bandskills.php" method="post">
        <fieldset>
        
		<? $sql = "SELECT * FROM cr_instruments ORDER BY name";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
		echo '<input type="hidden" name="formindex[]" value="' . $row['id'] . '" />';
		echo "<input name='description[]' value='" . $row['name'] . "' />";
		
		echo "<a href='bandskills.php?eventtyperemove=true&eventtypeID=" . $row['id'] . "'><img src='graphics/close.png' /></a><br />"; 
	 } ?>
     </fieldset>
     
	 </form>
	 <h2>Add a new band instrument:</h2>
	 <form action="bandskills.php?method=newtype" method="post">
	 	<fieldset>
	 		<p><label>New instrument:</label>
	 		<input type="text" name="description" id="banddescription"/></p>
	 	</fieldset>
	 	<input type="submit" value="Add new skill" />
	 </form>

	</p>
		
	
</div>
<? 
if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="settings.php">Back to settings</a></div>
		<div class="item"><a href="createEvent.php">Create a new event</a></div>
</div>
<? } ?>
<? include('includes/footer.php'); ?>