<?php
// $Header: /cvsroot/tsheet/timesheet.php/daily.php,v 1.7 2005/05/10 11:42:53 vexil Exp $

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn()) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

if (empty($contextUser))
	errorPage("Could not determine the context user");

//define the command menu
include("timesheet_menu.inc");

//check that the client id is valid
if ($client_id == 0)
	$client_id = getFirstClient();

//check that project id is valid
if ($proj_id == 0)
	$task_id = 0;
	
//calculate tomorrow and yesterday for "prev" & "next" buttons
$yesterday = mktime(0,0,0,$month,$day,$year) - 24*60*60;
$tomorrow = mktime(0,0,0,$month,$day,$year) + 24*60*60;

function getDailyTimes($month, $day, $year, $id, $proj_id) {
	include("table_names.inc");
	list($qhq, $numq) = dbQuery("select timeformat from $CONFIG_TABLE where config_set_id = '1'");
	$configData = dbResult($qhq);
		
	$query = "select date_format(start_time,'%d') as day_of_month, trans_num, ";
	
	if ($configData["timeformat"] == "12")
		$query .= "date_format(end_time, '%l:%i%p') as endd, date_format(start_time, '%l:%i%p') as start, ";
	else
		$query .= "date_format(end_time, '%k:%i') as endd, date_format(start_time, '%k:%i') as start, ";
		$query .= "unix_timestamp(end_time) - unix_timestamp(start_time) as diff_sec, " .
							"unix_timestamp(start_time) as start_time, " .
							"unix_timestamp(end_time) as end_time, " .
							"end_time as end_time_str, " .
							"start_time as start_time_str, ".
							"$PROJECT_TABLE.title as project_title, " .
							"$TASK_TABLE.name, $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id " .
							"FROM $TIMES_TABLE, $TASK_TABLE, $PROJECT_TABLE " .
							"WHERE $TASK_TABLE.proj_id=$PROJECT_TABLE.proj_id AND " .
							"uid='$id' AND ";
	
	$query .= "$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND " .
						"((start_time >= '$year-$month-$day 00:00:00' AND start_time <= '$year-$month-$day 23:59:59') " .
						" OR (end_time >= '$year-$month-$day 00:00:00' AND end_time <= '$year-$month-$day 23:59:59') " .
						" OR (start_time < '$year-$month-$day 00:00:00' AND end_time > '$year-$month-$day 23:59:59')) " .
						" order by day_of_month, start_time";

	list($my_qh, $num) = dbQuery($query);
		return array($num, $my_qh);
}


//include date input classes
include "form_input.inc";

list($num, $qh) = getDailyTimes($month, $day, $year, $contextUser, $proj_id);

?>
<html>
<head>
<title>Update timesheet for <? echo $contextUser; ?></title>
<?
include("header.inc");
include("client_proj_task_javascript.inc");
?>
<script language="Javascript">

	function delete_entry(transNum) {
				if (confirm('Are you sure you want to delete this time entry?'))
					location.href = 'delete.php?client_id=<?php echo $client_id; ?>&proj_id=<?php echo $proj_id; ?>&task_id=<?php echo $task_id; ?>&trans_num=' + transNum;
	}

</script>
</HEAD>
<BODY <? include ("body.inc"); ?> onload="doOnLoad();">
<?
include ("banner.inc");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Daily Timesheet
						</td>
						<td align="left" nowrap class="outer_table_heading">
							<? echo strftime("%A %B %d, %Y", mktime(0,0,0,$month, $day, $year)); ?>
						</td>
						<td align="right" nowrap>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?month=<? echo date("m",$yesterday); ?>&year=<? echo date("Y",$yesterday); ?>&day=<? echo date("d",$yesterday); ?>&proj_id=<? echo $proj_id; ?>" class="outer_table_action">Prev</a>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?month=<? echo date("m",$tomorrow); ?>&year=<? echo date("Y",$tomorrow); ?>&day=<? echo date("d",$tomorrow); ?>&proj_id=<? echo $proj_id; ?>" class="outer_table_action">Next</a>
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
						<td class="inner_table_column_heading" align="center">Project</td>
						<td class="inner_table_column_heading" align="center">Task</td>
						<td class="inner_table_column_heading" align="center">Start</td>
						<td class="inner_table_column_heading" align="center">End</td>
						<td class="inner_table_column_heading" align="center">Total</td>
						<td class="inner_table_column_heading" align="center"><i>Actions</i></td>
					</tr>
