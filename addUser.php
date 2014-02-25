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
$userID = $_GET['id'];
$userremove = $_GET['userremove'];
$sessionUserID = $_SESSION['userid'];

if($userremove == "true") {
	removeUser($userID);
}

if($action == "reset") {
	$new_password = md5('churchrota');
	$sql = "UPDATE cr_users SET password = '$new_password' WHERE id = '$userID'";
	$result = mysql_query($sql) or die(mysql_error); 

	header('Location: viewUsers.php');
}

if($action == "edit") {
	$sql = "SELECT * FROM cr_users WHERE id = '$userID'";
	$result = mysql_query($sql) or die(mysql_error); 
	
	while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {
	 	$id = $row['id'];
		$firstname = $row['firstName'];
		$lastname = $row['lastName'];
		$email = $row['email'];
		$mobile = $row['mobile'];
		$userisAdmin = $row['isAdmin'];
		$userisBandAdmin = $row['isBandAdmin'];
		$userisEventEditor = $row['isEventEditor'];
		$userisOverviewRecipient = $row['isOverviewRecipient'];
	}
} 



// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(isAdmin()) {
	$firstname = $_POST['firstname'];
	$firstname = strip_tags($firstname);
	$firstnameLower = strtolower($firstname);
	$lastname = $_POST['lastname'];
	$lastname = strip_tags($lastname);
	$lastnameLower = strtolower($lastname);
	
	$username = $firstnameLower.$lastnameLower;
	
	} else {
		$firstnameLower = strtolower($firstname);
		$lastnameLower = strtolower($lastname);
		$username = $firstnameLower.$lastnameLower;
	}
	$email = $_POST['email'];
	$email = strip_tags($email);
	
	$mobile = $_POST['mobile'];
	$mobile = strip_tags($mobile);
	
	$band = $_POST['band'];
	$otherskills = $_POST['otherskills'];

	if(isset($_POST['isAdmin'])) 
			{
				$isAdminLocal = '1';
			}
		else
			{
	    		$isAdminLocal = '0';
			}	
			
	if(isset($_POST['isOverviewRecipient'])) 
			{
				$userisOverviewRecipient = '1';
			}
		else
			{
	    		$userisOverviewRecipient = '0';
			}	

	if(isset($_POST['isBandAdmin'])) 
			{
				$userisBandAdmin = '1';
			}
		else
			{
	    		$userisBandAdmin = '0';
			}	
	if(isset($_POST['isEventEditor'])) 
			{
				$userisEventEditor = '1';
			}
		else
			{
	    		$userisEventEditor = '0';
			}	
			
	if($action == "edit") {
		// Update the database rather than insert new values
		$sql = "UPDATE cr_users SET firstName = '$firstname', lastName = '$lastname', username = '$username', isAdmin = '$isAdminLocal',
		email = '$email', mobile = '$mobile', isOverviewRecipient = '$userisOverviewRecipient', isBandAdmin = '$userisBandAdmin', isEventEditor = '$userisEventEditor' WHERE id = '$userID'";
	} else {
		// Create a new record
		$password = RandomPassword(8, true, true, true);
		mailNewUser($firstname, $lastname, $email, $username, $password);
		$passwordEncrypted = md5($password);
	    $sql = ("INSERT INTO cr_users (firstName, lastName, username, isAdmin, email, mobile, password,isOverviewRecipient,isBandAdmin,isEventEditor)
VALUES ('$firstname', '$lastname', '$username', '$isAdminLocal', '$email', '$mobile', '$passwordEncrypted', '$userisOverviewRecipient', '$userisBandAdmin' ,'$userisEventEditor')");
	}
	
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
	if($action != "edit") {
		$userID = mysql_insert_id();
	}
	if(isAdmin()) {
	if (isset($band) or isset($otherskills)) { 
		
		if(isset($band)) {
			if($action == "edit") { 
			$sql2 = "SELECT *, 
			(SELECT skill FROM cr_skills WHERE cr_skills.skill = cr_instruments.name AND userID = '$userID' ) AS description
			FROM cr_instruments WHERE name IN (SELECT skill FROM cr_skills WHERE cr_skills.skill = cr_instruments.name AND userID = '$userID')";
			$result2 = mysql_query($sql2) or die(mysql_error());
			while($row = mysql_fetch_array($result2, MYSQL_ASSOC)) 
			{ 
				$bandarray[]= $row['description'];
			}
			if(empty($bandarray)) {
				while (list ($key, $valadd) = each ($band)) {
					addBandSkill($userID, $valadd);
				}
			} else {
				$addarray = array_diff($band, $bandarray); 
			
				while (list ($key, $valadd) = each ($addarray)) {
					addBandSkill($userID, $valadd);
				}
			
			
			$deletearray = array_diff($bandarray, $band);
			
			while (list ($key2, $valdelete) = each ($deletearray)) {
				deleteBandSkill($userID, $valdelete);
			}
			}
			// Otherwise we're just adding values alongside our recently inserted SQL row
			} else {
				while (list ($key, $valadd) = each ($band)) {
					addBandSkill($userID, $valadd);
				}
			}
		}
		
		
		if(isset($otherskills)) {
			if($action == "edit") { 
			//$sql2 = "SELECT *
			//	FROM cr_groups WHERE groupID != 2 AND groupID IN (SELECT groupID FROM cr_skills WHERE cr_skills.groupID = cr_groups.groupID AND cr_skills.userID = '$userID') ORDER BY groupID";
				
			$sql2 = "SELECT distinct g.groupID
						FROM cr_groups g
						inner join cr_skills s on s.groupID = g.groupID 
						and g.groupID != 2
						AND s.userID = '$userID'
						ORDER BY g.groupID";
						
			$result2 = mysql_query($sql2) or die(mysql_error());
			while($row = mysql_fetch_array($result2, MYSQL_ASSOC)) 
			{ 
				$skillsarray[]= $row['groupID'];
			}
			if(empty($skillsarray)) {
				while (list ($key, $valadd) = each ($otherskills)) {
					addOtherSkills($userID, $valadd);
				}
			} else { 
				$addarray = array_diff($otherskills, $skillsarray); 
			
				while (list ($key3, $valadd) = each ($addarray)) {
					addOtherSkills($userID, $valadd);
				}
			
			
			$deletearray = array_diff($skillsarray, $otherskills);
			
			while (list ($key4, $valdelete) = each ($deletearray)) {
				removeOtherSkills($userID, $valdelete);
			
			}
			}
			// Otherwise inserting from scratch
			} else {
				while (list ($key, $valadd) = each ($otherskills)) {
					addOtherSkills($userID, $valadd);
				}
			}
		}
		
		}
	}
	if(isAdmin()) {
		header ( "Location: viewUsers.php#section" . $userID);
	//}else{
		//header ( "Location: addUser.php?action=edit&id=" . $userID);
	}
} 
include('includes/header.php');
?>


