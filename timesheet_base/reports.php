<?
// $Header: /cvsroot/tsheet/timesheet.php/reports.php,v 1.5 2005/03/02 22:22:38 stormer Exp $

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']: $contextUser;

//define the command menu
include("timesheet_menu.inc");
  
// Set default months
setReportDate($year, $month, $day, $next_week, $prev_week, $next_month, $prev_month, $time, $time_middle_month); 

?>
<html>
<head><title>Timesheet.php Reports Page</title>
<?php include ("header.inc"); ?>
</head>
<body <? include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							Reports
						</td>
						<td align="left" nowrap class="outer_table_heading">
			  			<? echo date('F d, Y',$time) ?>
						</td>
						<td align="right" nowrap>
						<?
							printPrevNext($time, $next_week, $prev_week, $next_month, $prev_month, $time_middle_month,$uid);
						?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Report Description</td>
						<td class="inner_table_column_heading">Actions</td>
					</tr>				
					<tr>
						<td class="calendar_cell_middle">Hours worked by a specific user</td>
						<td class="calendar_cell_disabled_right">
							<a href="admin_report_specific_user.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=monthly">Generate monthly</a> /
							<a href="admin_report_specific_user.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=weekly">Generate weekly</a>
						</td>
					<tr>
					<tr>
						<td class="calendar_cell_middle">Hours worked on specific project</td>
						<td class="calendar_cell_disabled_right">
							<a href="admin_report_specific_project.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=monthly">Generate monthly</a> /
							<a href="admin_report_specific_project.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=weekly">Generate weekly</a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle">Hours worked for a specific client</td>
						<td class="calendar_cell_disabled_right">
							<a href="admin_report_specific_client.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=monthly">Generate monthly</a> /
							<a href="admin_report_specific_client.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=weekly">Generate weekly</a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle">Hours worked by all users on all projects</td>
						<td class="calendar_cell_disabled_right">
							<a href="admin_report_all.php?month=<? print $month; ?>&year=<? print $year; ?>&mode=monthly">Generate monthly</a>
						</td>
					</tr>															
				</table>
			</td>
		</tr>
	</table>
	
<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>
		
<?
include ("footer.inc");
?>
</BODY>
</HTML>
