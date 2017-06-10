<?php
// $Header: /cvsroot/tsheet/timesheet.php/popup.php,v 1.11 2005/05/17 03:38:37 vexil Exp $

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

//load local vars from superglobals
$year = $_REQUEST["year"];
$month = $_REQUEST["month"];
$day = $_REQUEST["day"];
$destination = $_REQUEST["destination"];
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;

//get todays values
$today = time();
$todayYear = date("Y", $today);
$todayMonth = date("n", $today);
$todayDay = date("j", $today);

//check that the client id is valid
if ($client_id == 0 || empty($client_id))
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
<?php 
include("header.inc");
include("client_proj_task_javascript.inc");
?>
<script language="Javascript">

function resizePopupWindow() {
	//now resize the window
	var outerTable = document.getElementById('outer_table');
	var newWidth = outerTable.offsetWidth + window.outerWidth - window.innerWidth;
	var newHeight = outerTable.offsetHeight + window.outerHeight - window.innerHeight;
	window.resizeTo(newWidth, newHeight);
}

</script>
</HEAD>
<body style="margin: 0; padding: 0;" class="face_padding_cell" <? include ("body.inc"); ?> onload="doOnLoad();">
	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="outer_table">	
		<tr>
		<td width="100%" class="face_padding_cell">
					
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>			
			
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Clock On / Off
						</td>
						<td align="right" nowrap class="outer_table_heading">
							<? echo strftime("%A %B %d, %Y", mktime(0,0,0,$month, $day, $year)); ?>
						</td>												
					</tr>
				</table>
								
<!-- include the timesheet face up until the next start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<form action="action.php" method="post" name="addForm" id="theForm">
		<input type="hidden" name="year" value="<?echo $year; ?>">
		<input type="hidden" name="month" value="<?echo $month; ?>">
		<input type="hidden" name="day" value="<?echo $day; ?>">
		<input type="hidden" id="client_id" name="client_id" value="<?echo $client_id; ?>">
		<input type="hidden" id="proj_id" name="proj_id" value="<?echo $proj_id; ?>">
		<input type="hidden" id="task_id" name="task_id" value="<?echo $task_id; ?>">
		<input type="hidden" name="fromPopupWindow" value="true">
		<input type="hidden" name="origin" value="<? echo $_SERVER["PHP_SELF"]; ?>">
		<input type="hidden" name="destination" value="<? echo $destination; ?>">
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
																	<input type="radio" name="clock_on_radio" id="clock_on_radio_date" value="date" onclick="enableClockOn();" checked>
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
																<input type="radio" name="clock_on_radio" id="clock_on_radio_now" value="now" onclick="enableClockOn();">	now
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
	
</BODY>
</HTML>
