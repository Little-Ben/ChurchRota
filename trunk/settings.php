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

$action = $_GET['action'];
$userID = $_GET['id'];

// If the form has been sent, we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$siteurl = $_POST['siteurl'];
	$notificationemail = $_POST['notificationemail'];
	$notificationemail = mysql_real_escape_string($notificationemail);
	$siteadminemail = $_POST['siteadminemail'];
	$siteadminemail = mysql_real_escape_string($siteadminemail);
	$norehearsalemail = $_POST['norehearsalemail'];
	$norehearsalemail = mysql_real_escape_string($norehearsalemail);
	$yesrehearsal = $_POST['yesrehearsal'];
	$yesrehearsal = mysql_real_escape_string($yesrehearsal);
	$newusermessage = $_POST['newusermessage'];
	$newusermessage = mysql_real_escape_string($newusermessage);	
	$owner = $_POST['owner'];

	
		// Update the database rather than insert new values
		$sql = "UPDATE cr_settings SET siteurl = '$siteurl', notificationemail = '$notificationemail', adminemailaddress = '$siteadminemail', norehearsalemail = '$norehearsalemail', yesrehearsal = '$yesrehearsal', newusermessage = '$newusermessage', owner = '$owner'";
	
	
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
	header('Location: settings.php');
} 

include('includes/header.php');
?>



<div class="elementBackground">
<form action="#" method="post" id="settings">
		<fieldset>
		<?
		$sql = "SELECT * FROM cr_settings";
		$result = mysql_query($sql) or die(mysql_error());
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		?>
			<label class="owner" for="siteurl">Enter your organisation name:</label>
			<input class="owner" name="owner" id="owner" type="text" value="<? echo $row['owner'];?>"  />
		
			<label class="settings" for="siteurl">Enter URL of your website:</label>
			<input class="settings" name="siteurl" id="siteurl" type="text" value="<? echo $row['siteurl'];?>"  />
			
			<label class="settings" for="siteadminemail">Enter your admin email address:</label>
			<input class="settings" name="siteadminemail" id="siteadminemail" type="text" value="<? echo $row['adminemailaddress'];?>"  />
			
			<label class="settings" for="notificationemail">Enter the text you would like at the bottom of a notification email:</label>
			<textarea id="notificationemail" type="text" name="notificationemail"><? echo $row['notificationemail'];?></textarea>
			
			<label class="settings" for="norehearsalemail">Text to display for events with no rehearsal</label>
			<textarea id="norehearsalemail" type="text" name="norehearsalemail"><? echo $row['norehearsalemail'];?></textarea>
			
			<label class="settings" for="yesrehearsal">Text to display for advertising rehearsal</label>
			<textarea id="yesrehearsal" type="text" name="yesrehearsal"><? echo $row['yesrehearsal'];?></textarea>
			
			<label class="settings" for="newusermessage">Email to new users:</label>
			<textarea id="newusermessage" type="text" name="newusermessage"><? echo $row['newusermessage'];?></textarea>
<?
		}
		?>
			
			
		<input type="submit" value="Update" class="settings" />
		</fieldset>
	</form>

</div>
<div id="right">
	<div class="item"><a href="editeventtype.php">Edit event types</a></div>
	<div class="item"><a href="editSkills.php">Edit skills</a></div>
	<div class="item"><a href="bandskills.php">Edit band skills</a></div>
	<div class="item"><a href="locations.php">Edit Locations</a></div>
</div>
<? include('includes/footer.php'); ?>