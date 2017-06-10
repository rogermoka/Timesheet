<?
// $Header: /cvsroot/tsheet/timesheet.php/edit.php,v 1.9 2005/02/03 08:06:10 vexil Exp $
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

//load local vars from superglobals
$save_changes = isset($_REQUEST['save_changes']) ? $_REQUEST['save_changes']: false;
$task_id = $_REQUEST['task_id'];
$proj_id = $_REQUEST['proj_id'];
$client_id = $_REQUEST['client_id'];
$trans_num = $_REQUEST['trans_num'];
$month = $_REQUEST['month'];
$day = $_REQUEST['day'];
$year = $_REQUEST['year'];
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"]: "edit";

if ($action == "saveChanges") {
	$clock_on_date_year = $_REQUEST['clock_on_date_year'];
	$clock_on_date_month = $_REQUEST['clock_on_date_month'];
	$clock_on_date_day = $_REQUEST['clock_on_date_day'];
	$clock_off_date_year = $_REQUEST['clock_off_date_year'];
	$clock_off_date_month = $_REQUEST['clock_off_date_month'];
	$clock_off_date_day = $_REQUEST['clock_off_date_day'];
	$clock_on_time_hour = $_REQUEST['clock_on_time_hour'];
	$clock_on_time_min = $_REQUEST['clock_on_time_min'];
	$clock_off_time_hour = $_REQUEST['clock_off_time_hour'];
	$clock_off_time_min = $_REQUEST['clock_off_time_min'];
	$log_message = $_REQUEST['log_message'];

	$clock_on_time_string = "$clock_on_date_year-$clock_on_date_month-$clock_on_date_day $clock_on_time_hour:$clock_on_time_min:00";
	$clock_off_time_string = "$clock_off_date_year-$clock_off_date_month-$clock_off_date_day $clock_off_time_hour:$clock_off_time_min:00";

	$queryString = "UPDATE $TIMES_TABLE SET start_time='$clock_on_time_string', ".
								"end_time='$clock_off_time_string', ".
								"log_message='$log_message', ".
								"task_id='$task_id', " .
								"proj_id='$proj_id' " .
								"WHERE ".
								"trans_num='$trans_num'";

	list($qh,$num) = dbQuery($queryString);

	Header("Location: daily.php?proj_id=$proj_id&task_id=$task_id&month=$month&year=$year&day=$day");
	exit;
}

//define the command menu
include("timesheet_menu.inc");

//get trans info
$trans_info = get_trans_info($trans_num);

		//Due to a bug in mysql with converting to unix timestamp from the string, 
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$trans_info["start_time"] = strtotime($trans_info["start_time_str"]);
		$trans_info["end_time"] = strtotime($trans_info["end_time_str"]);

if ($action != "saveChanges") {
	$proj_id = $trans_info["proj_id"];
	$task_id = $trans_info["task_id"];
	$client_id = $trans_info["client_id"];
}

include("form_input.inc");

?>
<html>
<head>
<title>Edit Work Log Record for <? echo $contextUser; ?></title>
<?php 
include("header.inc");
include("client_proj_task_javascript.inc");
?>
</HEAD>
<BODY <? include ("body.inc"); ?> onload="doOnLoad();">
<?php include ("banner.inc"); ?>

<table width="500" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0" class="table_head">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Edit Work Log Record:
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">

		<form name="editForm" action="<? echo $_SERVER["PHP_SELF"]; ?>" method="post" id="theForm">
		<input type="hidden" name="year" value="<?echo $year; ?>">
		<input type="hidden" name="month" value="<?echo $month; ?>">
		<input type="hidden" name="day" value="<?echo $day; ?>">
		<input type="hidden" id="client_id" name="client_id" value="<?echo $client_id; ?>">
		<input type="hidden" id="proj_id" name="proj_id" value="<?echo $proj_id; ?>">
		<input type="hidden" id="task_id" name="task_id" value="<?echo $task_id; ?>">																										
		<input type="hidden" name="trans_num" value="<? echo $trans_num; ?>">
		<input type="hidden" name="action" value="saveChanges" />
		
		<tr>
			<td>			
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">				
					<tr>
						<td align="left">
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
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table>			
								<tr>
									<td>
										<table class="clock_on_box">											
											<tr>
												<td align="left" class="clock_on_text" nowrap>
													Start time:
												</td>
												<td align="left" nowrap>
													<? $hourInput = new HourInput("clock_on_time_hour"); 
														 $hourInput->create(date("G", $trans_info["start_time"])); ?>
													:
													<? $minuteInput = new MinuteInput("clock_on_time_min");
														 $minuteInput->create(date("i", $trans_info["start_time"])); ?>
													&nbsp;on&nbsp;
													<? $monthInput = new MonthInput("clock_on_date_month");
														 $monthInput->create(date("n", $trans_info["start_time"])); ?>
													,
													<? $dayInput= new DayInput("clock_on_date_day");
														 $dayInput->create(date("d", $trans_info["start_time"])); ?>
													&nbsp;							
													<input type="text" name="clock_on_date_year" size="4" value="<? echo date("Y", $trans_info["start_time"]); ?>">
												</td>
												<td align="left">
													<img src="images/clock-green-sml.gif">
												</td>
											</tr>
											<tr>
												<td align="left" class="clock_off_text" nowrap>
													End time:
												</td>
												<td align="left" nowrap>
													<? $hourInput = new HourInput("clock_off_time_hour"); 
														 $hourInput->create(date("G", $trans_info["end_time"])); ?>
															:
													<? $minuteInput = new MinuteInput("clock_off_time_min");
														 $minuteInput->create(date("i", $trans_info["end_time"])); ?>
													&nbsp;on&nbsp;
													<? $monthInput = new MonthInput("clock_off_date_month");
														 $monthInput->create(date("n", $trans_info["end_time"])); ?>
													,
													<? $dayInput= new DayInput("clock_off_date_day");
														 $dayInput->create(date("d", $trans_info["end_time"])); ?>
													&nbsp;							
													<input type="text" name="clock_off_date_year" size="4" value="<? echo date("Y", $trans_info["end_time"]); ?>">
												</td>					
												<td align="left">
													<img src="images/clock-red-sml.gif">
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="3">
										Log Message:
									</td>
								</tr>
								<tr>
									<td colspan="3" align="center">
										<textarea name="log_message" cols="60" rows="5" style="width: 100%;"><? echo trim(stripslashes($trans_info["log_message"])); ?></textarea>
									</td>
								</tr>	
								<tr>
									<td>
										<table width="100%" border="0" class="table_bottom_panel">
											<tr>
												<td align="center">
													<input type="button" value="Save Changes" name="submitButton" id="submitButton" onClick="onSubmit();">
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
	

<?php include ("footer.inc"); ?>
</body>
</html>