<?
if ($num == 0) {
	print "	<tr>\n";
	print "		<td class=\"calendar_cell_middle\"><i>No hours recorded.</i></td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_disabled_right\">&nbsp;</td>\n";
	print "	</tr>\n";
	print "</table>\n";
}
else {
	$last_task_id = -1;
	$today_total_sec = 0;
	$total_diff_sec = 0;
	
	while ($data = dbResult($qh)) {
		//Due to an inconsistency with mysql and php with converting to unix timestamp from the string, 
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$data["start_time"] = strtotime($data["start_time_str"]);
		$data["end_time"] = strtotime($data["end_time_str"]);

		//get the project title and task name
		$projectTitle = stripslashes($data["project_title"]);
		$taskName = stripslashes($data["name"]);
			
		//start printing details of the task
		print "<tr>\n";
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('proj_info.php?proj_id=$data[proj_id]','Project Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$projectTitle</a></td>\n";
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('task_info.php?task_id=$data[task_id]','Task Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=300,height=150')\">$taskName</a></td>\n";
		
		if ($data["diff_sec"] > 0) {
			//if both start and end time are not today
			if ($data["start_time"] < mktime(0,0,0,$month,$day,$year) && 
					$data["end_time"] > mktime(23,59,59,$month,$day,$year))
			{
				$today_diff_sec = 24*60*60; //all day - no one should work this hard!

				echo "<td class=\"calendar_cell_middle\" align=\"right\"><font color=\"#909090\"><i>" . $data["start"] . "," .
						 "<a href=\"daily.php?month=" . date("m",$data["start_time"]) .
						 "&year=" . date("Y",$data["start_time"]) .
						 "&day=" . date("d",$data["start_time"]) .
						 "&proj_id=$proj_id\"><i>" . date("d-M",$data["start_time"]) .
						 "</a></i></font>" .
						 "</td>" .
						 "<td class=\"calendar_cell_middle\" align=\"right\"><font color=\"#909090\"><i>" . $data["endd"] . "," .
						 "<a href=\"daily.php?month=" . date("m",$data["end_time"]) .
						 "&year=" . date("Y",$data["end_time"]) .
						 "&day=" . date("d",$data["end_time"]) .
						 "&proj_id=$proj_id\"><i>" . date("d-M",$data["end_time"]) .
						 "</a></i></font>" .
						 "</td>" .
						 "<td class=\"calendar_cell_middle\" align=\"right\">" . formatSeconds($today_diff_sec). "<font color=\"#909090\"><i> of " . 
						 		formatSeconds($data["diff_sec"]) . "</i></font></TD>\n";

				
			}		
			//if end time is not today
			elseif ($data["end_time"] > mktime(23,59,59,$month,$day,$year)) {
				$today_diff_sec = mktime(0,0,0, $month,$day,$year) + 24*60*60 - $data["start_time"];
			
				echo "<td class=\"calendar_cell_middle\" align=\"right\">" . $data["start"] . "</td>" .
						 "<td class=\"calendar_cell_middle\" align=\"right\"><font color=\"#909090\"><i>" . $data["endd"] . "," .
						 "<a href=\"daily.php?month=" . date("m",$data["end_time"]) .
						 "&year=" . date("Y",$data["end_time"]) .
						 "&day=" . date("d",$data["end_time"]) .
						 "&proj_id=$proj_id\"><i>" . date("d-M",$data["end_time"]) .
						 "</a></i></font>" .
						 "</td>" .
						 "<td class=\"calendar_cell_middle\" align=\"right\">" . formatSeconds($today_diff_sec). "<font color=\"#909090\"><i> of " . formatSeconds($data["diff_sec"]) . "</i></font></td>\n";
			}
			//elseif start time is not today
			elseif ($data["start_time"] < mktime(0,0,0,$month,$day,$year)) {
				$today_diff_sec = $data["end_time"] - mktime(0,0,0, $month,$day,$year); 
			
				echo "<td class=\"calendar_cell_middle\" align=\"right\"><font color=\"#909090\"><i>" . $data["start"] . "," .
						 "<a href=\"daily.php?month=" . date("m",$data["start_time"]) .
						 "&year=" . date("Y",$data["start_time"]) .
						 "&day=" . date("d",$data["start_time"]) .
						 "&proj_id=$proj_id\"><i>" . date("d-M",$data["start_time"]) .
						 "</a></i></font>" .
						 "</td>" .
				 		 "<td class=\"calendar_cell_middle\" align=\"right\">" . $data["endd"] . "</td>" .						
						 "<td class=\"calendar_cell_middle\" align=\"right\">" . formatSeconds($today_diff_sec). "<font color=\"#909090\"><i> of " . 
						 		formatSeconds($data["diff_sec"]) . "</i></font></td>\n";
			}
			else {
				$today_diff_sec = $data["diff_sec"];
				print "<td class=\"calendar_cell_middle\" align=\"right\">$data[start]</td>\n";
				print "<td class=\"calendar_cell_middle\" align=\"right\">$data[endd]</td>\n";
				print "<td class=\"calendar_cell_middle\" align=\"right\">" . formatSeconds($data["diff_sec"]) . "</td>\n";
			}

			print "<td class=\"calendar_cell_disabled_right\" align=\"right\">\n";
			print "	<a href=\"edit.php?client_id=$client_id&proj_id=$proj_id&task_id=$task_id&trans_num=$data[trans_num]&year=$year&month=$month&day=$day\" class=\"action_link\">Details</a>,&nbsp;\n";
			//print "	<a href=\"delete.php?client_id=$client_id&proj_id=$proj_id&task_id=$task_id&trans_num=$data[trans_num]\" class=\"action_link\">Delete</a>\n";
			print "	<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">Delete</a>\n";
			print "</td>";
			
			//add to todays total
			$today_total_sec += $today_diff_sec;
		}
		else {			
			print "<td class=\"calendar_cell_middle\" align=\"right\">$data[start]</td>\n";
			print "<td class=\"calendar_cell_middle\" align=\"right\">&nbsp;</td>\n";
			print "<td class=\"calendar_cell_middle\" align=\"right\">&nbsp;</td>\n";			
			print "<td class=\"calendar_cell_disabled_right\" align=\"right\">\n";
			print "	<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">Delete</a>\n";
			print "</td>";
		}
		
		print "</tr>";
		
	}
	print "<tr>\n";
	print "	<td class=\"calendar_totals_line_weekly_right\" colspan=\"5\" align=\"right\">";
	print " Daily Total: <span class=\"calendar_total_value_weekly\">" . formatSeconds($today_total_sec) . "</span></td>\n";
	print "	<td class=\"calendar_cell_disabled_right\" align=\"right\">&nbsp;</td>\n";
	print "</tr>\n";
	print "</table>";
} 
?>

			</td>		
		</tr>
	</table>
	

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

<table width="436" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
			
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>			
			
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Clock On / Off
						</td>
					</tr>
				</table>
								
<!-- include the timesheet face up until the next start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">

		<form action="action.php" method="post" name="addForm" id="theForm">
		<input type="hidden" name="destination" value="daily">
		<input type="hidden" name="year" value="<?echo $year; ?>">
		<input type="hidden" name="month" value="<?echo $month; ?>">
		<input type="hidden" name="day" value="<?echo $day; ?>">
		<input type="hidden" id="client_id" name="client_id" value="<?echo $client_id; ?>">
		<input type="hidden" id="proj_id" name="proj_id" value="<?echo $proj_id; ?>">
		<input type="hidden" id="task_id" name="task_id" value="<?echo $task_id; ?>">																										
		<input type="hidden" name="origin" value="<? echo $_SERVER["PHP_SELF"]; ?>">
		<input type="hidden" name="destination" value="<? echo $_SERVER["PHP_SELF"]; ?>">										

		<tr>
			<td>				
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">			
					<tr>
						<td>
							<table width="100%" border="0">
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Client:</td></tr></table></td>
													<td width="100%">
														<select id="clientSelect" name="clientSelect" onChange="onChangeClientSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																									
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Project:</td></tr></table></td>
													<td width="100%">
														<select id="projectSelect" name="projectSelect" onChange="onChangeProjectSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																		
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Task:</td></tr></table></td>
													<td width="100%">
														<select id="taskSelect" name="taskSelect" onChange="onChangeTaskSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																										
								<tr>
									<td>
										<table width="100%" border="0">
											<tr>								
												<td align="center">
													<table width="300" border="0" class="clock_on_box">
														<tr>										
															<td valign="top" align="left" class="clock_on_text">
																<input type="checkbox" name="clock_on_check" id="clock_on_check" onclick="enableClockOn();">Clock on at:
															</td>
															<td valign="middle">
																<? // If the current day is today:
																if (($year == date('Y')) && ($month == date('m')) && ($day == date('j'))): ?>
																	<input type="radio" name="clock_on_radio" value="date" id="clock_on_radio_date" onclick="enableClockOn();" checked>
																<? endif; ?>
																<? $hourInput = new HourInput("clock_on_time_hour");
																	 $hourInput->create(10); ?>
																:
																<? $minuteInput = new MinuteInput("clock_on_time_min");
																	 $minuteInput->create(); ?>
															</td>
															<td>
																<img src="images/clock-green-sml.gif" border="0">
															</td>
														</tr>								
														<? // If the current day is today:
														if (($year == date('Y')) && ($month == date('m')) && ($day == date('j'))): ?>
														<tr>
															<td>&nbsp;</td>
															<td valign="middle" align="left" class="clock_on_text">						
																<input type="radio" name="clock_on_radio" value="now" id="clock_on_radio_now" onclick="enableClockOn();">	now
															</td>
															<td>&nbsp;</td>
														</tr>
														<? endif; ?>
													</table>
												</td>
											</tr>
											<tr>
												<td align="center">
													<table width="300" border="0" class="clock_off_box">
														<tr>
															<td valign="top" align="left" class="clock_off_text">
																<input type="checkbox" name="clock_off_check" id="clock_off_check" onclick="enableClockOff();">Clock off at:
															</td>
															<td valign="middle">
																<? // If the current day is today:
																if (($year == date('Y')) && ($month == date('m')) && ($day == date('j'))): ?>
																	<input type="radio" name="clock_off_radio" id="clock_off_radio_date" value="date" onclick="enableClockOff();">
																<? endif; ?>
																<? $hourInput = new HourInput("clock_off_time_hour");
																	 $hourInput->create(17); ?>
																:
																<? $minuteInput = new MinuteInput("clock_off_time_min");
																	 $minuteInput->create(); ?>
															</td>
															<td>
																<img src="images/clock-red-sml.gif" border="0">
															</td>
														</tr>
														<? // If the current day is today:
														if (($year == date('Y')) && ($month == date('m')) && ($day == date('j'))): ?>
														<tr>
															<td>&nbsp;</td>
															<td valign="middle" align="left" class="clock_off_text">						
																<input type="radio" name="clock_off_radio" id="clock_off_radio_now" value="now" onclick="enableClockOff();" checked>now
															</td>
															<td>&nbsp;</td>
														</tr>
														<? endif; ?>
													</table>
												</td>
											</tr>
											<tr>
												<td align="center">
													<input type="button" value="Clock on and/or off" name="submitButton" id="submitButton" onClick="onSubmit();">
												</td>
											</tr>																												
										</table>
									</td>									
								</tr>
							</table>
						</td>					
					</tr>							
				</table>
			</td>
		</tr>
		</form>	
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
