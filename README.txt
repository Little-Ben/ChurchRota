Installing Church Rota
======================

0) Check sourceforge for updates
   https://sourceforge.net/projects/churchrota/
   
   SVN (LATEST version!)
   http://churchrota.svn.sourceforge.net/viewvc/churchrota?view=revision&revision=HEAD
   
1) Setup a mysql database

2) Copy extracted files to your web server

3) Edit variables in 'includes/dbConfig.php'
   - $dbname
   - $username
   - $password
   Set the values according your mysql configuration
   
4) Open a web browser and call 'install.php'

5) Fill the form and enter complete and valid information
   this creates your admin user
   
6) Login to the system with your user datails and update 'Settings'

7) Your done - enjoy your new Church Rota system



Updating Church Rota
====================

IMPORTANT: 
Before updating a running installation,
be sure to have a backup of installation files AND database!!!

Each version-ZIP is a fully installion package.
So updating a running installation is simply extracting the ZIP to your 
installation folder.
Save your database config (includes/dbConfig.php) before extracting, 
otherwise it will be reseted!

If there are database updates, they will be executed when an admin 
user logs in. So this should be your first step after installing new updates.



Issue tracking
==============
Please use sourceforge's tracking system, if you encounter any issues 
(bugs, feature requests, etc.) related to Church Rota.

see https://sourceforge.net/tracker/?group_id=556037&source=navbar

