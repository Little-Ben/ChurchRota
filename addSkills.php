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
	exit;
} 
if (!isAdmin()) {
	header('Location: error.php?no=100&page='.basename($_SERVER['SCRIPT_FILENAME']));
	exit;
}

// Get the query string
$userID = $_GET["id"];

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$groupID = $_POST['groups'];
	
	$description = $_POST['description'];
	$description = strip_tags($description);
	
	$sql = ("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userID', '$groupID', '$description')");
	//V2.1.0//echo $sql;
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	// After we have inserted the data, we want to head back to the main users page
	//V2.1.0// header('Location: bandskills.php'); // Move to the home page of the admin section
	header('Location: viewUsers.php#section' . $userID); // Move to the home page of the admin section
      exit;
}
include('includes/header.php');
?>

<div class="elementBackground">
<?php
	$namesql = "SELECT firstName, lastName FROM cr_users WHERE id = '$userID'";
	$nameresult = mysql_query($namesql) or die(mysql_error());
	
	while($nametag = mysql_fetch_array($nameresult, MYSQL_ASSOC)) {
		?>
		<p>Enter new skills for <?php echo $nametag['firstName'] . " " . $nametag['lastName']; } ?></p>
<form action="#" method="post" id="addSkill">
		<fieldset>
		<label for="groups">Skill type:</label>
			<select name="groups" id="groups">
<?php
	$sql = "SELECT * FROM cr_groups";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		?>
		<option value="<?php echo $row['groupID']; ?>"><?php echo $row['description']; ?></option>
	
	<?php
	}

?>
</select>
<label for="description">Description:</label>
			<input id="description" name="description" type="text" placeholder="Enter skill description" />
<input type="submit" value="Submit" />
		</fieldset>
	</form>	
</div>
<?php if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="viewUsers.php">View all users</a></div>
</div>
<?php } ?>
<?php include('includes/footer.php'); ?>
