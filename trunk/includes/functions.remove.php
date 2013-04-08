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

function removeSkill($skillID) {
	$query = "DELETE FROM cr_skills WHERE skillID = '$skillID'";
	mysql_query($query) or die(mysql_error());
} 

function removePost($postid) {
	$query = "DELETE FROM cr_discussion WHERE id = '$postid'";
	mysql_query($query) or die(mysql_error());
} 

function removeCategory($areaid) {
	$query = "DELETE FROM cr_discussionCategories WHERE id = '$areaid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: discussion.php");
} 

function removeDiscussion($areaid) {
	$query = "DELETE FROM cr_discussion WHERE id = '$areaid'";
	mysql_query($query) or die(mysql_error());
	$query = "DELETE FROM cr_discussion WHERE topicParent = '$areaid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: discussion.php");
} 

function removeeventtype($eventtypeid) {
	$query = "DELETE FROM cr_eventTypes WHERE id = '$eventtypeid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: editeventtype.php");
} 

function removelocation($location) {
	$query = "DELETE FROM cr_locations WHERE id = '$location'";
	mysql_query($query) or die(mysql_error());
	header ("Location: locations.php");
} 

function removeUser($userID) {
	$query = "DELETE FROM cr_users WHERE id = '$userID'";
	mysql_query($query) or die(mysql_error());
	$query = "DELETE FROM cr_skills WHERE userID = '$userID'";
	mysql_query($query) or die(mysql_error());
	header ( "Location: viewUsers.php" );
} 

function removeSkillGroups($skillID) {
	$query = "DELETE FROM cr_groups WHERE groupID = '$skillID'";
	mysql_query($query) or die(mysql_error());
	
	$query = "DELETE FROM cr_skills WHERE groupID = '$skillID'";
	mysql_query($query) or die(mysql_error());
	header ("Location: editSkills.php");
} 


function removeBandMembers($bandMembersID) {
	$query = "DELETE FROM cr_bandMembers WHERE bandMembersID = '$bandMembersID'";
	mysql_query($query) or die(mysql_error());
} 

function removeResource($id) {
	$query = "DELETE FROM cr_documents WHERE id = '$id'";
	mysql_query($query) or die(mysql_error());
} 

function removeEventPeople($removeEventID, $removeSkillID) {
	$query = "DELETE FROM cr_eventPeople WHERE eventID = '$removeEventID' AND skillID = '$removeSkillID'";
	mysql_query($query) or die(mysql_error());
} 

function removeEvent($removeWholeEvent) {
	$query = "DELETE FROM cr_events WHERE id = '$removeWholeEvent'";
	mysql_query($query) or die(mysql_error());
} 

function removeBand($removeBand) {
	$query = "DELETE FROM cr_bands WHERE bandID = '$removeBand'";
	mysql_query($query) or die(mysql_error());
} 
?>