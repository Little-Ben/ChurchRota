<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2011 David Bunce, 2013 Benjamin Schmitt

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

function executeDbSql($sql) {
	if (!mysql_query($sql)) { 
		die('Error: ' . mysql_error() . ', SQL: ' . $sql); 
	}
}

function updateDatabase() {
	$sql = "show columns from cr_settings like 'version'";
	$result = mysql_query($sql) or die(mysql_error());
	$num_rows = mysql_num_rows($result);

	if ($num_rows > 0) {

		$sql = "select version from cr_settings";
		$result = mysql_query($sql) or die(mysql_error());
	
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
			$version = $row['version'];
		}
	}
	else
	{
			$version='unknown';
	}
		
	switch ($version) {
		case "unknown":
			executeDbSql("create table cr_settings_bkp_orig as select * from cr_settings");
			executeDbSql("alter table cr_events add(deleted varchar(2) default '0')"); 
			executeDbSql("alter table cr_eventPeople add(deleted varchar(2) default '0')"); 
			executeDbSql("alter table cr_settings add(version varchar(20))");
			
			executeDbSql("update cr_settings set version = '2.0.0'");
			break;
	}
	
}


?>