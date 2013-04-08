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
$userID = $_SESSION['userid'];
$id = $_GET['edit'];




// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$oldPassword = mysql_real_escape_string($_POST['oldpassword']);
		$newPassword = mysql_real_escape_string($_POST['newpassword']);
		$checkPassword = mysql_real_escape_string($_POST['checkpassword']);
		$newPassword = md5($newPassword);
		$oldPassword = md5($oldPassword);
		$checkPassword = md5($checkPassword);
		// Check the password matches the old one
		$sql = "SELECT * FROM cr_users WHERE id = '$userID' OR id = '$id'";
		$result = mysql_query($sql) or die(mysql_error());
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($oldPassword == $row['password']) {
				if($newPassword == $checkPassword) { 
					$message = "Your password has been changed";
					$status = "success";
				} else {
					$message = "Please check both the new passwords match";
				}
				
			} else {
				$message = "Password incorrect, please try again";
				$status = "fail";
			}
		}
		
		
		if($status == "success") {
		// Update the database rather than insert new values
		$sql = "UPDATE cr_users SET password = '$newPassword' WHERE id = '$userID'";
	
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		}
} 
include('includes/header.php');
?>


<div class="elementBackground">
<h2>Edit password</h2>
<p><?php echo $message; ?></p>
<form action="#" method="post" id="addUser">
		<fieldset>
			<label for="oldpassword">Old password:</label>
			<input name="oldpassword" id="oldpassword" type="password"  />
			
			<label for="newpassword">New password:</label>
			<input name="newpassword" id="newpassword" type="password"  />
			
			<label for="checkpassword">Verify:</label>
			<input id="checkpassword" name="checkpassword" type="password"   />
			
			
			
			<input type="submit" value="Edit Password" />
		</fieldset>
	</form>
</div>

<div id="right">
		<div class="item"><a href="addUser.php?action=edit&id=<? echo $_SESSION['userid']; ?>">Edit my account</a></div>
</div>
<? include('includes/footer.php'); ?>
