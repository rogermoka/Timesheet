Timesheet.php

(c) 2017 Roger N. Mokarzel Filho
(c) 2004 Dominic J. Gamble, Advancen
(c) 1998-1999 Peter D. Kovacs

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.


Timesheet.php installation.

UNIX SYSTEMS WITH SHELL ACCESS

To install, run the script:
	
	install.sh

To upgrade from an earlier version, run the script:

	upgrade.sh


SERVERS WITHOUT SHELL ACCESS AND WINDOWS SYSTEMS

(Manual Installation)

1. Extract the distribution files locally, so that you can edit some of them before uploading to your web server

2a. Create a database on the mysql server. 


	This can be done manually if you know how, 
	or by running the script that comes with the distribution.

	First open the "timesheet_create.sql.in" file and replace the following occurrances:

	__DBUSER__ replace with the database username (one with privileges to create databases)
	__DBNAME__ the name of the database you want to create (e.g. "timesheet")
	__DBHOST__ the hostname which mysql is runnning on
	__DBPASS__ the password for the username which will access it

	Now rename it to "timesheet_create.sql"

script from the distribution. This script is just a list of SQL commands.
If you don't use this script, be sure to set the privileges on the new database.


2b. (Alternatively) Use an existing database.


3. Create the tables
	
	Open the "timesheet.sql.in" and replace allinstances of __TABLE_PREFIX__ 
	with a prefix you would like all tables to start with. This is done so that
	tables like "user" don't conflict with other tables you have in the same database.
	If you have no other tables you can just delete occurances of __TABLE_PREFIX__
	from the file.

	Now rename it to "timesheet.sql". It is just a set of SQL commands to be run from mysql
	or whatever interface you have to it. Make sure you run it on the right database.

4. Enter your database details into the "database_credentials.inc.in" file from the distribution. 
	
	The values you need to set are:

	$DATABASE_HOST - (The hostname of the database - usually 'localhost')
	$DATABASE_USER - (The username which you will connect to the database with)
	$DATABASE_PASS - (The password which you will connect to the database with)
	$DATABASE_DB - (The name of the database you created - usually 'timesheet')

	Now rename this fine to "database_credentials.inc"
 
5. Set the admin password for timesheet.php, by running the following SQL command:

	INSERT INTO user VALUES ('admin', 10, PASSWORD('mypassword'), '.*', 'Timesheet', 'Admin', '', '', '0.00', '', 'OUT', '1');

	Also run these:

	INSERT INTO assignments VALUES(1,'$ADMIN_USER');
	INSERT INTO task_assignments VALUES(1,'$ADMIN_USER', '1');

6. Upload the files to your web server.

7. Test the installation by logging in to 'login.php' with the username 'admin', and the password entered above.
