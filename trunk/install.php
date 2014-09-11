<?php
/*
	This file is part of Church Rota.
	
	Copyright (C) 2011 David Bunce

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
$holdQuery = true;
include('includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$sql = "CREATE TABLE IF NOT EXISTS `cr_bandMembers` (
  `bandMembersID` int(11) NOT NULL AUTO_INCREMENT,
  `bandID` int(4) NOT NULL DEFAULT '0',
  `skillID` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bandMembersID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=60";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_bands` (
  `bandID` int(11) NOT NULL AUTO_INCREMENT,
  `bandLeader` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`bandID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_discussion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topicParent` int(6) NOT NULL DEFAULT '0',
  `CategoryParent` int(6) NOT NULL DEFAULT '0',
  `userID` int(6) NOT NULL DEFAULT '0',
  `topic` text NOT NULL,
  `topicName` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=82";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
	
	$sql = "CREATE TABLE IF NOT EXISTS `cr_discussionCategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `parent` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "INSERT INTO `cr_discussionCategories` (`id`, `name`, `description`, `parent`) VALUES
(9, 'General Conversation', '', 0)";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}

		$sql = "CREATE TABLE IF NOT EXISTS `cr_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(127) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `url` varchar(127) NOT NULL DEFAULT '',
  `link` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}

	$sql = "CREATE TABLE IF NOT EXISTS `cr_eventPeople` (
  `eventPersonID` int(11) NOT NULL AUTO_INCREMENT,
  `eventID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `skillID` int(11) NOT NULL DEFAULT '0',
  `notified` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eventPersonID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2406";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_events` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rehearsalDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(50) NOT NULL DEFAULT '',
  `notified` int(2) NOT NULL DEFAULT '0',
  `rehearsal` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=43";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_eventTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `rehearsal` int(2) NOT NULL DEFAULT '0',
  `groupformat` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "INSERT INTO `cr_eventTypes` (`id`, `description`, `rehearsal`, `groupformat`) VALUES
(5, 'Morning Service', 1, 0)";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_groups` (
  `groupID` int(3) NOT NULL AUTO_INCREMENT,
  `description` varchar(25) NOT NULL DEFAULT '',
  `rehearsal` int(1) NOT NULL DEFAULT '0',
  `formatgroup` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=117";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
		$sql = "INSERT INTO `cr_groups` (`groupID`, `description`, `rehearsal`, `formatgroup`) VALUES
(2, 'Band', 1, 1),
(3, 'Rehearsal Tech', 1, 1),
(10, 'Preaching', 0, 3),
(11, 'Leading', 0, 3),
(12, 'Hosting', 0, 3),
(13, 'Setup', 0, 3),
(1, 'Worship Leader', 1, 1),
(4, 'Event Tech', 0, 1)";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_instruments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}

	$sql = "INSERT INTO `cr_instruments` (`id`, `name`) VALUES
(1, 'Acoustic Guitar'),
(2, 'Electric Guitar'),
(3, 'Vocals'),
(4, 'Keys'),
(5, 'Drums'),
(6, 'Percussion'),
(7, 'Cello'),
(8, 'Trumpet'),
(9, 'Bass')";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
		$sql = "CREATE TABLE IF NOT EXISTS `cr_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "INSERT INTO `cr_locations` (`id`, `description`) VALUES
(2, 'Holy Trinity Church Hall')";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}

	$sql = "CREATE TABLE IF NOT EXISTS `cr_settings` (
  `siteurl` text NOT NULL,
  `owner` text NOT NULL,
  `notificationemail` text NOT NULL,
  `adminemailaddress` text NOT NULL,
  `norehearsalemail` text NOT NULL,
  `yesrehearsal` text NOT NULL,
  `newusermessage` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "INSERT INTO `cr_settings` (`siteurl`, `owner`, `notificationemail`, `adminemailaddress`, `norehearsalemail`, `yesrehearsal`, `newusermessage`) VALUES
('http://example.com/rota', 'A church', 'Dear [name]\r\n\r\nThis is a message to remind you that you are on the rota for the service on [date] in [location]\r\n\r\n[rehearsal]\r\n \r\nThis is how you currently appear on the rota: \r\n \r\n[rotaoutput]\r\n \r\nIf you have arranged a swap, please let us know.\r\n\r\nMany thanks for your continued service!\r\nChurch Support Staff\r\n \r\nThis is an automatically generated email. To view the whole rota, please go to [siteurl] and login with your username [username]', 'info@examplerota.com', 'There will be no rehearsal. Please come at 9.30 on Sunday morning for setup and soundcheck.', 'There will be a rehearsal for this service', 'Dear [name]\r\n\r\nThis email contains important information for you because you are on one or more teams at the church.\r\n\r\nYou have been added as a new user to the Church Rota system at [siteurl].\r\n\r\nYour user login details are as follows:\r\nUsername: [username]\r\nPassword: [password]\r\n\r\nPlease make sure your contact details are correct. We also recommend you immediately change your password to something unique and memorable.\r\n\r\nHave a look around. It''s designed to be very simple to use. One of the key features is that you can click on \"Show only my events\" for a condensed view showing only those events where you are scheduled to do something. If nothing shows up - lucky you! Maybe you would like to volunteer for something new?\r\n\r\nIf you have any questions, please feel free to get in contact with us.\r\n\r\nMany thanks for your continued service!\r\nChurch Support Staff')";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
		$sql = "CREATE TABLE IF NOT EXISTS `cr_skills` (
  `skillID` int(6) NOT NULL AUTO_INCREMENT,
  `userID` int(3) NOT NULL DEFAULT '0',
  `groupID` int(3) NOT NULL DEFAULT '0',
  `skill` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`skillID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=530";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(6) NOT NULL DEFAULT '0',
  `categoryid` int(4) NOT NULL DEFAULT '0',
  `topicid` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=146";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$sql = "CREATE TABLE IF NOT EXISTS `cr_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(30) NOT NULL DEFAULT '',
  `lastName` varchar(30) NOT NULL DEFAULT '',
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `isAdmin` char(2) NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL DEFAULT '',
  `mobile` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=96";
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	$firstname = $_POST['firstname'];
	$firstname = strip_tags($firstname);
	$firstname = mysql_real_escape_string($firstname);
	$firstnameLower = strtolower($firstname);
	$lastname = $_POST['lastname'];
	$lastname = strip_tags($lastname);
	$lastname = mysql_real_escape_string($lastname);
	
	$lastnameLower = strtolower($lastname);
	
	$username = $firstnameLower.$lastnameLower;
	
	$password = $_POST['password'];
	$password = mysql_real_escape_string($password);
	$password = md5($password);
	
	$email = $_POST['email'];
	$email = strip_tags($email);
	$email = mysql_real_escape_string($email);
	
	$mobile = $_POST['mobile'];
	$mobile = strip_tags($mobile);
	$mobile = mysql_real_escape_string($mobile);
		
	$sql = "INSERT INTO cr_users (firstName, lastName, username, isAdmin, email, mobile, password)
	VALUES ('$firstname', '$lastname', '$username', '1', '$email', '$mobile', '$password')";
	
	if (!mysql_query($sql))
 	 	{
  		die('Error: ' . mysql_error());
  		}
		
	
	// after login we move to the main page
    header('Location: login.php?loginname='.$username); // Move to the home page of the admin section
    exit;
	
}

$formatting = "light";
include('includes/header.php'); 
?>

<div class="elementBackground">
<h2>Welcome to the Church Rota</h2>
<p>Thank you for choosing to install Church Rota. We have searched the database configuration files and have been able to connect,
so this is the last stage in the installation. Simply enter an administator username and password and you will be ready to go...</p>

<form action="install.php" method="post" id="addUser">
		<fieldset>
			<label for="firstname">First name:</label>
			<input name="firstname" id="firstname" type="text"  placeholder="Enter first name" />
			
			<label for="lastname">Last name:</label>
			<input name="lastname" id="lastname" type="text"  placeholder="Enter last name" />
			
			<label for="email">Email:</label>
			<input id="email" name="email" type="text" placeholder="Enter email address" />
			
			<label for="mobile">Mobile number:</label>
			<input id="mobile" name="mobile" type="text"  placeholder="Enter their mobile number" />
			
			<label for="password">Password:</label>
			<input id="password" name="password" type="password"  />
		</fieldset>
		<input type="submit" value="Let's go!" />
</form>
</div>



<div id="right">

</div>

<?php 
$owner='A Church';
$version='0.0.0';
include('includes/footer.php'); ?>