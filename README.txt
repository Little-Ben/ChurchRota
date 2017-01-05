
Installing ChurchRota
=====================

0) Check for updates

   Project: http://sourceforge.net/projects/churchrota/

   Stable Releases:
     Files: https://sourceforge.net/projects/churchrota/files/

   Source Code Management (latest but less tested code):
     We have moved our code base to GitHub.
     Please make sure to contribute only at that platform!

     GitHub (main place for code, issue tracking, pull requests, etc.):    
     https://github.com/Little-Ben/ChurchRota
   
     SF's Code GIT Repository (mirroring GitHub, no pull requests here please)
     https://sourceforge.net/p/churchrota/git/ci/master/tree/

     SF's SVN Repository (legacy, only for historic reasons, may be delete)
     http://sourceforge.net/p/churchrota/code/
   
1) Setup a mysql database

2) Copy extracted files to your web server

3) Edit variables in 'includes/dbConfig.php'
   - $dbname
   - $username
   - $password
   - $host (only if host provider tells you)
   Set the values according your mysql configuration
   
4) Open a web browser and call 'install.php'

5) Fill the form and enter complete and valid information
   this creates your admin user
   
6) Login to the system with your user datails and update 'Settings'

7) Your done - enjoy your new Church Rota system



Updating ChurchRota
===================

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
Please use GitHub's issue tracking system, if you encounter any issues 
(bugs, feature requests, etc.) related to ChurchRota.

see https://github.com/Little-Ben/ChurchRota/issues


For historic reasons our old ticket system is reachable here:
https://sourceforge.net/p/churchrota/tickets


Contributing
============
We are happy about new or improved features and encourage you to contribute to 
ChurchRota. Since development moved to GitHub, please make sure to contribute 
there (pull request, issues): 

https://github.com/Little-Ben/ChurchRota


Contact
=======
Please see Twitter user @ChurchRota_Dev for updates on ChurchRota.


Changelog
=========
see ./CHANGELOG.txt

