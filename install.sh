#!/bin/sh

TIMESHEET_NEW_VERSION="1.2.1";
TIMESHEET_LAST_VERSION="1.2.0";

echo "###################################################################"
echo "# Timesheet.php $TIMESHEET_NEW_VERSION (c) 1998-1999 Peter D. Kovacs               #"
echo "#                   (c) 2004 Dominic J. Gamble, Advancen          #"
echo "###################################################################"
echo "# This program is free software; you can redistribute it and/or   #"
echo "# modify it under the terms of the GNU General Public License     #"
echo "# as published by the Free Software Foundation; either version 2  #"
echo "# of the License, or (at your option) any later version.          #"
echo "# This program is distributed in the hope that it will be useful, #"
echo "# but WITHOUT ANY WARRANTY; without even the implied warranty of  #"
echo "# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   #"
echo "# GNU General Public License for more details.                    #"
echo "###################################################################"

echo "Welcome to the timesheet.php Installation. This script will attempt to "
echo "install timesheet.php onto your webserver. Timesheet.php has only been "
echo "tested under PHP4, MySQL, and Apache. Other configurations may work, and "
echo "if they do not, any efforts to get them to work would be appreciated."
echo ""
echo "If you want to upgrade from a previous version of timesheet.php, please "
echo "use the upgrade script (upgrade.sh). That script can upgrade versions "
echo "1.1 thru $TIMESHEET_LAST_VERSION to the current version ($TIMESHEET_NEW_VERSION)"
echo ""
echo -n "Press 'Enter' to continue installation, 'Ctrl-C' to cancel:"
read NOTHING

echo ""
echo -n "Please enter the hostname which the MySQL server is running on (localhost):"
read DBHOST
if [ "$DBHOST" = "" ]; then
	DBHOST="localhost"
	echo $DBHOST
fi

echo ""
echo "Due to changes to MySQL in version 4.1, the way that passwords are stored and "
echo "accessed has changed. There are 3 different functions and you must choose the "
echo "correct one according to your version of MySQl"
echo ""
echo "Your local version of mysql is:"
echo ""
mysql --version
echo ""
echo "Please select a password function:"
echo "   1: SHA1 (Use this for version 4.1 and later)"
echo "   2: PASSWORD (Use this for version below 4.1)"
echo "   3: OLD_PASSWORD (For versions above 4.1 when SHA1 fails)"
read PASSWORD_FUNCTION_NUMBER

DBPASSWORDFUNCTION="SHA1"
if [ "$PASSWORD_FUNCTION_NUMBER" = "3" ]; then
	DBPASSWORDFUNCTION="OLD_PASSWORD"
fi

if [ "$PASSWORD_FUNCTION_NUMBER" = "2" ]; then
	DBPASSWORDFUNCTION="PASSWORD"
fi

echo ""
echo "Timesheet.php can create its tables in an existing database, or can "
echo "create a new database called 'timesheet' to store its tables. If you "
echo "are installing timesheet.php onto a shared server, then it is likely "
echo "that you do not have permission to create a new database, but you have "
echo "an existing database which was set up for you by the system administrator."
echo ""

until [ "$NEWEXIST" = "n" -o "$NEWEXIST" = "N" -o "$NEWEXIST" = "e" -o "$NEWEXIST" = "E" ]
do
	echo -n "Create a NEW database or use an EXISTING database (n/e)?"
	read NEWEXIST
done

SUCCESS=0

if [ "$NEWEXIST" = "e" -o "$NEWEXIST" = "E" ]; then	
	until [ $SUCCESS = 1 ]
	do	
		echo -n "Please enter the name of the existing database:"
		read DBNAME
		echo ""
		echo "To add tables to your existing database, you must provide the username "
		echo "and password which you use to access it. This should have been set up "
		echo "for you by your system administrator."
		echo ""
		echo -n "$DBNAME MySQL username:"
		read DBUSER
		echo -n "$DBNAME MySQL password:"
		read DBPASS
	
		#now test
		mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < test.sql > /dev/null

		if [ $? = 1 ]; then
			SUCCESS=0
			echo "There was an error accessing the database. Either the database "
			echo "doesn't exist, or your username/password is incorrect."
		else
			SUCCESS=1
		fi
	done
