<?
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
$month = $_REQUEST['month'];
$day = $_REQUEST['day'];
$year = $_REQUEST['year'];
$client_id = $_REQUEST['client_id'];
$proj_id = $_REQUEST['proj_id'];
$task_id = $_REQUEST['task_id'];
$origin = $_REQUEST["origin"];
$destination = $_REQUEST["destination"];
$clock_on_time_hour = $_REQUEST['clock_on_time_hour'];
$clock_on_time_min = $_REQUEST['clock_on_time_min'];
$clock_off_time_hour = $_REQUEST['clock_off_time_hour'];
$clock_off_time_min = $_REQUEST['clock_off_time_min'];
$clockonoff = $_REQUEST['clockonoff'];

// create the command menu cancel option
$commandMenu->add(new TextCommand("Cancel", true, "$destination?client_id=$client_id&proj_id=$proj_id&task_id=$task_id&year=$year&month=$month&day=$day"));

?>
<html>
<head>
	<title>Clock off - Enter log message</title>
<?
include ("header.inc");
?>
</head>
<body <? include ("body.inc"); ?> >
<?
include ("banner.inc");
?>
<form action="action.php" method="post">
	<input type="hidden" name="origin" value="<?php echo $origin; ?>">
	<input type="hidden" name="destination" value="<?php echo $destination; ?>">
	<input type="hidden" name="clock_on_time_hour" value="<? echo $clock_on_time_hour; ?>">
	<input type="hidden" name="clock_off_time_hour" value="<? echo $clock_off_time_hour; ?>">
	<input type="hidden" name="clock_on_time_min" value="<? echo $clock_on_time_min; ?>">
	<input type="hidden" name="clock_off_time_min" value="<? echo $clock_off_time_min; ?>">
	<input type="hidden" name="year" value="<? echo $year ?>">
	<input type="hidden" name="month" value="<? echo $month; ?>">
	<input type="hidden" name="day" value="<? echo $day; ?>">
	<input type="hidden" name="client_id" value="<? echo $client_id; ?>">	
	<input type="hidden" name="proj_id" value="<? echo $proj_id; ?>">
	<input type="hidden" name="task_id" value="<? echo $task_id; ?>">
	<input type="hidden" name="clockonoff" value="<? echo $clockonoff; ?>">
	<input type="hidden" name="log_message_presented" value="1">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Enter Log Message
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>			
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Please Enter Log message: (max 255 characters)</td>
					</tr>					
					<tr>
						<td>
							<textarea name="log_message" cols="60" rows="4" style="width: 100%;"></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Done">
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
	
</form>

<?
include ("footer.inc");
?>
	</body>
</html>
