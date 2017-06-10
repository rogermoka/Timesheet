# MySQL dump
#
# Host: localhost    Database: timesheet
#--------------------------------------------------------

#cleanup
use mysql;
DELETE FROM user WHERE User='__DBUSER__';
DELETE FROM db WHERE db='__DBNAME__';
DROP DATABASE IF EXISTS __DBNAME__;
CREATE DATABASE __DBNAME__;

#
# Now add a user with access to timesheets tables
#

use mysql;

INSERT INTO user (Host,User,Password)
VALUES('__DBHOST__','__DBNAME__',__DBPASSWORDFUNCTION__('__DBPASS__'));

INSERT INTO db(Host,Db,User,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Grant_priv,References_priv, Index_priv, Alter_priv)
VALUES('__DBHOST__','__DBNAME__','__DBUSER__','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y');

FLUSH PRIVILEGES;