else
	echo -n "Please enter the name of the new database:"
	read DBNAME
	if [ "$DBNAME" = "" ]; then
		DBNAME="timesheet"
		echo $DBNAME
	fi

	DBUSER=$DBNAME
	
	until [ $SUCCESS = 1 ]
	do				
		echo ""
		echo "To create a new database, you must provide the MySQL administrators "
		echo "username and password. This should have been set up when you installed "
		echo "MySQL. If you have forgotten it, please read "
		echo "	http://www.mysql.com/doc/R/e/Resetting_permissions.html "
		echo "for information on resetting the password."
		echo ""
		echo -n "MySQL Administrator username:"
		read MYSQLADMINUSER
		echo -n "MySQL Administator password:"
		read MYSQLADMINPASS
		echo ""
		echo "A new account will be created specifically for accessing the "
		echo "timesheet database. The username and password will be stored in "
		echo "the timesheet.php's configuration file 'database_credentials.inc'."
		echo ""
		echo -n "Please choose a password for the MySQL timesheet account:"
		read DBPASS

		#replace connection settings in the timesheet_create.sql.in file
		sed s/__DBHOST__/$DBHOST/g timesheet_create.sql.in | \
		sed s/__DBNAME__/$DBNAME/g | \
		sed s/__DBUSER__/$DBUSER/g | \
		sed s/__DBPASSWORDFUNCTION__/$DBPASSWORDFUNCTION/g | \
		sed s/__DBPASS__/$DBPASS/g > timesheet_create.sql
	
		#execute the script
		mysql -h $DBHOST -u $MYSQLADMINUSER --password=$MYSQLADMINPASS < timesheet_create.sql

		if [ $? = 0 ]; then
			SUCCESS=1
		else 
			SUCCESS=0
			echo ""
			echo "There was an error creating the database. "
			echo "Please check you have the correct username and password."
		fi
	done
fi
	
echo ""
echo "Timesheet.php prefixes all tables used with a string, so to avoid "
echo "name clashes with other tables in the database. This prefix is "
echo " normally 'timesheet_', however you can choose another string to "
echo "meet your requirements."
echo ""
echo -n "Table name prefix (timesheet_) ?"
read TABLE_PREFIX
if [ "$TABLE_PREFIX" = "" ]; then
	TABLE_PREFIX="timesheet_"
fi

#replace prefix and version timesheet.sql.in
sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g timesheet.sql.in | sed s/__TIMESHEET_VERSION__/$TIMESHEET_NEW_VERSION/g > timesheet.sql

#replace prefix in table_names.inc.in
sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g table_names.inc.in > table_names.inc

#replace prefix in sample_data.sql.in
sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g sample_data.sql.in > sample_data.sql

echo ""
echo "Timesheet.php installation will now create the necessary tables "
echo "in the $DBNAME database:"
echo ""
mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < timesheet.sql

if [ $? != 0 ]; then
	echo ""
	echo "An unexpected error occured when creating the tables. Please report this to dominic@advancen.com"
	exit 1;
fi
		
#replace the DBNAME, DBUSER, and DBPASS in the database_credentials.inc.in file
sed s/__DBHOST__/$DBHOST/g database_credentials.inc.in | \
sed s/__DBNAME__/$DBNAME/g | \
sed s/__DBUSER__/$DBUSER/g | \
sed s/__DBPASSWORDFUNCTION__/$DBPASSWORDFUNCTION/g | \
sed s/__DBPASS__/$DBPASS/g > database_credentials.inc