<div class="elementBackground">
<?php
	// Work out what action we need to give the form
	if($action == "edit") {
		$formstring = "id=$userID&action=$action";
	} else {
		$formstring = "id=$userID";
	}	

?>
<form action="addUser.php?<?php echo $formstring; ?>" method="post" id="addUser">
		<fieldset>
			
			<?php 
			if(isAdmin()) { 
				$isCompromised=false;
			?>
			<label for="firstname">First name:</label>
			<input name="firstname" id="firstname" type="text" value="<?php echo $firstname; ?>" placeholder="Enter first name" />
			
			<label for="lastname">Last name:</label>
			<input name="lastname" id="lastname" type="text" value="<?php echo $lastname; ?>" placeholder="Enter last name" />
			
			<label for="isAdmin">Make them an ADMIN?:</label>
			<input name="isAdmin" id="isAdmin" type="checkbox" value="1" <?php if($userisAdmin == '1') { echo 'checked="checked"'; } 
			else if($userisAdmin == '0') { }?> />
			
			<label for="isBandAdmin">Make them a BAND admin?:</label>
			<input name="isBandAdmin" id="isBandAdmin" type="checkbox" value="1" <?php if($userisBandAdmin == '1') { echo 'checked="checked"'; } 
			else if($userisBandAdmin == '0') { }?> />

			<label for="isEventEditor">Make them an EVENT EDITOR?:</label>
			<input name="isEventEditor" id="isEventEditor" type="checkbox" value="1" <?php if($userisEventEditor == '1') { echo 'checked="checked"'; } 
			else if($userisEventEditor == '0') { }?> />
			
			<label for="isOverviewRecipient">Make them a MAIL OVERVIEW RECIPIENT?:</label>
			<input name="isOverviewRecipient" id="isOverviewRecipient" type="checkbox" value="1" <?php if($userisOverviewRecipient == '1') { echo 'checked="checked"'; } 
			else if($userisOverviewRecipient == '0') { }?> />
			
			<?php } else {
			
				if ($userID == $sessionUserID) {
					echo $firstname . " " . $lastname;
					$isCompromised=false;
				}else{
					notifyAttack(__FILE__,"Impersonating Attack",$sessionUserID);
					$isCompromised=true;
				}
					
			} 
			
			if (!$isCompromised) {
			?>
			<label for="email">Email:</label>
			<input id="email" name="email" type="text" value="<?php echo $email; ?>" placeholder="Enter their email address" />
			
			<label for="mobile">Mobile number:</label>
			<input id="mobile" name="mobile" type="text" value="<?php echo $mobile; ?>" placeholder="Enter their mobile number" />
			<?php } ?>
		</fieldset>
		<?php if(isAdmin()) { ?>
        <fieldset>
        <h3>Band skills:</h3>
        <?php
							
        	$sql2 = "SELECT *, 
			(SELECT skill FROM cr_skills WHERE cr_skills.skill = cr_instruments.name AND userID = '$userID') AS description
			FROM cr_instruments";
			$result2 = mysql_query($sql2) or die(mysql_error());
			$position = 1;
			
			while($row2 =  mysql_fetch_array($result2, MYSQL_ASSOC)) {
				$description = $row2['description'];
				$instrument = $row2['name'];
				
				if($position == 1) {
					echo "<div class='row'>";
					echo "<div class='checkboxitem'>";
					checkBandSkill($description, $instrument);
					echo "</div>";
					$position = 2;
				} else {
					echo "<div class='checkboxitem right'>";
					checkBandSkill($description, $instrument);
					echo "</div>";
					echo "</div>";
					$position = 1;
				}
			
            
         
           } ?>
        </fieldset>
		
		<fieldset>
        <h3>Other roles</h3>
        	<?php			
		//$sql = "SELECT *, 
		//	(SELECT groupID FROM cr_skills WHERE cr_skills.groupID = cr_groups.groupID AND cr_skills.userID = '$userID' LIMIT 1) AS inSkill
		//	FROM cr_groups WHERE groupID != 2 ORDER BY groupID";
			
		$sql = "select g.groupID,g.description,g.rehearsal,g.formatgroup,g.short_name,min(s.groupID) inSkill
					from cr_groups g
					left outer join cr_skills s on g.groupID=s.groupID
					and s.userID='$userID'
					WHERE g.groupID != 2 
					GROUP BY g.groupID,g.description,g.rehearsal,g.formatgroup,g.short_name
					ORDER BY g.groupID";
					
			$result = mysql_query($sql) or die(mysql_error());
			$i = 1;
			$position = 1;
			while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {

				$description = $row['description'];
					
					
					if($row['inSkill'] != '') 
					{ $checked =  'checked=\'checked\' '; }  else { $checked = "";  }
					
					
					if($position == 2) {
						echo "<div class='checkboxitem right'><label class='styled' for='rehearsaldate[" .  $row['groupID'] . "]'>" .
						$row['description'] . "</em></label><input class='styled' " . $checked . "type='checkbox' id='rehearsaldate[" . 
						$row['groupID'] . "]'	name='otherskills[]' value='" .  
						$row['groupID'] . "' /></div></div>";
						
						$position = 1;
					} else {
						if($i == "1") { 
							$class = ""; 
							$i = "0"; 
						} else { 
						$class = ""; 
							$i = "1";
						}
						
						echo "<div class='row" . $class . "'>
						<div class='checkboxitem'><label class='styled' for='rehearsaldate[" .  $row['groupID'] . "]'>" .
						$row['description'] .  "</em></label><input class='styled' " . $checked . "type='checkbox' id='rehearsaldate[" . 
						$row['groupID'] . "]'	name='otherskills[]' value='" .  
						$row['groupID'] . "' /></div>";
						
						$position = 2;
					}
					
					
					
					
		
					
					
					
			}
			if ($position == 2) {
							echo "</div>";
							$position = 1;
						}
			
			
		
        
        
        	
				
			} ?>
        
        </fieldset>
		<?php if (!$isCompromised) {
				if($action == "edit") {
					echo '<input type="submit" value="Save changes" />';
				} else { 
					echo '<input type="submit" value="Add user" />';
				}
		} ?>
	</form>
</div>
<div id="right">
<?php if(isAdmin()) { ?>

		<div class="item"><a href="viewUsers.php">View all users</a></div>
		<?php }
		if (!$isCompromised) {
			if($action == "edit") {		?>
				<div class="item"><a href="editPassword.php?id=<?php echo $id; ?>">Change password</a></div>
			<?php }
		}?>
</div>
<?php include('includes/footer.php'); ?>
