<?php 
 // you have to open the session first 
 session_start(); 
 
 //remove all the variables in the session 
 session_unset(); 
 
 // destroy the session 
 session_destroy();  
 
 header ( "Location: index.php");
 ?> 