echo ""
echo -n "Where would you like timesheet installed (full path): "
read INSTALL_DIR
echo ""
if [ ! -d $INSTALL_DIR ]; then
	echo "Creating installation folder $INSTALL_DIR ..."
	echo ""
	mkdir -p $INSTALL_DIR
	if [ $? != 0 ]; then
		echo ""
		echo "install.sh: Could not create installation folder. Do you have the correct permissions?"
		echo ""
		exit 1
	fi
fi

if [ ! -d $INSTALL_DIR/css ]; then
	echo "Creating $INSTALL_DIR/css ..."
	echo ""
	mkdir $INSTALL_DIR/css
	if [ $? != 0 ]; then
		echo ""
		echo "There was an error creating the $INSTALL_DIR/css directory."
		echo "Do you have the correct permissions?"
		echo ""
		exit 1
	fi
fi

if [ ! -d $INSTALL_DIR/images ]; then
	echo "Creating $INSTALL_DIR/images ..."
	echo ""
	mkdir $INSTALL_DIR/images
	if [ $? != 0 ]; then
		echo ""
		echo "There was an error creating the $INSTALL_DIR/images directory."
		echo "Do you have the correct permissions?"
		echo ""
		exit 1
	fi
fi
	
echo ""
echo "Installing files..."
cp *.php *.inc *.html .htaccess $INSTALL_DIR
if [ $? != 0 ]; then
	echo ""
	echo "There were errors copying the files."
	echo "Do you have the correct permissions?"
	echo ""
	exit 1
fi
cp css/*.css $INSTALL_DIR/css/
if [ $? != 0 ]; then
	echo ""
	echo "There was an error copying the css files. "
	echo "Do you have the correct permissions?"
	echo ""
	exit 1
fi
cp images/*.gif $INSTALL_DIR/images
if [ $? != 0 ]; then
	echo ""
	echo "There was an error copying the image files."
	echo "Do you have the correct permissions?"
	echo ""
	exit 1
fi

echo ""
echo "An account must now be created with administrator privileges"
echo "to allow someone to login and configure the system."
echo ""
echo -n "Please enter a username for the account:"
read ADMIN_USER
echo -n "Please enter a password for the account:"
read ADMIN_PASS

echo -n "INSERT INTO $TABLE_PREFIX" > sql.tmp
echo -n "user VALUES ('$ADMIN_USER',10,$DBPASSWORDFUNCTION('" >> sql.tmp
echo -n $ADMIN_PASS >> sql.tmp
echo "'),'.*','Timesheet','Admin','','','0.00','','OUT','1');" >> sql.tmp
mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < sql.tmp

echo -n "INSERT INTO $TABLE_PREFIX" > sql.tmp
echo -n "assignments VALUES(1,'$ADMIN_USER');" >> sql.tmp
mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < sql.tmp

echo -n "INSERT INTO $TABLE_PREFIX" > sql.tmp
echo -n "task_assignments VALUES(1,'$ADMIN_USER', '1');" >> sql.tmp
mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < sql.tmp
rm sql.tmp

echo ""
echo "###################################################################"
echo "Be sure that your web server is set up to parse PHP 4 (or later)"
echo "files.  See the PHP documentation at http://www.php.net for more"
echo "information on how to do this."
echo ""
echo "If you are wanting to use LDAP for authentication then you will"
echo "need the php LDAP modules for apache, or LDAP compiled into your"
echo "Apache PHP module. For more information check the PHP documentation"
echo "at http://www.php.net"
echo ""
echo "Once that is done, point your browser to the installation and log"
echo "in as $ADMIN_USER with the password you gave above. "
echo ""
echo "If you have any questions or comments, or would just like to say"
echo "thanks, please contact dominic@advancen.com"
echo ""
echo "If you find this program useful we ask that you make a donation to"
echo "help fund further development of timesheet.php for everyones "
echo "benefit. You can also sponsor specific changes for features which"
echo "you would like to see in timesheet.php."
echo ""

