<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2013 Benjamin Schmitt

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

$action = $_GET['action'];
$userID = $_GET['id'];

// If the form has been sent, we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$prev_subject = $_POST['prev_subject'];
	//$prev_subject = mysql_real_escape_string($prev_subject);

	$prev_message = $_POST['prev_message'];
	//$prev_message = mysql_real_escape_string($prev_message);
	
	$overviewSent = notifyOverview($prev_subject,$prev_message);
}

$overviewArr = notifyOverview("",""); 

$formatting = "true";

$queryRcpt="select count(email) as CNT from cr_users where isOverviewRecipient=1";
$resultRcpt = mysql_query($queryRcpt) or die(mysql_error());
$rowRcpt = mysql_fetch_array($resultRcpt, MYSQL_ASSOC);

include('includes/header.php');
?>
<div class="elementBackground">
<h2>Rota Overview Mail</h2>	
<?php
if ($overviewSent == "")
{
?>
	<form action="#" method="post" id="settings">
		<fieldset>
			<div class="elementContent" >	
				
				<label class="settings">This message will be sent to ALL users flagged as "Overview Recipient".</label>
				
				<label class="settings" for="prev_subject">Subject:</label>	
				<input class="settings" name="prev_subject" id="prev_subject" type="text" value="<?php echo $overviewArr[0];?>"  />
				
				<label class="settings" for="prev_message">Message to <?php echo $rowRcpt["CNT"]; ?> user/s:</label>
				<textarea class="mceNoEditor" id="prev_message" type="text" name="prev_message"><?php echo $overviewArr[1];?></textarea>
			
			</div>
			<input type="submit" value="Send email" class="settings" />
		</fieldset>
	</form>

<?php			
}else{
	echo $overviewSent;
}?>	
</div>	
		
<?php include('includes/footer.php'); ?>