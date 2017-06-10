<?php
//$Header: /cvsroot/tsheet/timesheet.php/weekly.php,v 1.6 2005/05/23 05:39:39 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
require("class.Pair.php");
if (!$authenticationManager->isLoggedIn()) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

//define the command menu
include("timesheet_menu.inc");

// Check project assignment.
if ($proj_id != 0 && $client_id != 0) { // id 0 means 'All Projects'

	//make sure project id is valid for client. If not then choose another.
	if (!isValidProjectForClient($proj_id, $client_id)) {
		$proj_id = getValidProjectForClient($client_id);
	}
}
else
	$task_id = 0;
	

//a useful constant
define("A_DAY", 24 * 60 * 60);

//get the passed date (context date)
$todayDate = mktime(0, 0, 0,$month, $day, $year);
$todayYear = date("Y", $todayDate);
$todayMonth = date("n", $todayDate);
$todayDay = date("j", $todayDate);
$dateValues = getdate($todayDate);
$todayDayOfWeek = $dateValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = getWeekStartDay();

$daysToMinus = $todayDayOfWeek - $startDayOfWeek;
if ($daysToMinus < 0)
	$daysToMinus += 7;

//work out the start date by minusing enough seconds to make it the start day of week
$startDate = $todayDate - $daysToMinus * A_DAY;
$startYear = date("Y", $startDate);
$startMonth = date("n", $startDate);
$startDay = date("j", $startDate);

//work out the end date by adding 7 days
$endDate = $startDate + 7 * A_DAY;
$endYear = date("Y", $endDate);
$endMonth = date("n", $endDate);
$endDay = date("j", $endDate);

// Calculate the previous week
$previousWeekDate = $todayDate - A_DAY * 7;
$previousWeekYear = date("Y", $previousWeekDate);
$previousWeekMonth = date("n", $previousWeekDate);
$previousWeekDay = date("j", $previousWeekDate);

//calculate next week
$nextWeekDate = $todayDate + A_DAY * 7;
$nextWeekYear = date("Y", $nextWeekDate);
$nextWeekMonth = date("n", $nextWeekDate);
$nextWeekDay = date("j", $nextWeekDate);

//get the timeformat
list($qh2, $numq) = dbQuery("select timeformat from $CONFIG_TABLE where config_set_id = '1'");
$configData = dbResult($qh2);

//build the database query
$query = "SELECT date_format(start_time,'%d') AS day_of_month, ";
			
if ($configData["timeformat"] == "12")
	$query .= "date_format(end_time, '%l:%i%p') AS endd, date_format(start_time, '%l:%i%p') AS start, ";
else
	$query .= "date_format(end_time, '%k:%i') AS endd, date_format(start_time, '%k:%i') AS start, ";
				
$query .= "unix_timestamp(end_time) - unix_timestamp(start_time) AS diff_sec, ".
						"end_time AS end_time_str, ".
						"start_time AS start_time_str, ".
						"unix_timestamp(start_time) AS start_time, ".
						"unix_timestamp(end_time) AS end_time, ".
						"$PROJECT_TABLE.title AS projectTitle, " .
						"$TASK_TABLE.name AS taskName, " .
						"$TIMES_TABLE.proj_id, " .
						"$TIMES_TABLE.task_id, " .
						"$CLIENT_TABLE.organisation AS clientName " .
						"FROM $TIMES_TABLE, $TASK_TABLE, $PROJECT_TABLE, $CLIENT_TABLE WHERE " .
						"uid='$contextUser' AND ";
			
if ($proj_id > 0) //otherwise want all records no matter what project
	$query .=	"$TIMES_TABLE.proj_id=$proj_id AND ";
else if ($client_id > 0) //only records for projects of the given client
	$query .= "$PROJECT_TABLE.client_id=$client_id AND ";
			
$query .= 
				"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
				"$TASK_TABLE.proj_id = $PROJECT_TABLE.proj_id AND ".
				"$PROJECT_TABLE.client_id = $CLIENT_TABLE.client_id AND ".
			"((start_time >= '$startYear-$startMonth-$startDay 00:00:00' AND " .
			"start_time < '$endYear-$endMonth-$endDay 00:00:00') ".
			"OR (end_time >= '$startYear-$startMonth-$startDay 00:00:00' AND " .
			"end_time < '$endYear-$endMonth-$endDay 00:00:00') ".			
			"OR (start_time < '$startYear-$startMonth-$startDay 00:00:00' AND end_time >= '$endYear-$endMonth-$endDay 00:00:00')) ".
				"ORDER BY day_of_month, proj_id, task_id, start_time";

