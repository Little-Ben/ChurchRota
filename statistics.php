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
$method = $_GET["method"];

// If the form has been submitted, then we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if($method == 'truncate') {
		$sql = "CREATE TABLE tmp_system_statistics as SELECT * from cr_statistics WHERE type='system'";
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}
		
		$sql = ("TRUNCATE TABLE cr_statistics");
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}
			
		$sql = ("ALTER TABLE cr_statistics  AUTO_INCREMENT = 50");
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}

		$sql = "INSERT INTO cr_statistics (userid,date,type,detail1,detail2,detail3,script) ";
		$sql = $sql . "SELECT userid,date,type,detail1,detail2,detail3,script from tmp_system_statistics order by date";
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}

		$sql = "DROP TABLE tmp_system_statistics";
		if (!mysql_query($sql))
 	 		{
  			die('Error: ' . mysql_error());
  			}

		insertStatistics("system",__FILE__,"statistics deleted");


		
		// After we have truncated the data, we want to reload the page
		header('Location: statistics.php'); // Move to the home page of the admin section
		exit;
		
	} else {
		
	}
}

if($method == 'showall') {
	$limit=" ";
	$browserLimit=" ";
}
else{
	$limit="LIMIT 10";
	$browserLimit="LIMIT 5";
}
	

include('includes/header.php');
?>

<div class="elementBackground">
		<h2>Church Rota Statistics:</h2>
				
		<p>
		<?php 
			if ($debug) {
				echo "<table class=\"statistics\">";
				echo "<thead>";
				echo "<tr><th >Browser / Platform</th><th>Count</th></tr>";
				echo "</thead>";
				echo "<tbody>";
				
					$sql = "SELECT getBrowserInfo(detail3) as browser,count(*) as count from cr_statistics where detail1 like 'login%' and detail3!='' group by getBrowserInfo(detail3) order by count desc ".$browserLimit;

					$result = mysql_query($sql) or die(mysql_error());
					while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
						extract($row);
						echo "<tr>";
						echo "<td>".$browser."</td>";
						echo "<td>".$count."</td>";
						echo "</tr>";
					}
				
				echo "</tbody>";
				echo "</table>";
			}
		?>
		<p>

		<table class="statistics">
		<thead>
		<tr><th>Date</th><th>User</th><th>Type</th><th>Action</th><th>Info</th></tr>
		</thead>
		<tbody>
		<?php
			$sql = "SELECT s.date,s.detail1,s.detail2,s.detail3,s.type,trim(concat(u.firstName,' ',u.lastName)) as name from cr_statistics s,cr_users u where u.ID=s.userID";
			if ($debug==false) $sql = $sql . " and s.type = 'system'";
			$sql = $sql . " ORDER BY date desc " . $limit;
			$result = mysql_query($sql) or die(mysql_error());
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
				extract($row);
				echo "<tr>";
				echo "<td>".$date."</td>";
				echo "<td>".$name."</td>";
				echo "<td>".$type."</td>";
				echo "<td>".$detail1."</td>";
				echo "<td>".$detail2."</td>";
				//echo "<td>".$detail3."</td>";
				echo "</tr>";
			}
		?>
		</tbody>
		</table>
		
		<a href="#" data-reveal-id="truncStatData" class="button">Delete User Statistics</a>

			<div id="truncStatData" class="reveal-modal">
     			<h1>Really delete user statistics?</h1>
				<p>Are you sure you really want to delete ALL user statistics data? <br>There is no way of undoing this action.</p>
				<p><form action="statistics.php?method=truncate" method="post" id="truncate">
				<input type="submit" value="Sure, delete statistics" /></form></p>
     			<a class="close-reveal-modal">&#215;</a>
			</div>
		
</div>
<?php 
if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="settings.php">Back to settings</a></div>
		<?php	if($method != "showall") { ?>
		<div class="item"><a href="statistics.php?method=showall">Show all statistics</a></div>
		<?php } else { ?>
		<div class="item"><a href="statistics.php">Show latest statistics</a></div>
		<?php } ?>
</div>
<?php } ?>
<?php include('includes/footer.php'); ?>