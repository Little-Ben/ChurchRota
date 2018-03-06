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

/*
function _activateSkill($skillID) {
	$query = "DELETE FROM cr_skills WHERE skillID = '$skillID'";
	mysql_query($query) or die(mysql_error());
} 

function _activatePost($postid) {
	$query = "DELETE FROM cr_discussion WHERE id = '$postid'";
	mysql_query($query) or die(mysql_error());
} 

function _activateCategory($areaid) {
	$query = "DELETE FROM cr_discussionCategories WHERE id = '$areaid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: discussion.php");
} 

function _activateDiscussion($areaid) {
	$query = "DELETE FROM cr_discussion WHERE id = '$areaid'";
	mysql_query($query) or die(mysql_error());
	$query = "DELETE FROM cr_discussion WHERE topicParent = '$areaid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: discussion.php");
} 

function _activateeventtype($eventtypeid) {
	$query = "DELETE FROM cr_eventTypes WHERE id = '$eventtypeid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: editeventtype.php");
} 
*/

function activateLocation($location,$state) {
	$query = "UPDATE cr_locations set active = $state WHERE id = '$location'";
	mysql_query($query) or die(mysql_error());
	header ("Location: locations.php");
} 

/*
function _activateUser($userID) {
	$query = "DELETE FROM cr_users WHERE id = '$userID'";
	mysql_query($query) or die(mysql_error());
	$query = "DELETE FROM cr_skills WHERE userID = '$userID'";
	mysql_query($query) or die(mysql_error());
	header ( "Location: viewUsers.php" );
} 

function _activateSkillGroups($skillID) {
	if ($skillID != 2)  // 2: special group, hardcoded funcionality for band and its members, can't be deleted
	{
		$query = "DELETE FROM cr_groups WHERE groupID = '$skillID'";
		mysql_query($query) or die(mysql_error());
		
		$query = "DELETE FROM cr_skills WHERE groupID = '$skillID'";
		mysql_query($query) or die(mysql_error());
	}
	header ("Location: editSkills.php");
} 


function _activateBandMembers($bandMembersID) {
	$query = "DELETE FROM cr_bandMembers WHERE bandMembersID = '$bandMembersID'";
	mysql_query($query) or die(mysql_error());
} 

function _activateResource($id) {
	$query = "DELETE FROM cr_documents WHERE id = '$id'";
	mysql_query($query) or die(mysql_error());
} 


function _activateEventMemberSkill($groupID, $userID) {
	$query = "DELETE FROM cr_skills WHERE groupID = '$groupID' AND userID = '$userID'";
	mysql_query($query) or die(mysql_error());
} 

function _activateEventPeople($removeEventID, $removeSkillID) {
	$query = "UPDATE cr_eventPeople SET deleted = 1 WHERE eventID = '$removeEventID' AND skillID = '$removeSkillID'";
	mysql_query($query) or die(mysql_error());
} 

function _activateEvent($removeWholeEvent) {
	$query = "UPDATE cr_events SET deleted = 1 WHERE id = '$removeWholeEvent'";
	mysql_query($query) or die(mysql_error());
} 

function _activateBand($removeBand) {
	$query = "DELETE FROM cr_bands WHERE bandID = '$removeBand'";
	mysql_query($query) or die(mysql_error());
} 

function _activateBandSkill($bandskillid) {
	$query = "DELETE FROM cr_instruments WHERE id = '$bandskillid'";
	mysql_query($query) or die(mysql_error());
	header ("Location: bandskills.php");
} 
*/

?>
