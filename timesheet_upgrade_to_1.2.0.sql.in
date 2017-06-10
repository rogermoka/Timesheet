# MySQL dump
#
# Host: localhost    Database: timesheet
#--------------------------------------------------------

#
# Table structure for table 'config'
#

DROP TABLE IF EXISTS __TABLE_PREFIX__config;
CREATE TABLE __TABLE_PREFIX__config (
  config_set_id int(1) NOT NULL default '0',
  version varchar(32) NOT NULL default '__TIMESHEET_VERSION__',
  headerhtml mediumtext NOT NULL,
  bodyhtml mediumtext NOT NULL,
  footerhtml mediumtext NOT NULL,
  errorhtml mediumtext NOT NULL,
  bannerhtml mediumtext NOT NULL,
  tablehtml mediumtext NOT NULL,
  locale varchar(127) default NULL,
  timezone varchar(127) default NULL,
  timeformat enum('12','24') NOT NULL default '12',
  weekstartday TINYINT NOT NULL default 0,
  useLDAP tinyint(4) NOT NULL default '0',
  LDAPScheme varchar(32) default NULL,
  LDAPHost varchar(255) default NULL,
  LDAPPort int(11) default NULL,
  LDAPBaseDN varchar(255) default NULL,
  LDAPUsernameAttribute varchar(255) default NULL,
  LDAPSearchScope enum('base','sub','one') NOT NULL default 'base',
  LDAPFilter varchar(255) default NULL,
  PRIMARY KEY  (config_set_id)
) TYPE=MyISAM;

#
# Dumping data for table 'config'
#

INSERT INTO __TABLE_PREFIX__config VALUES (0,'__TIMESHEET_VERSION__','<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">','link=\"#004E8A\" vlink=\"#171A42\"','<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheet.php website: <A href=\"http://www.advancen.com/timesheet/\"><span \r\n\r\nclass=\"bottom_bar_text\">http://www.advancen.com/timesheet/</span></A>\r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>','<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>','<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" background=\"images/timesheet_background_pattern.gif\"><img src=\"images/timesheet_banner.gif\"></td></tr><tr>\r\n\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" width=\"1\" height=\"1\"></td></tr>\r\n</table>','','en_AU','Australia/Melbourne','12',1,0,'ldap','watson',389,'dc=watson','cn','base','');
INSERT INTO __TABLE_PREFIX__config VALUES (1,'__TIMESHEET_VERSION__','<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">','link=\"#004E8A\" vlink=\"#171A42\"','<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheet.php website: <A href=\"http://www.advancen.com/timesheet/\"><span \r\n\r\nclass=\"bottom_bar_text\">http://www.advancen.com/timesheet/</span></A>\r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>','<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>','<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" background=\"images/timesheet_background_pattern.gif\"><img src=\"images/timesheet_banner.gif\"></td></tr><tr>\r\n\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" width=\"1\" height=\"1\"></td></tr>\r\n</table>','','en_AU','Australia/Melbourne','12',1,0,'ldap','watson',389,'dc=watson','cn','base','');
INSERT INTO __TABLE_PREFIX__config VALUES (2,'__TIMESHEET_VERSION__','<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/questra/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">','link=\"#004E8A\" vlink=\"#171A42\"','</td><td width=\"2\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"2\" height=\"1\"></td></tr>\r\n<tr><td colspan=\"3\" style=\"background-color: #9494B7; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheet.php website: <A href=\"http://www.advancen.com/timesheet/\"><span \r\n\r\nclass=\"bottom_bar_text\">http://www.advancen.com/timesheet/</span></A>\r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>','<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>','<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n  <td style=\"padding-right: 15; padding-bottom: 8;\"><img src=\"images/questra/logo.gif\"></td>\r\n  <td width=\"100%\" valign=\"bottom\">\r\n    <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n      <tr><td colspan=\"3\" class=\"text_faint\" style=\"padding-bottom: 5;\" align=\"right\">You are logged in as %username%.</td></tr>\r\n      <tr>\r\n     <td background=\"images/questra/bar_left.gif\" valign=\"top\"><img src=\"images/questra/spacer.gif\" height=\"1\" width=\"8\"></td>\r\n        <td background=\"images/questra/bar_background.gif\" width=\"100%\" style=\"padding: 5;\">%commandmenu%</td>\r\n        <td background=\"images/questra/bar_right.gif\" valign=\"top\"><img src=\"images/questra/spacer.gif\" height=\"1\" width=\"8\"></td>\r\n      </tr>\r\n    </table>\r\n  </td>\r\n</tr></table>\r\n\r\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"3\" height=\"8\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"1\" height=\"8\"></td></tr><tr>\r\n<td width=\"2\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"2\" height=\"1\"></td>\r\n<td width=\"100%\" bgcolor=\"#F2F2F8\">','','en_AU','Australia/Melbourne','12',1,0,'ldap','watson',389,'dc=watson','cn','base','');
