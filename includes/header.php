<!--
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
-->
<!DOCTYPE html>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Church Rota - <?php echo $owner; ?></title>
	<link rel="stylesheet" type="text/css" href="includes/style.css" />
	<link rel="stylesheet" type="text/css" href="includes/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="css/reveal.css">
	<script src="includes/jquery.js" language="javascript" type="text/javascript"></script>
	<script src="includes/jquery.reveal.js" type="text/javascript"></script>
	<script type="text/javascript">
/*
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-23120342-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
*/
</script>
	<?php if(isset($formatting) && $formatting == "true") { ?>
	<script src="includes/churchrota.js" language="javascript" type="text/javascript"></script>
    <script src="includes/jquery.jeditable.js" language="javascript" type="text/javascript"></script>
	<script src="includes/jquery-ui.js" language="javascript" type="text/javascript"></script>
	<script src="includes/timepicker.js" language="javascript" type="text/javascript"></script>
	
	
	<script src="includes/tiny_mce/tiny_mce.js" type="text/javascript"></script>
	<script type="text/javascript" >
		tinyMCE.init({
			mode : "textareas",
			editor_deselector : "mceNoEditor",
			theme : "simple"   //(n.b. no trailing comma, this will be critical as you experiment later)
		});
		
	$(document).ready(function() {
		$('#date').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm:ss'
		});
		
		$('#accordion').accordion({active: false});
			
		$('#rehearsaldateactual').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm:ss'
		});
		
		$('.edit_area').editable('<?php if(isset($sendurl)) echo $sendurl; ?>', {
			type : 'textarea',
			cancel    : 'Cancel',
        	 submit    : 'OK',
			 tooltip   : 'Click to edit',
			"submitdata": function () {
			return {
				editableaction: 'edit'
				};
			},
			callback : function(value, settings) {
         		window.location.reload();
   			}
		});
		$('.edit').editable('<?php if(isset($sendurl)) echo $sendurl; ?>', {
			"submitdata": function () {
			return {
				editableaction: 'edit',
				type: 'title'
				};
			},
			callback : function(value, settings) {
         		window.location.reload();
   			}
		});
	});
</script >
	<?php } 
	
	else { ?>
	<script src="includes/jquery.confirm.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="includes/jquery.confirm.css" />
	<script type="text/javascript">
		$(document).ready(function()
		{
  			$(".elementContent").hide();
			<?php if(isset($hidefirst) && $hidefirst != true) { ?> 
			$(".elementContent:first").show()
			$('.elementHead:first').removeClass('arrowwaiting').addClass('arrowactive'); 
			<?php } ?>
  			$(".elementHead").click(function()
  			{
    			$(this).next(".elementContent").slideToggle(600);
				$(this).toggleClass("arrowwaiting",600);
				$(this).toggleClass("arrowactive",600);
  			});
			$('.elementHead').hover(function() {
 				$(this).css('cursor','pointer');
 				}, function() {
 				$(this).css('cursor','auto');
			});
			
			$('.delete').click(function(e) {
					
					e.preventDefault();
					thisHref	= $(this).attr('href');
					
					if($(this).next('div.question').length <= 0)
						$(this).after('<div class="question">Are you sure?<br/> <span class="yes">Yes</span><span class="cancel">Cancel</span></div>');
					
					$('.question').animate({opacity: 1}, 300);
					
					$('.yes').live('click', function(){
						window.location = thisHref;
					});
					
					$('.cancel').live('click', function(){
						$(this).parents('div.question').fadeOut(300, function() {
							$(this).remove();
						});
					});
					
				});

		});
		$(window).scroll(function() {
			if($(this).scrollTop() != 0) {
				$('#toTop').fadeIn();	
			} else {
				$('#toTop').fadeOut();
			}
		});
 
	$('#toTop').click(function() {
		$('document').animate({ scrollTop:0 }, '1000');
		return false;
	});
	
	</script>
	<?php } ?>
</head>
<body>
<div id="toTop">
<a href="#logo">Back to top</a>
</div>

<div id="container">
	<div id="header">
		<a href="index.php" id="logo"><img src="graphics/logo.jpg" alt="Church Rota Logo" width="263" height="48" /></a>
		<ul>
			<?php if(isLoggedIn()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='index.php'? 'class="active"' : '');?>><a  href="index.php">Home</a></li>
			
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='resources.php'? 'class="active"' : '');?> ><a href="resources.php">Resources</a></li>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='discussion.php'? 'class="active"' : '');?>
			<?php if(!isAdmin()) { ?><li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='addUser.php'? 'class="active"' : '');?>><a href="addUser.php?action=edit&id=<?php echo $_SESSION['userid']; ?>">My account</a></li><?php } ?>
			<?php }
			if(isAdmin()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='viewUsers.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='addUser.php'? 'class="active"' : ''); ?> ><a href="viewUsers.php">Users</a></li>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='settings.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='editeventtype.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='editSkills.php'? 'class="active"' : '');
			echo (basename($_SERVER['SCRIPT_FILENAME'])=='locations.php'? 'class="active"' : '');?>><a  href="settings.php">Settings</a></li>
			<?php }  ?>
			<?php if(isLoggedIn()) { ?>
			<li <?php echo (basename($_SERVER['SCRIPT_FILENAME'])=='logout.php'? 'class="active"' : '');?>><a  href="logout.php">Logout</a></li>
			<?php }  ?>
			
		</ul>
	</div>