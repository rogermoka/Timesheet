#!/bin/sh


TIMESHEET_NEW_VERSION="1.2.1";
DATETIME=`date +%Y-%m-%d_%H-%M-%S`
DB_BACKUP_FILE="timesheet-backup-${DATETIME}.sql";

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

echo "Welcome to the timesheet.php upgrade tool. This script will attempt to "
echo "upgrade your existing version of Timesheet.php. Timesheet.php has only been "
echo "tested under PHP4, MySQL, and Apache. Other configurations may work, and "
echo "if they do not, any efforts to get them to work would be appreciated."
echo ""
echo "This script will attempt to detect existing settings and the existing version of "
echo "timesheet.php, and then upgrade timesheet.php, whilst keeping your existing "
echo "data."
echo ""
echo "Please note that when upgrading you may lose your configuration details and"
echo "graphical templates. Please save these into a text file before upgrading so that"
echo "you can enter them afterwards."
echo ""
echo "Whilst this script has been tested and known to work on at least one machine "
echo "there is no guarantee it will be successfull on yours. This script will attempt "
echo "to backup the existing database into a file called '$DB_BACKUP_FILE'."
echo ""

# get the working dir of the existing timesheet.php
SUCCESS=0
until [ $SUCCESS = 1 ]
do	
	echo ""
	echo -n "Please enter the working directory of the version of timesheet.php "
	echo -n "you wish to upgrade (full path):"
	read INSTALL_DIR

	if [ ! -d $INSTALL_DIR ]; then
		echo "Directory '$INSTALL_DIR' not found."
	else
		SUCCESS=1					
	fi
done

CREDENTIALS_FILE="database_credentials.inc"
if [ ! -e $INSTALL_DIR/$CREDENTIALS_FILE ]; then
	CREDENTIALS_FILE="common.inc"
fi

# get the database name
DBNAME=`awk '/DATABASE_DB/ {print $0 }' $INSTALL_DIR/$CREDENTIALS_FILE | awk '{print $3}' | sed s/\"//g | sed s/\;//g`

# get the database host 
DBHOST=`awk '/DATABASE_HOST/ {print $0 }' $INSTALL_DIR/$CREDENTIALS_FILE | awk '{print $3}' | sed s/\"//g | sed s/\;//g`

# get the mysql user
DBUSER=`awk '/DATABASE_USER/ {print $0 }' $INSTALL_DIR/$CREDENTIALS_FILE | awk '{print $3}' | sed s/\"//g | sed s/\;//g`

# get the mysql pass
DBPASS=`awk '/DATABASE_PASS / {print $0 }' $INSTALL_DIR/$CREDENTIALS_FILE | awk '{print $3}' | sed s/\"//g | sed s/\;//g`

echo ""
echo "Using database '$DBNAME' on host '$DBHOST'"
echo "Using username '$DBUSER' and password '$DBPASS'"
echo "Press Enter..."
read asdf

#now test
mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < test.sql > /dev/null

if [ $? = 1 ]; then
	echo "There was an error accessing the database. Either the database doesn't exist, or your username/password is incorrect."
	exit 1;
fi

# backup the database
echo ""
echo -n "Backing up the database..."
echo ""
mysqldump -h $DBHOST -u $DBUSER --password=$DBPASS $DBNAME > $DB_BACKUP_FILE

if [ $? = 1 ]; then
	echo "There was an error backing up the database. Upgrade will not continue."
	exit 1;
fi

# get the table name prefix
TABLE_PREFIX=`awk '/CONFIG_TABLE/ {print $3 }' $INSTALL_DIR/table_names.inc | sed s/\"//g | sed s/\;//g | sed s/config//g`
echo "Table name prefix='$TABLE_PREFIX'"
echo "Press Enter..."
read asdf2

#get the config table name
#TIMESHEET_CONFIG_TABLE=`awk '/CONFIG_TABLE/ {print $0 }' $INSTALL_DIR/table_names.inc | awk '{print $3}' | sed s/\"//g | sed s/\;//g`
TIMESHEET_CONFIG_TABLE="${TABLE_PREFIX}config"

