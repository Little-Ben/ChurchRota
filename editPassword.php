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

$action = $_GET['action'];
$userID = $_SESSION['userid'];
//$id = $_GET['edit'];
$id = $_GET['id'];



// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$oldPassword = mysql_real_escape_string($_POST['oldpassword']);
		$newPassword = mysql_real_escape_string($_POST['newpassword']);
		$checkPassword = mysql_real_escape_string($_POST['checkpassword']);
		$newPassword = md5($newPassword);
		$oldPassword = md5($oldPassword);
		$checkPassword = md5($checkPassword);
		// Check the password matches the old one
		//$sql = "SELECT * FROM cr_users WHERE id = '$userID' OR id = '$id'";
		$sql = "SELECT * FROM cr_users WHERE id = '$id'";
		$result = mysql_query($sql) or die(mysql_error());
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if(($oldPassword == $row['password'])||(isAdmin())) {
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
			
			$firstname = $row['firstName'];
			$lastname = $row['lastName'];
		}
		
		
		if($status == "success") {
			// Update the database rather than insert new values
			//$sql = "UPDATE cr_users SET password = '$newPassword' WHERE id = '$userID'";
			$sql = "UPDATE cr_users SET password = '$newPassword' WHERE id = '$id'";

			//if ($debug) notifyInfo(__FILE__,"pwd_change",$userID); //only_for_testing//
			if ($debug) insertStatistics("user",__FILE__,"pwd_change",$status);

			
			if (!mysql_query($sql))
			{
			die('Error: ' . mysql_error());
			}
		}
} 
else
{
		//no POST -> we are in edit mode
		$sql = "SELECT * FROM cr_users WHERE id = '$id'";
		$result = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$firstname = $row['firstName'];
		$lastname = $row['lastName'];
}
include('includes/header.php');
?>


<div class="elementBackground">
<h2>Edit password</h2>
<?php
	if (($userID == $id)||(isAdmin())) {
?>

<p><?php echo $message; ?></p>
<form action="#" method="post" id="addUser">
		<fieldset>
		<?php echo $firstname . " " . $lastname . "<br>"; ?>

			<label for="oldpassword" ><?php if (!isAdmin()) { echo "Old password:"; }?></label>
			<input name="oldpassword" id="oldpassword" <?php if (isAdmin()) { echo "type=\"hidden\""; }else{ echo "type=\"password\""; } ?> />
			
			<label for="newpassword">New password:</label>
			<input name="newpassword" id="newpassword" type="password"  />
			
			<label for="checkpassword">Verify:</label>
			<input id="checkpassword" name="checkpassword" type="password"   />
			
			
			
			<input type="submit" value="Edit Password" />
		</fieldset>
	</form>
<?php
}else{
	notifyAttack(__FILE__,"Password Change Attack",$userID);
	if ($debug) insertStatistics("system",__FILE__,"Password Change Attack");
}
?>	
	
</div>

<div id="right">
		<div class="item"><a href="addUser.php?action=edit&id=<?php echo $_SESSION['userid']; ?>">Edit my account</a></div>
</div>
<?php include('includes/footer.php'); ?>
