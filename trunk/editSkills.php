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
$skillID = $_GET["skillID"];
$skillremove = $_GET['skillremove'];
$skillmove = $_GET['skillmove'];
$value = $_GET['value'];
$method = $_GET['method'];

if ($skillremove == "true") {
 removeSkillGroups($skillID);
 }
 
if ($skillmove == "true") {
 moveSkillGroups($skillID, $value);
 }


// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if($method == 'newskill') {
		$groupID = $_POST['groups'];
	
		$newskill = $_POST['newskill'];
		$newskill = strip_tags($newskill);
	
		$rehearsal = $_POST['rehearsal'];
		$rehearsal = strip_tags($rehearsal);
	
		$sql = ("INSERT INTO cr_groups (description, rehearsal, formatgroup) VALUES ('$newskill', '$rehearsal', '3')");
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}
	} else { 
		// Handle renaming of the titles
		$formindex = $_POST['formindex'];
		$description = $_POST['description'];
		 
		$formArray = array_combine($formindex, $description);
		
		
		while (list ($key, $valadd) = each ($formArray)) {
				updateSkill($key, $valadd);
			}
		
	}
		// After we have inserted the data, we want to head back to the main users page
	 	header('Location: editSkills.php'); // Move to the home page of the admin section
      	exit;
}
include('includes/header.php');
?>

<div class="elementBackground">
		<h2>Add a new skill:</h2>
		<p><form action="editSkills.php?method=newskill" method="post" id="addSkill">
		<fieldset>
		<label for="newskill">New skill:</label>
		<input id="newskill" name="newskill" type="text" placeholder="Enter skill name" />
		
			<label for="rehearsal">This skill group must attend rehearsals:</label>
			<input name="rehearsal" id="rehearsal" type="checkbox" value="1"  />
			
<input type="submit" value="Add new skill" />

		
		</fieldset>
	</form>	
    </p>

		<h2>Edit Groups</h2>
		<p>
        <form action="editSkills.php" method="post">
        <fieldset>
        <strong>Group 1:</strong><br />
		<? $sql = "SELECT * FROM cr_groups ORDER BY formatgroup, groupid";
	$result = mysql_query($sql) or die(mysql_error());
	$formatgroup = 1;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
		$skillID = $row['groupID'];
		if ($row['formatgroup'] == $formatgroup) {
			// Do nothing, because they are all in the same group	
			$down = $formatgroup + 1;
			$up = $formatgroup - 1;
		} else {
			// Update the group heading
			$formatgroup = $row['formatgroup'];
			$down = $formatgroup + 1;
			$up = $formatgroup - 1;
			echo "<br /><strong>Group " . $formatgroup . "</strong><br />";
		}
		echo '<input type="hidden" name="formindex[]" value="' . $row['groupID'] . '" />';
		echo "<input name='description[]' value='" . $row['description'] . "' />";
		
		echo " <a class='smalllink' href='editSkills.php?skillmove=true&value=" . $down . "&skillID=" . $skillID . "'>Down</a> ";
		echo "<a class='smalllink' href='editSkills.php?skillmove=true&value=" . $up . "&skillID=" . $skillID . "'>Up</a> ";
		?>
		<a href="editsingleskill.php?id=<?php echo $skillID; ?>">
		<img src='graphics/tool.png' /></a> 
		<?php
		if ($skillID != 2) {
			//delete button, only if not skillgroup "band", because of its hardcoded special status (band members)
			echo "<a href='editSkills.php?skillremove=true&skillID=" . $skillID . "'><img src='graphics/close.png' /></a><br />"; 
		}
	 } ?>
     </fieldset>
     <input type="submit" value="Update groups" /></p>
     </form>
	 
</div>
<? 
if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="settings.php">Back to settings</a></div>
		<div class="item"><a href="viewUsers.php">View all users</a></div>
</div>
<? } ?>
<? include('includes/footer.php'); ?>