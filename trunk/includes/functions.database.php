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
			//break;
			
		case "2.0.0":
			executeDbSql("update cr_settings set version = '2.0.1'");
			
		case "2.0.1":
			executeDbSql("update cr_settings set version = '2.0.2'");
		case "2.0.2":	
			executeDbSql("create table cr_settings_bkp2_0_2 as select * from cr_settings");
			
			executeDbSql("alter table cr_settings add(lang_locale varchar(20))");
			executeDbSql("alter table cr_settings add(event_sorting_latest int(1))");
			executeDbSql("alter table cr_settings add(snapshot_show_two_month int(1))");
			executeDbSql("alter table cr_settings add(snapshot_reduce_skills_by_group int(1))");
			executeDbSql("alter table cr_settings add(logged_in_show_snapshot_button int(1))");
			executeDbSql("alter table cr_settings add(time_format_long varchar(50))");
			executeDbSql("alter table cr_settings add(time_format_normal varchar(50))");
			executeDbSql("alter table cr_settings add(time_format_short varchar(50))");
			executeDbSql("alter table cr_settings add(users_start_with_myevents int(1))");
			executeDbSql("alter table cr_settings add(time_zone varchar(50))");
			executeDbSql("alter table cr_settings add(google_group_calendar varchar(100))");
			executeDbSql("alter table cr_users modify email varchar(255)");
			executeDbSql("alter table cr_settings add(overviewemail text NOT NULL)");
			executeDbSql("alter table cr_users add(isOverviewRecipient char(2) NOT NULL DEFAULT '0')");
			executeDbSql("alter table cr_groups add(short_name char(2))");
				
			executeDbSql("update cr_settings set lang_locale = 'en_GB'");					 // de_DE
			executeDbSql("update cr_settings set event_sorting_latest = 0");
			executeDbSql("update cr_settings set snapshot_show_two_month = 0");
			executeDbSql("update cr_settings set snapshot_reduce_skills_by_group = 0");
			executeDbSql("update cr_settings set logged_in_show_snapshot_button = 0");
			executeDbSql("update cr_settings set time_format_long = '%A, %B %e @ %I:%M %p'"); // de_DE: %A, %e. %B %Y, %R Uhr, KW%V
			executeDbSql("update cr_settings set time_format_normal = '%m/%d/%y %I:%M %p'"); // de_DE: %d.%m.%Y %H:%M 
			executeDbSql("update cr_settings set time_format_short = '%a, <strong>%b %e</strong>, %I:%M %p'");              // de_DE: %a, <strong>%e. %b</strong>, KW%V
			executeDbSql("update cr_settings set version = '2.1.0'");			
			executeDbSql("update cr_settings set users_start_with_myevents = 0");
			executeDbSql("update cr_settings set time_zone = 'Europe/London'"); //de_DE: Europe/Berlin
			executeDbSql("update cr_settings set google_group_calendar = ''"); 
			executeDbSql("update cr_settings set overviewemail = 'Hello,\r\n\r\nIn this email you find the Rota for [MONTH] [YEAR].\r\n\r\n[OVERVIEW]\r\n\r\nPlease inform us as soon as possible, if you are not able to serve as scheduled.\r\n\r\nBe blessed.\r\nChurch Support Stuff'"); 
			
			notifyInfo(__FILE__,"db-update=" . $version . "->2.1.0",$_SESSION['userid']);
				
			break;			
			
			
			
	}
	
}


?>