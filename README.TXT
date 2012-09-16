Thanks for downloading Host Player.

Before installing it make sure you have the Zend framework installed.


To Install please follow the instructions below:

1 - Create a Sub-domain for your music player, and point it to any directory on your server.

2 - Unzip the contents into that directory

3 - Create a mysql database and take not the database user

4 - Execute the music_db.sql in it, and create a new music player user in the user table, the password is plain text for the moment.

5- Create a directory outside the application (for enhanced security), you will use this directory to upload your tracks

6 - Modify applicationconfigs/application.ini so it matches your databse details, and music directory path 

7- Create a user for yourself, password is plain text (for now)