?>
<html>
<head>
<title>Weekly Timesheet for <? echo "$contextUser" ?></title>
<?php
include ("header.inc");
?>
</head>
<?php
echo "<body width=\"100%\" height=\"100%\"";
include ("body.inc");
if (isset($popup))
	echo "onLoad=window.open(\"popup.php?proj_id=$proj_id&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
echo ">\n";

include ("banner.inc");
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="month" value=<? echo $month; ?>>
<input type="hidden" name="year" value=<? echo $year; ?>>
<input type="hidden" name="day" value=<? echo $day; ?>>
<input type="hidden" name="task_id" value=<? echo $task_id; ?>>

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
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Client:</td></tr></table></td>
												<td width="100%"><? client_select_list($client_id, $contextUser, false, false, true, false, "submit();"); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" /></td>
											</tr>
										</table>
									</td>	
									<td>		
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Project:</td></tr></table></td>
												<td width="100%"><? project_select_list($client_id, false, $proj_id, $contextUser, false, true, "submit();"); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading">
							Week Start: <? echo date('D F j, Y',mktime(0,0,0,$startMonth, $startDay, $startYear)); ?>
						</td>
						<td align="right" nowrap>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?proj_id=<?echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<?echo $previousWeekYear ?>&month=<? echo $previousWeekMonth ?>&day=<? echo $previousWeekDay ?>" class="outer_table_action">Prev</a>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $nextWeekYear ?>&month=<? echo $nextWeekMonth ?>&day=<? echo $nextWeekDay ?>" class="outer_table_action">Next</a>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading" align="center">
<?php

						if ($client_id == 0)
							print "Client / ";
					
						if ($proj_id == 0) 
							print "Project / ";

						print "Task";
