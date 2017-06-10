<?php
// $Header: /cvsroot/tsheet/timesheet.php/admin_report_specific_project.php,v 1.10 2005/05/23 10:42:46 vexil Exp $

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
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	//get the first user from the database
	$uid = getFirstUser();

//define the command menu
include("timesheet_menu.inc");

if ($proj_id == 0)
	//get the first project
	$proj_id = getFirstProject();

	
// Set default months
setReportDate($year, $month, $day, $next_week, $prev_week, $next_month, $prev_month, $time, $time_middle_month);

function format_seconds($seconds) {
	$temp = $seconds;
	$hour = (int) ($temp / (60*60));
	
	if ($hour < 10)
		$hour = '0'. $hour;

	$temp -= (60*60)*$hour;    
	$minutes = (int) ($temp / 60);
  
	if ($minutes < 10)
		$minutes = '0'. $minutes;

	$temp -= (60*$minutes);    
	$sec = $temp;
	
	if ($sec < 10)
		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
															// be a space after the . operator for this to work.
	return "$hour:$minutes:$sec";
}
  
//get the project name
//	$query = "select title from $PROJECT_TABLE where proj_id='$proj_id'"; 
//	list($qh,$num) = dbQuery($query);
//	$data = dbResult($qh);
//	$project_title = $data['title'];	  
  
// Change the date-format for internationalization...
if ($mode == "all") $mode = "monthly";
if ($mode == "weekly") {
	$query = "select $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id, ".
			"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
			"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, $PROJECT_TABLE.title, $TASK_TABLE.name, ".
			"date_format(start_time, '%Y/%m/%d') as start_date, trans_num, $TIMES_TABLE.uid, ".
			"$USER_TABLE.first_name, $USER_TABLE.last_name, $TIMES_TABLE.log_message " .
			"from $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE ".
			"WHERE $TIMES_TABLE.uid=$USER_TABLE.username and end_time > 0 AND $TIMES_TABLE.proj_id='$proj_id' AND start_time >= '$year-$month-$day' AND ".
			"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND $TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
			"end_time < '".date("Y-m-d",$next_week)."' order by $USER_TABLE.uid, task_id, start_time";
} else {
	$query = "select $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id, ".
			"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
			"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, $PROJECT_TABLE.title, $TASK_TABLE.name, ".
			"date_format(start_time, '%Y/%m/%d') as start_date, trans_num, $TIMES_TABLE.uid, ".
			"$USER_TABLE.first_name, $USER_TABLE.last_name, $TIMES_TABLE.log_message " .
			"from $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE ".
			"WHERE $TIMES_TABLE.uid=$USER_TABLE.username and end_time > 0 AND $TIMES_TABLE.proj_id='$proj_id' AND start_time >= '$year-$month-1' AND ".
			"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND $TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
			"end_time < '".date("Y-m-1",$next_month)."' order by $USER_TABLE.uid, task_id, start_time";
}

//run the query  
list($qh,$num) = dbQuery($query);
  
//define working varibales  
$last_uid = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;

?>
<html>
<head>
<title>Timesheet.php Report: Hours for a specific project</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="admin_report_specific_project.php" method="get">
<input type="hidden" name="month" value="<? echo $month; ?>">
<input type="hidden" name="year" value="<? echo $year; ?>">
<input type="hidden" name="day" value="<? echo $day; ?>">
<input type="hidden" name="mode" value="<? echo $mode; ?>">


<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap>
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>											
									<td align="right" width="0" class="outer_table_heading">Project:</td>
									<td align="left" width="100%">
											<? project_select_droplist($proj_id); ?>
									</td>
								</tr>
							</table>
						</td>											
						<td align="center" nowrap class="outer_table_heading">
			  			<? 
							if ($mode == "weekly")
								echo date('F d, Y',$time); 
							else
								echo date('F Y',$time);
						?>
						</td>												
						<td align="right" nowrap>
						<?
							printPrevNext($time, $next_week, $prev_week, $next_month, $prev_month, $time_middle_month,"proj_id=$proj_id", $mode);
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
<?
	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>No hours recorded.<br><br></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	}
	else {
		while ($data = dbResult($qh)) {
			// New project, so print out last project total time and start a new table cell.
			if ($last_uid != $data["uid"]) {
				$last_uid = $data["uid"];
				if ($grand_total_time) {
					$formatted_time = format_seconds($total_time);
					print "<tr><td colspan=\"4\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
									"Total: <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				}
      
				print "<tr>\n\t<td colspan=\"4\" valign=\"top\" class=\"calendar_cell_disabled_right\">" .
							stripslashes("$data[first_name] $data[last_name]")."</td></tr>\n";
				$total_time = 0;
			}
			print "<tr>\n\t<td valign=\"top\" align=\"right\" width=\"50%\" class=\"calendar_cell_right\">\n\t";
			if ($last_task_id != $data["task_id"]) {
				$last_task_id = $data["task_id"];
				$current_task_name = stripslashes($data["name"]);
				print "\t<a href=\"javascript:void(0)\" onclick=\"javascript:window.open('task_info.php?task_id=$data[task_id]','Task Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=300,height=150')\">$current_task_name</a>\n\t";
			}
			print "&nbsp;</td>\n\t<td valign=\"top\" align=\"left\" width=\"8%\" class=\"calendar_cell_right\">$data[start_date]:&nbsp;&nbsp;</td>\n\t";
			print "</td>\n\t<td valign=\"top\" align=\"left\" class=\"calendar_cell_right\">";
			if ($data['log_message']) print stripslashes($data['log_message']);
			else print "&nbsp;";	
			print "</td>\n\t";
			print "<td valign=\"bottom\" align=\"right\" width=\"5%\" class=\"calendar_cell_right\">\n\t\t";
			print "<a href=\"javascript:void(0)\" onclick=\"javascript:window.open(" .
						"'trans_info.php?trans_num=$data[trans_num]'" . 
						",'Task Event Info','location=0,directories=no" .
						",status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">";
			print "&nbsp;&nbsp;$data[diff_time]<a>\n\t</td>\n</tr>\n";
			$total_time += $data["diff"];
			$grand_total_time += $data["diff"];
		}
  
		if ($total_time) {
			$formatted_time = format_seconds($total_time);
			print "<tr><td colspan=\"4\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							"Total: <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>";
		}
		$formatted_time = format_seconds($grand_total_time);
	}

?>
						</tr>
					</td>
				</table>
			</td>
		</tr>
<? 
	if ($num > 0)
	{
?>
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_bottom_panel">
					<tr>
						<td align="right" class="calendar_totals_line_monthly">
<?php
	if ($mode == "weekly")
		print "Weekly";
	else
		print "Monthly";
?>						
							total:
							<span class="calendar_total_value_monthly"><? echo $formatted_time; ?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
	}
?>
	</table>
	
<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>
		
</form>
<?
include ("footer.inc");
?>
</BODY>
</HTML>

