<?
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

function checkBandSkill($description, $checkbox) {
	if($description == $checkbox) {
		echo "<label class='styled' for='band[" . $checkbox . "]'>" . $checkbox . "</em></label>
        <input class='styled' type='checkbox' id='band[" . $checkbox . "]'	checked='checked' name='band[]' 
		value='" . $checkbox . "' />";
	} else {
		echo "<label class='styled' for='band[" . $checkbox . "]'>" . $checkbox . "</em></label>
        <input class='styled' type='checkbox' id='band[" . $checkbox . "]'	name='band[]' 
		value='" . $checkbox . "' />";

	}
	
}

function addBandSkill($userid, $skillid) {
	$sql = ("INSERT INTO cr_skills (userID, groupID, skill) VALUES ('$userid', '2', '$skillid')");
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function deleteBandSkill($userid, $skillid) {
	$sql = "REMOVE FROM cr_skills WHERE userID = '$userid' AND skill = '$skillid'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function addOtherSkills($userid, $skillid) {
	$sql = ("INSERT INTO cr_skills (userID, groupID) VALUES ('$userid', '$skillid')");
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}

function removeOtherSkills($userid, $skillid) {
	$sql = "REMOVE FROM cr_skills WHERE userID = '$userid' AND groupID = '$skillid'";
	
	if (!mysql_query($sql))
 	 {
  		die('Error: ' . mysql_error());
  	}
}
?>