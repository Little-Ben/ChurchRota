<?php

// Enter the name of your Database
$dbname = '';

// Enter the username
$username = '';

// Enter the password
$password = '';

// Unless your host tells you differently, this should remain as 'localhost'
$host = 'localhost';


// CONFIGURATION COMPLETE. Please upload files and navigate to install.php

// Connect to the database server
$dbh = @mysql_connect($host,$username,$password) or die ("Connection to $host with login '$username'/'$password' failed.");

// Choose the right database 
$db = @mysql_select_db($dbname, $dbh) or die ("Connection made. But database '$dbname' was not found.");



?>