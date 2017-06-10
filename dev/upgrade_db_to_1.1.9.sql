ALTER TABLE timesheet_config 
ADD COLUMN useLDAP TINYINT NOT NULL default 0;

ALTER TABLE timesheet_config 
ADD COLUMN LDAPScheme varchar(32);

ALTER TABLE timesheet_config 
ADD COLUMN LDAPHost varchar(255);

ALTER TABLE timesheet_config 
ADD COLUMN LDAPPort INTEGER;

ALTER TABLE timesheet_config 
ADD COLUMN LDAPBaseDN varchar(255);

ALTER TABLE timesheet_config 
ADD COLUMN LDAPUsernameAttribute varchar(255);

ALTER TABLE timesheet_config 
ADD COLUMN LDAPSearchScope enum('base', 'sub', 'one') NOT NULL default 'base';

ALTER TABLE timesheet_config 
ADD COLUMN LDAPFilter varchar(255);

ALTER TABLE timesheet_config
ADD COLUMN weekstartday TINYINT NOT NULL default 0;