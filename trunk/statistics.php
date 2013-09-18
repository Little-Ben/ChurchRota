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

function detect($user_agent) 
{	
	////--------------------------------------------------------------------
	////code by blpgirl (Leyla Maria Bonilla Palacio, Columbia, http://loquelediga.com/) - found on http://snipplr.com/view/35381/
	////which references class 'browserdetect' by Paul Scott, South Africa - on http://www.phpclasses.org/browse/package/2827.html
	////allowed to use under Terms of GPL
	////--------------------------------------------------------------------
	
    //nota: firefox debe ir luego de mozilla pues el user agent tiene ambos
    //y flock debe ir luego de mozilla y firefox por la misma razon
    //chrome también debe ir despues de mozilla y safari porque los contiene a ambos
    //funciona porque siempre queda el valor de la última cadena encontrada
    $browser = array ("IE","OPERA","MOZILLA","NETSCAPE","FIREFOX","SAFARI", "FLOCK", "CHROME");
    $os = array ("WINDOWS","MAC","IPHONE","IPAD","ANDROID"); //// modiefied, was: ("WINDOWS","MAC","IPHONE");
    $info['browser'] = "OTHER";
    $info['os'] = "OTHER";
    ////$user_agent = $_SERVER['HTTP_USER_AGENT'];  ////modiefied, is given now by parameter
    //Por cada valor del array de navegadores
    foreach ($browser as $parent)
    {
		//con strtoupper devuelve la cadena en mayúsculas y con strpos devuelve la posicion de la cadena
		//Si no se encuentra la cadena, devuelve FALSE
		$s = strpos(strtoupper($user_agent), $parent);
		//el user agent siempre suelta: Navegador/NumVersion o Navegador NumVersion pa explorer
		//con esto se tiene la posicion para la version que es justo despues del navegador (s + tamaño nombre navegador)
		$f = $s + strlen($parent);
		//devuelve la cadena que empieza en el caracter f y termina en f+5
		$version = substr($user_agent, $f, 5);
		//reemplaza el numero, punto o / en la cadena version y lo reemplaza por vacio
		$version = preg_replace('/[^0-9,.]/','',$version);
		if (!($s===false)) ////modified, was: if ($s)
		{
			//como se encontro el navegador, se asignan los valores
			$info['browser'] = $parent;
			$info['version'] = $version;
		}
    }
    foreach ($os as $val)
    {
		//eregi encuentra subcadenas sin diferenciar mayusculas de minusculas
		if (eregi($val,strtoupper($user_agent))) $info['os'] = $val;
    }
	return $info;
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
				echo "<tr><th >Browser Identification</th><th>Count</th></tr>";
				echo "</thead>";
				echo "<tbody>";
				
					$sql = "SELECT detail3 as browser, count(*) as count from cr_statistics where detail1 like 'login%' and detail3!='' group by detail3 order by count desc ".$browserLimit;
					$result = mysql_query($sql) or die(mysql_error());
					while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
						extract($row);
						echo "<tr>";
						//echo "<td>".$browser."</td>";

						//$arrBrowser = get_browser($browser,true)
						//echo "<td>".$arrBrowser['parent']." on ".$arrBrowser['platform']."</td>";
						
						//http://snipplr.com/view/35381/detectar-browser/
						$d = detect($browser);
						echo "<td>".$d['browser']." ".$d['version']." on ".$d['os']."</td>";
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
<? 
if(isAdmin()) { ?>
<div id="right">
		<div class="item"><a href="settings.php">Back to settings</a></div>
		<?php	if($method != "showall") { ?>
		<div class="item"><a href="statistics.php?method=showall">Show all statistics</a></div>
		<? } else { ?>
		<div class="item"><a href="statistics.php">Show latest statistics</a></div>
		<? } ?>
</div>
<? } ?>
<? include('includes/footer.php'); ?>