?>
						</td>
						<td align="center">&nbsp;</td>
						<?
						//print the days of the week
						$currentDayDate = $startDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%A", $currentDayDate);
							$currentDayDate += A_DAY;							
							print "	<td class=\"inner_table_column_heading\" align=\"center\">$currentDayStr</td>\n";
						}
						?>
						<td align="center">&nbsp;</td>
						<td class="inner_table_column_heading" align="center">Total</td>
					</tr>
					<tr>
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);			
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";

 
	class TaskInfo extends Pair {
		var $projectId;
		var $projectTitle;
		var $taskName;
		var $clientName;
		
		function TaskInfo($value1, $value2, $projectId, $projectTitle, $taskName, $clientName) {
			 parent::Pair($value1, $value2);
			 $this->projectId = $projectId;
			 $this->projectTitle = $projectTitle;
			 $this->taskName = $taskName;
			 $this->clientName = $clientName;
		}
	}

	// Get the Weekly data.
	list($qh3, $num3) = dbQuery($query);	

	//print "<p>Query: $query </p>";
	//print "<p>there were $num3 results</p>";


	//we're going to put the data into an array of
	//different (unique) TASKS
	//which has an array of DAYS (7) which has
	//and array of size 4: 
	// -index 0 is task entries array for tasks which started on a previous day and finish on a following day
	// -index 1 is task entries array for tasks which started on a previous day and finish today
	// -index 2 is task entreis array for tasks which started and finished today
	// -index 3 is task entries array for tasks which started today and finish on a following day

	$structuredArray = array();
	$previousTaskId = -1;
	$currentTaskId = -1;

	//iterate through results
	for ($i=0; $i<$num3; $i++) {
		//get the record for this task entry
		$data = dbResult($qh3,$i);

		//Due to a bug in mysql with converting to unix timestamp from the string, 
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$data["start_time"] = strtotime($data["start_time_str"]);
		$data["end_time"] = strtotime($data["end_time_str"]);
		
		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_time"];
		$currentTaskEndDate = $data["end_time"];
		$currentTaskName = $data["taskName"];
		$currentProjectTitle = $data["projectTitle"];
		$currentProjectId = $data["proj_id"];				
		$currentClientName = $data["clientName"];
		
		//debug
		//print "<p>taskId:$currentTaskId '$data[taskName]', start time:$data[start_time_str], end time:$data[end_time_str]</p>";
		
		//find the current task id in the array
		$taskCount = count($structuredArray);
		unset($matchedPair);
		for ($j=0; $j<$taskCount; $j++) {
			//does its value1 (the task id) match?
			if ($structuredArray[$j]->value1 == $currentTaskId) {
				//store the pair we matched with
				$matchedPair = &$structuredArray[$j];

				//debug				
				//print "<p> found existing matched pair so adding to that one </p>";
				
				//break since it matched
				break;
			}
		}
		
		//was it not matched
		if (!isset($matchedPair)) {

			//debug				
			//print "<p> creating a new matched pair for this task </p>";
				
			//create a new days array
			$daysArray = array();
			
			//put an array in each day (this internal array will be of size 4)
			for ($j=0; $j<7; $j++) {
				//create a task event types array
				$taskEventTypes = array();

				//add 4 arrays to it
				for ($k=0; $k<4; $k++)
					$taskEventTypes[] = array();
					
				//add the task event types array to the days array for this day
				$daysArray[] = $taskEventTypes;
			}
			
			//create a new pair
			$matchedPair = new TaskInfo($currentTaskId, 
																					 $daysArray, 
																					 $currentProjectId, 
																					 $currentProjectTitle, 
																					 $currentTaskName, 
																					 $currentClientName);
			
			//add the matched pair to the structured array
			$structuredArray[] = $matchedPair;					
			
			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$structuredArray[count($structuredArray)-1];			
			
			//print "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}
			
		//iterate through the days array
		for ($k=0; $k<7; $k++) {
		
			//$dayStart = strftime("%D %T", $startDate + $k * A_DAY);
			//$dayEnd = strftime("%D %T", $startDate + ($k + 1) * A_DAY);		
			//print "<p>DAY start: $dayStart, DAY end: $dayEnd</p>";
		
			//work out some booleans
			$startsOnPreviousDay = ($currentTaskStartDate < ($startDate + $k * A_DAY));
			$endsOnFollowingDay = ($currentTaskEndDate >= ($startDate + ($k + 1) * A_DAY));
			$startsToday = ($currentTaskStartDate >= ($startDate + $k * A_DAY) && 
													$currentTaskStartDate < ($startDate + ($k + 1) * A_DAY));
			$endsToday = 	($currentTaskEndDate >= ($startDate + $k * A_DAY) &&
													$currentTaskEndDate < ($startDate + ($k + 1) * A_DAY));	

			//$currentTaskStartDateStr = strftime("%D %T", $currentTaskStartDate);
			//$currentTaskEndDateStr = strftime("%D %T", $currentTaskEndDate);
			//print "<p>task start: $currentTaskStartDateStr task end: $currentTaskEndDateStr</p>";
													
			//print "<p>startsOnPreviousDay=$startsOnPreviousDay, endsOnFollowingDay=$endsOnFollowingDay" .
			//	", startsToday=$startsToday, endsToday=$endsToday</p>";
		
			//does it start before this day and end after this day?
			if ($startsOnPreviousDay && $endsOnFollowingDay)
				//add this task entry to the array for index 0
				$matchedPair->value2[$k][0][] = $data;
			//does it start before this day and end on this day?
			else if ($startsOnPreviousDay && $endsToday)
				//add this task entry to the arry for index 1
				$matchedPair->value2[$k][1][] = $data;
			//does it start and end on this day?
			else if ($startsToday && $endsToday)
				//add this task entry to the array for index 2					
				$matchedPair->value2[$k][2][] = $data;
			//does it start on this day and end on a following day
			else if ($startsToday && $endsOnFollowingDay)
				//add this task entry to the array for index 3
				$matchedPair->value2[$k][3][] = $data;						
		}
	}
	
	//by now we should have our results structured in such a way that it's easy to output it

	//set vars
	$previousProjectId = -1;
	$allTasksDayTotals = array(0,0,0,0,0,0,0); //totals for each day

/*	$previousTaskId = -1;	
	$thisTaskId = -1;	
	$columnDay = -1;
	$columnStartDate = $startDate;*/
	
	//iterate through the structured array
	$count = count($structuredArray);
	unset($matchedPair);
	for ($i=0; $i<$count; $i++) {
		$matchedPair = &$structuredArray[$i];

		//start the row
		print "<tr>";

		//open the column for client name, project title, task name
		print "<td  class=\"calendar_cell_middle\" valign=\"top\">";
		
		//should we print the client name?
		if ($client_id == 0)
			print "<div class=\"client_name_small\">$matchedPair->clientName</div>";
		
		//print the project title
		if ($proj_id == 0) 
			print "<div class=\"project_name_small\">$matchedPair->projectTitle</div>";
			
		//print the task name
		print "<div class=\"task_name_small\">$matchedPair->taskName</div>";
		print "</td>\n";
		
		//print the spacer column
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
		
		//iterate through the days array
		$currentDay = 0;
		$weeklyTotal = 0;
		foreach ($matchedPair->value2 as $currentDayArray) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"right\">";
			
			//while we are printing times set the style
			print "<span class=\"task_time_small\">";
						
			//declare todays vars			
			$todaysStartTime = $startDate + $currentDay * A_DAY;
			$todaysEndTime = $startDate + ($currentDay + 1) * A_DAY;
			$currentDay++;
			$todaysTotal = 0;
			
			//create a flag for empty cell
			$emptyCell = true;
			
			//iterate through the current day array
			for ($j=0; $j<4; $j++) {
				$currentTaskEntriesArray = $currentDayArray[$j];				
				
				//print "C" . count($currentTaskEntriesArray) . " ";
				
				//iterate through the task entries
				foreach ($currentTaskEntriesArray as $currentTaskEntry) {
					//is the cell empty?
					if ($emptyCell)
						//the cell is not empty since we found a task entry
						$emptyCell = false;					
					else					
						//print a break for the next entry
						print "<br>";
						
					//format printable times
					$formattedStartTime = $currentTaskEntry["start"];
					$formattedEndTime = $currentTaskEntry["endd"];						
										
					switch($j) {
					case 0: //tasks which started on a previous day and finish on a following day
						print "...-...";
						$todaysTotal += A_DAY;
						break;
					case 1: //tasks which started on a previous day and finish today
						print "...-" . $formattedEndTime;
						$todaysTotal += $currentTaskEntry["end_time"] - $todaysStartTime;
						break;
					case 2: //tasks which started and finished today
						print $formattedStartTime . "-" . $formattedEndTime;
						$todaysTotal += $currentTaskEntry["end_time"] - $currentTaskEntry["start_time"];
						break;
					case 3: //tasks which started today and finish on a following day
						print $formattedStartTime . "-...";
						$todaysTotal += $todaysEndTime - $currentTaskEntry["start_time"];
						break;
					default:
						print "error";					
					}						
				}
			}
			
			//make sure the cell has at least a space in it so that its rendered by the browser
			if ($emptyCell)
				print "&nbsp;";
				
			//close the times class
			print "</span>";
				
			if (!$emptyCell) {
				//print todays total
				$todaysTotalStr = formatSeconds($todaysTotal);
				print "<br><span class=\"task_time_total_small\">$todaysTotalStr</span>";			
			}
			
			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $todaysTotal;
		
			//add this days total to the all tasks total for this day
			$allTasksDayTotals[$currentDay - 1] += $todaysTotal;
		}	

		//print the spacer column
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";

		//format the weekly total
		$weeklyTotalStr = formatSeconds($weeklyTotal);
			
		//print the total column
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\">$weeklyTotalStr</span></td>";
		
		//end the row
		print "</tr>";				
		
		//store the previous task and project ids
		$previousTaskId = $currentTaskId;
		$previousProjectId = $matchedPair->projectId;		
	}
	
	//create an actions row
	print "<tr>\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Actions:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	$currentDayDate = $startDate;
	for ($i=0; $i<7; $i++) {
		$currentDay = date("j", $currentDayDate);
		$currentMonth = date("m", $currentDayDate);
		$currentYear = date("Y", $currentDayDate);				
		$popup_href = "javascript:void(0)\" onclick=window.open(\"popup.php".
											"?client_id=$client_id".
											"&proj_id=$proj_id".
											"&task_id=$task_id".
											"&year=$currentYear".
											"&month=$currentMonth".
											"&day=$currentDay".
											"&destination=$_SERVER[PHP_SELF]".
											"\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310\") dummy=\"";
		print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
		print "<a href=\"$popup_href\" class=\"action_link\">Add</a></td>\n";
		$currentDayDate += A_DAY;
	}
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n";
	print "</tr>";
	
	
	//create a new totals row
	print "<tr>\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Total Hours:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
						
	//iterate through day totals for all tasks
	$grandTotal = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = formatSeconds($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\">$formattedTotal</span></td>";
	}
	
	//print grand total
	$formattedGrandTotal = formatSeconds($grandTotal);
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\">$formattedGrandTotal</span></td>";
	print "</tr>";
		
?>	
					
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
	