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
$userremove = $_GET['userremove'];

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
				$isAdmin = '1';
			}
		else
			{
	    		$isAdmin = '0';
			}	
	if($action == "edit") {
		// Update the database rather than insert new values
		$sql = "UPDATE cr_users SET firstName = '$firstname', lastName = '$lastname', username = '$username', isAdmin = '$isAdmin',
		email = '$email', mobile = '$mobile' WHERE id = '$userID'";
	} else {
		// Create a new record
		$password = RandomPassword(8, true, true, true);
		mailNewUser($firstname, $lastname, $email, $username, $password);
		$passwordEncrypted = md5($password);
	    $sql = ("INSERT INTO cr_users (firstName, lastName, username, isAdmin, email, mobile, password)
VALUES ('$firstname', '$lastname', '$username', '$isAdmin', '$email', '$mobile', '$passwordEncrypted')");
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
			$sql2 = "SELECT *
				FROM cr_groups WHERE groupID != 2 AND groupID IN (SELECT groupID FROM cr_skills WHERE cr_skills.groupID = cr_groups.groupID AND cr_skills.userID = '$userID') ORDER BY groupID";
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
	if(isAdmin()):
		header ( "Location: viewUsers.php#section" . $userID);
    else :
	header ( "Location: index.php ");
	endif;
} 
include('includes/header.php');
?>


<div class="elementBackground">
<?
	// Work out what action we need to give the form
	if($action == "edit") {
		$formstring = "id=$userID&action=$action";
	} else {
		$formstring = "id=$userID";
	}	

?>
<form action="addUser.php?<? echo $formstring; ?>" method="post" id="addUser">
		<fieldset>
			<? if(isAdmin()) { ?>
			<label for="firstname">First name:</label>
			<input name="firstname" id="firstname" type="text" value="<? echo $firstname; ?>" placeholder="Enter first name" />
			
			<label for="lastname">Last name:</label>
			<input name="lastname" id="lastname" type="text" value="<? echo $lastname; ?>" placeholder="Enter last name" />
			
			<label for="isAdmin">Make them an admin?:</label>
			<input name="isAdmin" id="isAdmin" type="checkbox" value="1" <? if($userisAdmin == '1') { echo 'checked="checked"'; } 
			else if($userisAdmin == '0') { }?> />
			
			<? } else {
				echo $firstname . " " . $lastname;
			} ?>
			
			
			<label for="email">Email:</label>
			<input id="email" name="email" type="text" value="<? echo $email; ?>" placeholder="Enter their email address" />
			
			<label for="mobile">Mobile number:</label>
			<input id="mobile" name="mobile" type="text" value="<? echo $mobile; ?>" placeholder="Enter their mobile number" />
		</fieldset>
		<? if(isAdmin()) { ?>
        <fieldset>
        <h3>Band skills:</h3>
        <?
							
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
		
        <h3>Other roles</h3>
        	<?			
		$sql = "SELECT *, 
			(SELECT groupID FROM cr_skills WHERE cr_skills.groupID = cr_groups.groupID AND cr_skills.userID = '$userID' LIMIT 1) AS inSkill
			FROM cr_groups WHERE groupID != 2 ORDER BY groupID";
			$result = mysql_query($sql) or die(mysql_error());
			$i = 1;
			$position = 1;
			while($row =  mysql_fetch_array($result, MYSQL_ASSOC)) {

				$description = $row['description'];
					
					
					if($row['inSkill'] != '') 
					{ $checked =  'checked="checked"'; }  else { $checked = "";  }
					
					
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
		<? if($action == "edit") {
		echo '<input type="submit" value="Save changes" />';
			} else { 
				echo '<input type="submit" value="Add user" />';
		} ?>
	</form>
</div>
<div id="right">
<? if(isAdmin()) { ?>

		<div class="item"><a href="viewUsers.php">View all users</a></div>
		<? }
		if($action == "edit") {		?>
		<div class="item"><a href="editPassword.php?id=<? echo $id; ?>">Change password</a></div>
<? } ?>
</div>
<? include('includes/footer.php'); ?>
