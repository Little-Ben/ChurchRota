<?php include('includes/dbConfig.php');
include('includes/functions.php');

// we must never forget to start the session
session_start();

if (isset($_POST['username']) && isset($_POST['password'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$password = md5($password);

	$sqlSettings = "SELECT * FROM cr_settings";
	$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
	$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
	
	if ($rowSettings[users_start_with_myevents]==1) {
		$users_start_with_myevents = "1";
	}else{
		$users_start_with_myevents = "0";
	}
	
	$sql = "SELECT * FROM cr_users WHERE username = '$username' AND password = '$password'";
	$result = mysql_query($sql, $dbh);
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	 $_SESSION['db_is_logged_in'] = true; 
      $_SESSION['isAdmin'] = $row['isAdmin']; // Set the admin status to be carried across this session
      $_SESSION['userid'] = $row['id'];
      $_SESSION['name'] = $row['firstName'] . " " . $row['lastName'];
	  $_SESSION['isBandAdmin'] = $row['isBandAdmin']; // Set the band admin status to be carried across this session
		
	//statistic 
	  if ($debug) insertStatistics("user",__FILE__,"login",null,$_SERVER['HTTP_USER_AGENT']);

	
   	// after login we move to the main page
      if ($_SESSION['isAdmin']==1) {
		updateDatabase();	  
		header('Location: index.php'); // Move to the home page of the admin section
	  }else{
		//if ($debug) notifyInfo(__FILE__,"login",$row['id']); //only_for_testing//
	    if ($users_start_with_myevents=='1') {
			header('Location: index.php?showmyevents=' . $_SESSION['userid']); // Move to the home page of the admin section
		}else{
			header('Location: index.php');
		}
	  }
      exit;
      
   }
}
include('includes/header.php'); ?>
<div class="elementBackground">
<h2>Login</h2>
<p>Please enter your username and password</p>
<form action="login.php" method="post" >
		<fieldset>
			<label for="username">Username:</label>
			<input name="username" id="username" type="text" placeholder="Enter your username" />
			
			<label for="password">Password:</label>
			<input name="password" id="password" type="password" placeholder="Enter your password" />
			
			<input type="submit" value="Login now" />
		</fieldset>
	</form>
</div>


<? include('includes/footer.php'); ?>