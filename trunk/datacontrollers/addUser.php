<?php
$action = $db->escape($_GET['action']);
$userID = $db->escape($_GET['id']);
$userremove = $db->escape($_GET['userremove']);


if($userremove == "true") {
	removeUser($userID);
}

if($action == "edit") {
	$user = $db->get_row("SELECT * FROM cr_users WHERE id = '$userID'");
	$firstname = $user->firstName;
	$lastname = $user->lastName;
	$username = $user->username;
	$email = $user->email;
	$mobile = $user->mobile;
} 

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(isAdmin()) {
		$firstname = $db->escape($_POST['firstname']);
		$firstnameLower = strtolower($firstname);
		$lastname = $db->escape($_POST['lastname']);
		$lastnameLower = strtolower($lastname);
		$username = $firstnameLower.$lastnameLower;
	} 
	
	$email = $db->escape($_POST['email']);
	$mobile = $db->escape($_POST['mobile']);
	$band = $_POST['band'];
	$otherskills = $_POST['otherskills'];
	
	if($email != "") {
		if(isset($_POST['isAdmin'])) 
			{
				$isAdmin = '1';
			}
		else
			{
	    		$isAdmin = '0';
			}	
		
		// This section creates the actual user profile
		if($action == "edit") {
			// Update the database rather than insert new values
			$db->query("UPDATE cr_users SET firstName = '$firstname', lastName = '$lastname', 
			username = '$username', isAdmin = '$isAdmin', email = '$email', mobile = '$mobile' WHERE id = '$userID'");
		} else {
			// Create a new record
			$password = RandomPassword(8, true, true, true);
			mailNewUser($firstname, $lastname, $email, $username, $password);
			$passwordEncrypted = md5($password);
			$db->query("INSERT INTO cr_users (firstName, lastName, username, isAdmin, email, mobile, password)
			VALUES ('$firstname', '$lastname', '$username', '$isAdmin', '$email', '$mobile', '$passwordEncrypted')");
		}
		
		if($action != "edit") {
			$userID = $db->insert_id; // If it's a new row, we need to have a handle on the ID to do the next stage
		}
		
		if(isAdmin()) {
			
			if (isset($band) or isset($otherskills)) { 
				if(isset($band)) {
					
					if($action == "edit") {
						$bandResultsQueries = $db->get_results("SELECT *, 
						(SELECT skill FROM cr_skills WHERE cr_skills.skill = cr_instruments.name AND userID = '$userID' ) AS description
						FROM cr_instruments 
						WHERE name IN (SELECT skill FROM cr_skills WHERE cr_skills.skill = cr_instruments.name AND userID = '$userID')");
						if(!empty($bandResultsQueries)) {
							foreach($bandResultsQueries as $bandResultsQuery) {
								$bandarray[] = $bandResultsQuery->description;
							}
						} 
						
						if(empty($bandarray)) {
							$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND groupID = 2");
							foreach($band as $valadd) {
								$db->query("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userID', '2', '$valadd')");
							}
						} else { // otherwise if $bandarray != empty and so we need to cycle through the array
							$addarray = array_diff($band, $bandarray); 
			
							while (list ($key, $valadd) = each ($addarray)) {
								$db->query("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userID', '2', '$valadd')");
							}
			
							$deletearray = array_diff($bandarray, $band);
			
							while (list ($key2, $valdelete) = each ($deletearray)) {
								$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND skill = '$valdelete'");
							}
						} // End of $bandarray empty else
						
					} else { // End check for $action being edit and so we need to just insert
						while (list ($key, $valadd) = each ($band)) {
							$db->query("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userID', '2', '$valadd')");
						}
					} // End of $band insert from scratch
					
				} else { // End isset $band
					$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND groupID = 2");
				}
				
				if(isset($otherskills)) {
					if($action == "edit") { 
						$otherSkillsResultsQueries = $db->get_results("SELECT *
						FROM cr_groups 
						WHERE groupID != 2 AND groupID IN 
						(SELECT groupID FROM cr_skills WHERE cr_skills.groupID = cr_groups.groupID AND cr_skills.userID = '$userID') 
						ORDER BY groupID");
						if(!empty($otherSkillsResultsQueries)) {
							foreach($otherSkillsResultsQueries as $otherSkillsResultsQuery) {
								$skillsarray[] = $otherSkillsResultsQuery->groupID;
							}
						}
						
						if(empty($skillsarray)) {
							$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND groupID <> '2'");
				
							foreach($otherskills as $valadd) {
								$db->query("INSERT INTO cr_skills (userID, groupID) VALUES ('$userID', '$valadd')");
								
							}
						} else {  // Finish check for $skillsarray being empty
							$addarray = array_diff($otherskills, $skillsarray); 
							
							while (list ($key3, $valadd) = each ($addarray)) {
								$db->query("INSERT INTO cr_skills (userID, groupID) VALUES ('$userID', '$valadd')");
								
							}
			
							$deletearray = array_diff($skillsarray, $otherskills);
			
							while (list ($key4, $valdelete) = each ($deletearray)) {
								$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND groupID = '$valdelete'");
							}
						} // End of $skillsarray empty check
			
					} else { // $action not edit so compeare from scratch
						while (list ($key, $valadd) = each ($otherskills)) {
							$db->query("INSERT INTO cr_skills (userID, groupID) VALUES ('$userID', '$valadd')");
						}
					}// End of $action being edit
					
				} else { // End isset $band
					$db->query("DELETE FROM cr_skills WHERE userID = '$userID' AND groupID != 2");
				}
				
			} // End isset test for $band and $otherskills
			
			header ( "Location: viewUsers.php#section" . $userID);
		} // End isAdmin() check
		
	} // End email presence check

} // End the method for handling information from the form

?>
