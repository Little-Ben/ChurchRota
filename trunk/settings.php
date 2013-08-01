<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2013 Benjamin Schmitt

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

// If the form has been sent, we need to handle the data.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$siteurl = $_POST['siteurl'];
	$notificationemail = $_POST['notificationemail'];
	$notificationemail = mysql_real_escape_string($notificationemail);
	$siteadminemail = $_POST['siteadminemail'];
	$siteadminemail = mysql_real_escape_string($siteadminemail);
	$norehearsalemail = $_POST['norehearsalemail'];
	$norehearsalemail = mysql_real_escape_string($norehearsalemail);
	$yesrehearsal = $_POST['yesrehearsal'];
	$yesrehearsal = mysql_real_escape_string($yesrehearsal);
	$newusermessage = $_POST['newusermessage'];
	$newusermessage = mysql_real_escape_string($newusermessage);
	$owner = $_POST['owner'];
	
	$lang_locale = $_POST['lang_locale'];
	$lang_locale = mysql_real_escape_string($lang_locale);
	//$event_sorting_latest = $_POST['event_sorting_latest'];
	//$event_sorting_latest = mysql_real_escape_string($event_sorting_latest);
		if(isset($_POST['event_sorting_latest'])) 
			{
				$event_sorting_latest = '1';
			}
		else
			{
	    		$event_sorting_latest = '0';
			}
	
	//$snapshot_show_two_month = $_POST['snapshot_show_two_month'];
	//$snapshot_show_two_month = mysql_real_escape_string($snapshot_show_two_month);
		if(isset($_POST['snapshot_show_two_month'])) 
			{
				$snapshot_show_two_month = '1';
			}
		else
			{
	    		$snapshot_show_two_month = '0';
			}

	//$snapshot_reduce_skills_by_group = $_POST['snapshot_reduce_skills_by_group'];
	//$snapshot_reduce_skills_by_group = mysql_real_escape_string($snapshot_reduce_skills_by_group);
		if(isset($_POST['snapshot_reduce_skills_by_group'])) 
			{
				$snapshot_reduce_skills_by_group = '1';
			}
		else
			{
	    		$snapshot_reduce_skills_by_group = '0';
			}

	//$logged_in_show_snapshot_button = $_POST['logged_in_show_snapshot_button'];
	//$logged_in_show_snapshot_button = mysql_real_escape_string($logged_in_show_snapshot_button);
		if(isset($_POST['logged_in_show_snapshot_button'])) 
			{
				$logged_in_show_snapshot_button = '1';
			}
		else
			{
	    		$logged_in_show_snapshot_button = '0';
			}

		if(isset($_POST['users_start_with_myevents'])) 
			{
				$users_start_with_myevents = '1';
			}
		else
			{
	    		$users_start_with_myevents = '0';
			}
			
			
	$time_format_long = $_POST['time_format_long'];
	$time_format_long = mysql_real_escape_string($time_format_long);
	$time_format_normal = $_POST['time_format_normal'];
	$time_format_normal = mysql_real_escape_string($time_format_normal);
	$time_format_short = $_POST['time_format_short'];
	$time_format_short = mysql_real_escape_string($time_format_short);
	$time_zone = $_POST['time_zone'];
	$time_zone = mysql_real_escape_string($time_zone);
	$google_group_calendar = $_POST['google_group_calendar'];
	$google_group_calendar = mysql_real_escape_string($google_group_calendar);
	$overviewemail = $_POST['overviewemail'];
	$overviewemail = mysql_real_escape_string($overviewemail);

	
	
		// Update the database rather than insert new values
		$sql = "UPDATE cr_settings SET siteurl = '$siteurl', notificationemail = '$notificationemail', adminemailaddress = '$siteadminemail', norehearsalemail = '$norehearsalemail', yesrehearsal = '$yesrehearsal', newusermessage = '$newusermessage', owner = '$owner',
		lang_locale='$lang_locale',
		event_sorting_latest='$event_sorting_latest',
		snapshot_show_two_month='$snapshot_show_two_month',
		snapshot_reduce_skills_by_group='$snapshot_reduce_skills_by_group',
		logged_in_show_snapshot_button='$logged_in_show_snapshot_button',
		users_start_with_myevents='$users_start_with_myevents',
		time_format_long='$time_format_long',
		time_format_normal='$time_format_normal',
		time_format_short='$time_format_short',
		time_zone='$time_zone',
		google_group_calendar='$google_group_calendar',
		overviewemail='$overviewemail'"
		;
	
	
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
	header('Location: settings.php');
} 

include('includes/header.php');
?>



