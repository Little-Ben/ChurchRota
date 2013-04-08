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
$userID = $_GET["id"];

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$groupID = $_POST['groups'];
	
	$description = $_POST['description'];
	$description = strip_tags($description);
	
	$sql = ("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userID', '$groupID', '$description')");
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	// After we have inserted the data, we want to head back to the main users page
	 header('Location: viewUsers.php#section' . $userID); // Move to the home page of the admin section
      exit;
}
include('includes/header.php');
?>

<div class="elementBackground">
<?
	$namesql = "SELECT firstName, lastName FROM cr_users WHERE id = '$userID'";
	$nameresult = mysql_query($namesql) or die(mysql_error());
	
	while($nametag = mysql_fetch_array($nameresult, MYSQL_ASSOC)) {
		?>
		<p>Enter new skills for <? echo $nametag['firstName'] . " " . $nametag['lastName']; } ?></p>
<form action="#" method="post" id="addSkill">
		<fieldset>
		<label for="groups">Skill type:</label>
			<select name="groups" id="groups">
<?
	$sql = "SELECT * FROM cr_groups";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		?>
		<option value="<? echo $row['groupID']; ?>"><? echo $row['description']; ?></option>
	
	<?
	}

?>
</select>
<label for="description">Description:</label>
			<input id="description" name="description" type="text" placeholder="Enter skill description" />
<input type="submit" value="Submit" />
		</fieldset>
	</form>	
</div>
<? if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="viewUsers.php">View all users</a></div>
</div>
<? } ?>
<? include('includes/footer.php'); ?>