#get the version number
TIMESHEET_VERSION=`echo "SELECT version FROM $TIMESHEET_CONFIG_TABLE WHERE config_set_id='1'" | \
 mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS 2> /dev/null | \
 awk 'BEGIN {RS=$_}; {print $2}'`

echo "Existing Timesheet version='$TIMESHEET_VERSION'"
echo "Press Enter..."
read asdf3

if [ "$TIMESHEET_VERSION" \< "1.2.0" ]; then 

	#replace prefix and version timesheet_upgrade....sql.in
	sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g timesheet_upgrade_to_1.2.0.sql.in | sed s/__TIMESHEET_VERSION__/$TIMESHEET_NEW_VERSION/g > timesheet_upgrade_to_1.2.0.sql

	#drop the config table and insert defaults
	echo ""
	echo "Timesheet.php upgrade will now import the new configuration (1.2.0)"
	echo "into the $DBNAME database:"
	echo ""
	mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < timesheet_upgrade_to_1.2.0.sql
fi

echo -n "Due to changes to MySQL in version 4.1, the way that passwords are stored and "
echo -n "accessed has changed. There are 3 different functions and you must choose the "
echo -n "correct one according to your version of MySQl"
echo ""
echo -n "Your local version of mysql is:"
echo ""
mysql --version
echo ""
echo "Please select a password function:"
echo "   1: SHA1 (Use this for version 4.1 and later)"
echo "   2: PASSWORD (Use this for version below 4.1)"
echo "   3: OLD_PASSWORD (Select this one if you orignally installed timesheet on pre 4.1 versions)"
read PASSWORD_FUNCTION_NUMBER
                                                                                                                            
DBPASSWORDFUNCTION="OLD_PASSWORD"
if [ "$PASSWORD_FUNCTION_NUMBER" = "3" ]; then
        DBPASSWORDFUNCTION="OLD_PASSWORD"
fi
                                                                                                                            
if [ "$PASSWORD_FUNCTION_NUMBER" = "2" ]; then
        DBPASSWORDFUNCTION="PASSWORD"
fi

if [ "$TIMESHEET_VERSION" \< "1.2.1" ]; then 
	#now do the latest (1.2.1) changes
	#replace prefix and version timesheet_upgrade....sql.in
	sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g timesheet_upgrade_to_1.2.1.sql.in | sed s/__TIMESHEET_VERSION__/$TIMESHEET_NEW_VERSION/g > timesheet_upgrade_to_1.2.1.sql
	
	#drop the config table and insert defaults
	echo ""
	echo "Timesheet.php upgrade will now import the new configuration (1.2.1)"
	echo "into the $DBNAME database:"
	echo ""
	echo $DBHOST
	echo $DBUSER
	echo $DBNAME
	echo $DBPASS
	mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS < timesheet_upgrade_to_1.2.1.sql

	if [ $? = 1 ]; then
		echo "There was an error altering tables in the database. Please make sure the user $DBUSER " 
		echo "has ALTER TABLE privileges. Upgrade will not continue."
		exit 1;
	fi
fi

#set the version number in the config table
echo "UPDATE ${TABLE_PREFIX}config set version='$TIMESHEET_NEW_VERSION';" | mysql -h $DBHOST -u $DBUSER --database=$DBNAME --password=$DBPASS

#replace the DBNAME, DBUSER, and DBPASS in the database_credentials.inc.in file
sed s/__DBHOST__/$DBHOST/g database_credentials.inc.in | \
sed s/__DBNAME__/$DBNAME/g | \
sed s/__DBUSER__/$DBUSER/g | \
sed s/__DBPASSWORDFUNCTION__/$DBPASSWORDFUNCTION/g | \
sed s/__DBPASS__/$DBPASS/g > database_credentials.inc

#replace prefix in sample_data.sql.in
sed s/__TABLE_PREFIX__/$TABLE_PREFIX/g sample_data.sql.in > sample_data.sql

#create new directories
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
echo "Upgrade complete."
echo ""
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
echo "If you have any questions or comments, or would just like to say"
echo "thanks, please contact dominic@advancen.com"
echo ""
echo "If you find this program useful we ask that you make a donation to"
echo "help fund further development of timesheet.php for everyones "
echo "benefit. You can also sponsor specific changes for features which"
echo "you would like to see in timesheet.php."
echo ""