<div class="elementBackground">
<form action="#" method="post" id="settings">
		<fieldset>
		<?
		$sql = "SELECT * FROM cr_settings";
		$result = mysql_query($sql) or die(mysql_error());
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		?>
			<div class="elementHead arrowwaiting">
				<h2><a name="administration">Administrative Infos</a></h2>
			</div>
			<div class="elementContent" >
				<label class="owner" for="siteurl">Enter your organisation name:</label>
				<input class="owner" name="owner" id="owner" type="text" value="<? echo $row['owner'];?>"  />
				
				<label class="settings" for="siteurl">Enter URL of your website:</label>
				<input class="settings" name="siteurl" id="siteurl" type="text" value="<? echo $row['siteurl'];?>"  />
				
				<label class="settings" for="siteadminemail">Enter your admin email address:</label>
				<input class="settings" name="siteadminemail" id="siteadminemail" type="text" value="<? echo $row['adminemailaddress'];?>"  />
			</div>
		</div>
		
		<div class="elementBackground">
			
			<div class="elementHead arrowwaiting">
				<h2><a name="email">Email Templates</a></h2>
			</div>
			<div class="elementContent" >
				
				<label class="settings" for="notificationemail">Enter the text you would like at the bottom of a notification email:</label>
				<textarea id="notificationemail" type="text" name="notificationemail"><? echo $row['notificationemail'];?></textarea>
				
				<label class="settings" for="norehearsalemail">Text to display for events with no rehearsal</label>
				<textarea id="norehearsalemail" type="text" name="norehearsalemail"><? echo $row['norehearsalemail'];?></textarea>
				
				<label class="settings" for="yesrehearsal">Text to display for advertising rehearsal</label>
				<textarea id="yesrehearsal" type="text" name="yesrehearsal"><? echo $row['yesrehearsal'];?></textarea>
				
				<label class="settings" for="newusermessage">Email to new users</label>
				<textarea id="newusermessage" type="text" name="newusermessage"><? echo $row['newusermessage'];?></textarea>
				
				<label class="settings" for="overviewemail">Email for monthly overview</label>
				<textarea id="overviewemail" type="text" name="overviewemail"><? echo $row['overviewemail'];?></textarea>
				
			</div>
		</div>
		
		<div class="elementBackground">
			<div class="elementHead arrowwaiting">
				<h2><a name="locale_timezone">Locale and Timezone</a></h2>
			</div>
			<div class="elementContent" >
				
				<label class="settings" for="lang_locale">Language locale (e.g. en_GB):</label>
				<input class="settings" name="lang_locale" id="lang_locale" type="text" value="<? echo $row['lang_locale'];?>"  />
				<label class="settings"><?php //echo setlocale(LC_ALL,null); ?></label>

				<label class="settings" for="time_format_long">Long time format (pattern see php strftime):</label>
				<input class="settings" name="time_format_long" id="time_format_long" type="text" value="<? echo $row['time_format_long'];?>"  />

				<label class="settings" for="time_format_normal">Standard time format (pattern see php  strftime):</label>
				<input class="settings" name="time_format_normal" id="time_format_normal" type="text" value="<? echo $row['time_format_normal'];?>"  />

				<label class="settings" for="time_format_short">Short time format (pattern see php strftime):</label>
				<input class="settings" name="time_format_short" id="time_format_short" type="text" value="<? echo $row['time_format_short'];?>"  />

				<label class="settings" for="time_zone">Time Zone (see php "List of Supported Timezones", e.g. Europe/London):</label>
				<input class="settings" name="time_zone" id="time_zone" type="text" value="<? echo $row['time_zone'];?>"  />
			</div>
		</div>
		
		<div class="elementBackground">
			<div class="elementHead arrowwaiting">
				<h2><a name="behaviour">Church Rota Behaviour</a></h2>
			</div>
			<div class="elementContent" >
					
				<label class="settings" for="event_sorting_latest">Event overview - show latest events first:</label>
				<input class="settings" name="event_sorting_latest" id="event_sorting_latest" type="checkbox" value="1" <? if($row['event_sorting_latest']=='1')  { echo 'checked="checked"'; } else if($row['event_sorting_latest'] == '0') { }?>  />
				
				<label class="settings" for="snapshot_show_two_month">Snapshot - show only current month (and following if current day>20):</label>
				<input class="settings" name="snapshot_show_two_month" id="snapshot_show_two_month" type="checkbox" value="1" <? if($row['snapshot_show_two_month']=='1')  { echo 'checked="checked"'; } else if($row['snapshot_show_two_month'] == '0') { }?>  />

				<label class="settings" for="snapshot_reduce_skills_by_group">Snapshot - show only skills up to user's max. used skill group:</label>
				<input class="settings" name="snapshot_reduce_skills_by_group" id="snapshot_reduce_skills_by_group" type="checkbox" value="1" <? if($row['snapshot_reduce_skills_by_group']=='1')  { echo 'checked="checked"'; } else if($row['snapshot_reduce_skills_by_group'] == '0') { }?>  />

				<label class="settings" for="logged_in_show_snapshot_button">Show button "Snapshot view" for users:</label>
				<input class="settings" name="logged_in_show_snapshot_button" id="logged_in_show_snapshot_button" type="checkbox" value="1" <? if($row['logged_in_show_snapshot_button']=='1')  { echo 'checked="checked"'; } else if($row['logged_in_show_snapshot_button'] == '0') { }?>  />

				<label class="settings" for="users_start_with_myevents">User starts with events filtered for "My Events":</label>
				<input class="settings" name="users_start_with_myevents" id="users_start_with_myevents" type="checkbox" value="1" <? if($row['users_start_with_myevents']=='1')  { echo 'checked="checked"'; } else if($row['users_start_with_myevents'] == '0') { }?>  />
			</div>
		</div>
		
		<div class="elementBackground">
			<div class="elementHead arrowwaiting">
				<h2><a name="ext_services">External Services</a></h2>
			</div>
			<div class="elementContent" >
			
				<label class="settings" for="google_group_calendar">Google Group Calendar ID (for admin snapshot, e.g.&nbsp;5vpkrij4fv8k011dcmt38rt7ik@group.calendar.google.com):</label>
				<input class="settings" name="google_group_calendar" id="google_group_calendar" type="text" value="<? echo $row['google_group_calendar'];?>"  />
			
			</div>
		</div>
		
		<div class="elementBackground">
			
			
		<?
		}
		?>
			
			
		<input type="submit" value="Update all" class="settings" />
		</fieldset>
	</form>

</div>
<div id="right">
	<div class="item"><a href="editeventtype.php">Edit event types</a></div>
	<div class="item"><a href="editSkills.php">Edit skills</a></div>
	<div class="item"><a href="bandskills.php">Edit band skills</a></div>
	<div class="item"><a href="locations.php">Edit Locations</a></div>
</div>
<? include('includes/footer.php'); ?>