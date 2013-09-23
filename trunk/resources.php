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

// Handle details from the header 
$id = $_GET['id'];
$action = $_GET['action'];
$removeresource = $_GET['removeresource'];
$editableaction = $_POST['editableaction'];

// Method to remove  someone from the band
if($removeresource != "") {
	removeResource($id);
}

// If the form has been sent, we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$resourcename = $_POST['resourcename'];
	$resourcelink = $_POST['resourcelink'];
	$resourcedescription = $_POST['resourcedescription'];
	
	if($action == "editsent") { 
		$editid = $_POST['id'];
		$type = $_POST['type'];
		$editid = str_replace("title", "", $editid);
		
		$sql = "UPDATE cr_documents SET title = '$resourcename', description = '$resourcedescription', link = '$resourcelink' WHERE id = '$id'";
		
		if (!mysql_query($sql))
 	 		{
  				die('Error: ' . mysql_error());
  			}
		
	} else {
		if ($_FILES['resourcefile']['tmp_name'] == "none") {
		
		} else {
			$filename = $_FILES['resourcefile']['name'];
			copy ($_FILES['resourcefile']['tmp_name'], "./documents/".$_FILES['resourcefile']['name']);    
   		} 
	
	
		
		$sql = ("INSERT INTO cr_documents (title, description, url, link) VALUES ('$resourcename', '$resourcedescription', '$filename', '$resourcelink')");
		if (!mysql_query($sql))
 	 		{
  				die('Error: ' . mysql_error());
  			}
		// After we have inserted the data, we want to head back to the main page
	 
	}
	header('Location: resources.php'); 
      exit;
}
$formatting = "true";
$sendurl = "resources.php";
include('includes/header.php');
if($action == "new" || $action == "edit") {
	if($action == "new") {
		$actionlink = "resources.php?action=newsent";
	} else {
		$actionlink = "resources.php?action=editsent&id=" . $id;
		$sql = "SELECT * FROM cr_documents WHERE id = '$id'";
		$result = mysql_query($sql) or die(mysql_error());
	
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$resourcename = $row['title'];
			$resourcedescription = $row['description'];
			$resourcelink = $row['link'];
		}
	}
?>
<div class="elementBackground highlight">
	<h2><a name="addBand">Add a new resource:</a></h2>
	<form id="addEesource" method="post" action="<?php echo $actionlink; ?>" enctype="multipart/form-data">
				<fieldset>
					<label for="resourcename">Resource name:</label>
					<input id="resourcename" type="text" name="resourcename" value="<?php echo $resourcename; ?>" placeholder="Enter resource name:" />
					
					<?php if($action == "edit" && $resourcelink != "") { ?>
					<label for="resourcelink">Resource link:</label>
					<input id="resourcelink" type="text" name="resourcelink" value="<?php echo $resourcelink; ?>" placeholder="Enter resource link:" />
					
					<?php } else if($action == "edit" && $resourcelink == "") { ?>
					<p>Resource was a file upload. There is currently no way of editing this. Please delete and create anew.</p>
					<?php } else { ?>
					<label for="resourcefile">Upload:</label>
					<input id="resourcefile" type="file" name="resourcefile" />
					<?php } ?>
					
					<label for="resourcedescription">Resource description:</label>
					<textarea id="resourcedescription" type="text" class="noMCE" name="resourcedescription"><?php echo $resourcedescription; ?></textarea>
					
					
					
					<input type="submit" value="Add resource" />
				</fieldset>		
	</form>
</div>
<div id="right">
		<div class="item"><a href="resources.php">View all resources</a></div>
	</div>

<?	} else {
	$sql = "SELECT * FROM cr_documents ORDER BY title";
	$result = mysql_query($sql) or die(mysql_error());
	
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$resourceID = $row['id'];
		?>
		<div class="elementBackground">
			<?php if($row['url'] != "") {
				?><a href="documents/<?php echo $row['url']; ?>" target="_blank">
			<?php } else { ?>
				<a href="<?php echo $row['link']; ?>" target="_blank">
			<?php } ?>
			<h2 id="title<?php echo $resourceID; ?>">
				<?php echo $row['title']; ?>
				<?php if(isAdmin()) { 
				echo "<a href='resources.php?action=edit&id=$resourceID'><img src='graphics/tool.png' /></a> "; 
				echo "<a href='resources.php?removeresource=true&id=$resourceID'><img src='graphics/close.png' /></a>"; 
				} ?>
             </h2></a> 
               
			
			<div id="<?php echo $resourceID; ?>"><p><?php echo $row['description']; ?></p></div>
			
		</div>		
	
	<?php }
	if(isAdmin()) { ?>
		<div id="right">
		<div class="item"><a href="resources.php?action=new">Add a new resource</a></div>
		</div>
	<?php }
	
	
} ?>
<?php include('includes/footer.php'); ?>
