<?php
// $Header: /cvsroot/tsheet/timesheet.php/admin_report_all.php,v 1.5 2005/05/23 10:42:46 vexil Exp $

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
$uid = isset($_REQUEST["uid"]) ? $_REQUEST["uid"]: 1;
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "username";
	
//define the command menu
include("timesheet_menu.inc");
   
// Calculate the previous month.
$next_month = $month + 1;
$next_year = $year;
$prev_month = $month - 1;
$prev_year = $year;

//rollover year forward
if (!checkdate($next_month, 1, $next_year)) {
	$next_month -= 12;
	$next_year ++;
}

//rollover year back
if (!checkdate($prev_month, 1, $prev_year)) {
    $prev_month += 12;
    $prev_year --;
}

?>
<html>
<head><title>Timesheet.php Report: All hours this month</title>
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
			  			<? echo date('F Y',mktime(0,0,0,$month,1,$year)) ?>
						</td>
						<td align="right" nowrap>
						<?
							print "<a href=\"$_SERVER[PHP_SELF]?uid=$uid&month=$prev_month&year=$prev_year\" class=\"outer_table_action\">Prev</a>&nbsp;";
			    		print "<a HREF=\"$_SERVER[PHP_SELF]?uid=$uid&month=$next_month&year=$next_year\" class=\"outer_table_action\">Next</a>";
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
						<td class="inner_table_column_heading">Name</td>
						<td class="inner_table_column_heading"><a href="<? echo $_SERVER["PHP_SELF"]; ?>?orderby=username&month=<? echo $month; ?>&year=<? echo $year; ?>" class="inner_table_column_heading">Username</a></td>
						<td class="inner_table_column_heading">Hours</td>
						<td class="inner_table_column_heading">
							<b><a href="<? echo $_SERVER["PHP_SELF"]; ?>?orderby=<? echo $PROJECT_TABLE; ?>.proj_id&month=<? echo $month; ?>&year=<? echo $year; ?>" class="inner_table_column_heading">Project</a>&nbsp;/&nbsp;
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?orderby=<? echo $TASK_TABLE; ?>.task_id&month=<? echo $month; ?>&year=<? echo $year; ?>" class="inner_table_column_heading">Task</a></b>
						</td>
					</tr>				
<?

	$query = "select distinct first_name, last_name, $USER_TABLE.username, $PROJECT_TABLE.title, $PROJECT_TABLE.proj_id, ".
					 "$TASK_TABLE.name, $TASK_TABLE.task_id ".
		"FROM $USER_TABLE, $PROJECT_TABLE, $TASK_TABLE, $ASSIGNMENTS_TABLE, $TASK_ASSIGNMENTS_TABLE WHERE ".
    "$ASSIGNMENTS_TABLE.proj_id = $PROJECT_TABLE.proj_id and $TASK_ASSIGNMENTS_TABLE.task_id = $TASK_TABLE.task_id ".
		"AND $PROJECT_TABLE.proj_id = $TASK_TABLE.proj_id AND ".
    "$ASSIGNMENTS_TABLE.username = $USER_TABLE.username and $USER_TABLE.username NOT IN ('admin','guest') ORDER BY $orderby";

	list ($qh,$num) = dbQuery($query);
	$last_username = "";

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>No hours recorded.<br><br></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	}
	else {
		while ($name_data = dbResult($qh)) {
			$query = "SELECT sec_to_time(sum(unix_timestamp(end_time) - unix_timestamp(start_time))) AS diff " .
				"FROM $TIMES_TABLE WHERE ".
      "start_time >= '$year-$month-1' AND end_time < '$next_year-$next_month-1' and end_time > 0 ".
      "and uid='$name_data[username]' AND task_id=$name_data[task_id] and proj_id=$name_data[proj_id]";
      
			list($qh2, $num2) = dbQuery($query);
			if ($num2 > 0)
				$time_data = dbResult($qh2);
    
			print "<tr>\n";
    
			if ($last_username != $name_data["username"]) {
				$last_username = $name_data["username"];
				print "<td class=\"calendar_cell_middle\">$name_data[first_name] $name_data[last_name]</TD>\n";
				print "<td class=\"calendar_cell_middle\"><A HREF=\"admin_report_specific_user.php?uid=$name_data[username]&month=$month&year=$year\">$name_data[username]</A></TD>\n";
			}
			else {
				print "<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
				print "<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
				}

			print "<td class=\"calendar_cell_middle\" align=\"center\">";
			if ($num2 > 0 && isset($time_data["diff"]))
				echo $time_data["diff"];
			else
				print "&nbsp;";
			print "</td>\n\n";
    
    $projectTitle = stripslashes($name_data["title"]);
    $taskName = stripslashes($name_data["name"]);
			print "<td class=\"calendar_cell_disabled_right\"><a href=\"javascript:void(0)\" ONCLICK=window.open(\"proj_info.php?proj_id=$name_data[proj_id]\",\"Info\",\"location=0,directories=no,status=no,menubar=no,resizable=1,scrollbar=yes,width=580,height=200\") class=\"outer_table_action\">$projectTitle</A> ".
          "<a href=\"javascript:void(0)\" ONCLICK=window.open(\"task_info.php?proj_id=$name_data[proj_id]&task_id=$name_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=580,height=220\")>$taskName</A></TD>\n";
			print "</tr>\n";
	}
}
?>
				</TABLE>
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
