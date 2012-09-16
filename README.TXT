Unlock the potential of your server or hosting account by transforming it in your 
personal music streaming service.

*** Please only use it legally, and make sure you have actually paid for your songs ***

Before installing it make sure you have the Zend framework installed.

**** It needs to be version 1.12 ***


To Install please follow the instructions below:

1 - Create a Sub-domain for your music player, and point it to any directory on your server.

2 - Unzip the contents into that directory

3 - Create a mysql database

4 - Execute the music_db.sql in it, and create a new music player user in the user table, the password is plain text for the moment.

5- Create a directory outside the application away from public access, and use this directory to upload your tracks using your preferred method. (ftp/sftp etc)

6 - Modify applicationconfigs/application.ini so it matches your database details, and music directory path 

