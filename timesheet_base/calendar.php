<?php
//$Header: /cvsroot/tsheet/timesheet.php/calendar.php,v 1.9 2005/05/23 05:39:38 vexil Exp $

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
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

//load local vars from superglobals
$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: (int)date("Y");
$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: (int)date("m");
$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: (int)date("j");
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;

// Check project assignment.
if ($proj_id != 0) { // id 0 means 'All Projects'
	list($qh, $num) = dbQuery("select * from $ASSIGNMENTS_TABLE where proj_id='$proj_id' and username='$contextUser'");
	if ($num < 1)
		errorPage("You cannot access this project, because you are not assigned to it.");
}
else
	$task_id = 0;


//a useful constant
define("A_DAY", 24 * 60 * 60);


//get the passed date (context date)
$todayDate = mktime(0, 0, 0, $month, $day, $year);
$todayYear = date("Y", $todayDate);
$todayMonth = date("n", $todayDate);
$todayDay = date("j", $todayDate);
$dateValues = getdate($todayDate);
$todayDayOfWeek = $dateValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = getWeekStartDay();

//work out the start date by minusing enough seconds to make it the start day of week
$startDate = mktime(0,0,0, $month, 1, $year);
$startYear = date("Y", $startDate);
$startMonth = date("n", $startDate);
$startDay = date("j", $startDate);

// Calculate the previous month.
$last_month = $month - 1;
$last_year = $year;
if (!checkdate($last_month, 1, $last_year)) {
	$last_month += 12;
	$last_year --;
}

//calculate the next month
$next_month = $month+1;
$next_year = $year;
if (!checkdate($next_month, 1, $next_year)) {
	$next_year++;
	$next_month -= 12;
}

//work out the end date by adding 7 days
$endDate = mktime(0,0,0,$next_month, 1, $next_year);
$endYear = date("Y", $endDate);
$endMonth = date("n", $endDate);
$endDay = date("n", $endDate);

// Get day of week of 1st of month
$dowForFirstOfMonth = date('w',mktime(0,0,0,$month,1,$year));

//get the number of lead in days
$leadInDays = $dowForFirstOfMonth - $startDayOfWeek;
if ($leadInDays < 0)
	$leadInDays += 7;
	
//get the first printed date
$firstPrintedDate = $startDate - ($leadInDays * A_DAY);

////get todays values
//$today = time();
//$todayYear = date("Y", $today);
//$todayMonth = date("n", $today);
//$todayDay = date("j", $today);

//define the command menu
include("timesheet_menu.inc");

function print_totals($seconds, $type="", $year, $month, $day) {

	//minus a week from the date given so we link to the start of that week
	$passedDate = mktime(0,0,0,$month, $day, $year);
	$passedDate -= A_DAY * 7;
	$year = date("Y", $passedDate);
	$month = date("n", $passedDate);
	$day = date("j", $passedDate);

	// Called from calendar.php to print out a line summing the hours worked in the past
	// week.  index.phtml must set all global variables.
	global $BREAK_RATIO, $client_id, $proj_id, $task_id;
	print "</tr><tr>\n";
	if ($BREAK_RATIO > 0) {
		print "<td align=\"left\" colspan=\"3\">";
		$break_sec =  floor($BREAK_RATIO*$seconds);
		$seconds -= $break_sec;
		print "<font size=\"-1\">Break time: <font color=\"red\">". formatSeconds($break_sec);
		print "</font></font></td><td align=\"right\" colspan=\"4\">";
	} 
	else
		print "<td align=\"right\" colspan=\"7\" class=\"calendar_totals_line_$type\">";

	if ($type=="monthly")
		print "Monthly total: ";
	else {
		print "<a href=\"weekly.php?client_id=$client_id&proj_id=$proj_id&task_id=$task_id&year=$year&month=$month&day=$day\">Weekly Total: </a>";
	}
								
	print "<span class=\"calendar_total_value_$type\">". formatSeconds($seconds) ."</span></td>\n";
}
    
?>
<html>
<head>
<title>Timesheet for <? echo "$contextUser" ?></title>
<?
include ("header.inc");
?>
</head>
<?
echo "<body width=\"100%\" height=\"100%\"";
include ("body.inc");
if (isset($popup))
	echo "onLoad=window.open(\"popup.php?proj_id=$proj_id&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
echo ">\n";

include ("banner.inc");
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" metho="get">
<input type="hidden" name="month" value=<? echo $month; ?>>
<input type="hidden" name="year" value=<? echo $year; ?>>
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
							<? echo date('F Y',mktime(0,0,0,$month, 1, $year)); ?>
						</td>
						<td align="right" nowrap>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?client_id=<? echo $client_id; ?>&proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $last_year ?>&month=<? echo $last_month ?>&day=<? echo $todayDay; ?>" class="outer_table_action">Prev</a>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?client_id=<? echo $client_id; ?>&proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $next_year ?>&month=<? echo $next_month ?>&day=<? echo $todayDay; ?>" class="outer_table_action">Next</a>
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
						<?
						//print the days of the week
						$currentDayDate = $firstPrintedDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%A", $currentDayDate);
							$currentDayDate += A_DAY;							
							print "	<td class=\"inner_table_column_heading\" align=\"center\">$currentDayStr</td>\n";
						}
						?>
					</tr>
					<tr>
<?php {

	//define the variable dayRow
	$dayRow = 0;
	
	// Print last months' days spots.
	for ($i=0; $i<$leadInDays; $i++) {
	//while (($dayRow < $dowForFirstOfMonth) && ($dowForFirstOfMonth != 0)) {
		print "<td width=\"14%\" HEIGHT=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
		$dayRow++;
	}
  
	// Get the Monthly data.
	list($num, $qh) = get_time_date($month, $year, $contextUser, $proj_id, $client_id);
  
  $i=0; $day = 1; $tot_sec = 0; $week_tot_sec = 0; $day_tot_sec = 0;
  while (checkdate($month, $day, $year)) {  
		// Reset daily variables;
		$day_tot_sec = 0;
		$last_task_id = -1;
		$last_proj_id = -1;
		$last_client_id = -1;
    
		// New Week.
		if ((($dayRow % 7) == 0) && ($dowForFirstOfMonth != 0)) {
			print_totals($week_tot_sec, "weekly", $year, $month, $day);
			$week_tot_sec = 0;
			print "</tr>\n<tr>\n";
		}
		else 
			$dowForFirstOfMonth = 1;

		//define subtable		
		if (($dayRow % 7) == 6)
			print "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_right\">\n";
		else
			print "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_middle\">\n";
		
		print "	<table width=\"100%\">\n";
    
		// Print out date.
    /*print "<tr><td valign=\"top\"><tt><A HREF=\"daily.php?month=$month&year=$year&".
      "day=$day&client_id=$client_id&proj_id=$proj_id&task_id=$task_id\">$day</a></tt></td></tr>";*/

		$popup_href = "javascript:void(0)\" onclick=window.open(\"popup.php".
											"?client_id=$client_id".
											"&proj_id=$proj_id".
											"&task_id=$task_id".
											"&year=$year".
											"&month=$month".
											"&day=$day".
											"&destination=$_SERVER[PHP_SELF]".
											"\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310\") dummy=\"";

    print "<tr><td valign=\"top\"><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
    print "<tr><td valign=\"top\"><A HREF=\"daily.php?month=$month&year=$year&".
      "day=$day&client_id=$client_id&proj_id=$proj_id&task_id=$task_id\">$day</a></td>";
    print "<td valign=\"top\" align=\"right\"><a href=\"$popup_href\" class=\"action_link\">".
    				 "<img src=\"images/add.gif\" width=\"11\" height=\"11\" border=\"0\">".
				    "</a></td>";
    print "</tr>";
    print "</table></td></tr>";


    $data_seen = 0;

    // If the day has data, print it.
		for ($i=0;$i<$num; $i++) {
			$data = dbResult($qh,$i);
			
			//Due to a bug in mysql with converting to unix timestamp from the string, 
			//we are going to use php's strtotime to make the timestamp from the string.
			//the problem has something to do with timezones.
			$data["start_time"] = strtotime($data["start_time_str"]);
			$data["end_time"] = strtotime($data["end_time_str"]);
			
			if (
					(($data["start_time"] < mktime(0,0,0,$month,$day,$year)) 
					&& ($data["end_time"] > mktime(23,59,59,$month,$day,$year))) ||
					(($data["start_time"] >= mktime(0,0,0,$month,$day,$year)) 
					&& ($data["start_time"] <= mktime(23,59,59,$month,$day,$year))) ||
					(($data["end_time"] >= mktime(0,0,0,$month,$day,$year)) 
					&& ($data["end_time"] <= mktime(23,59,59,$month,$day,$year)))
				 )
			{
			  // This day has data in it.  Therefore we want to print out a summary at the bottom of each day.
			  $data_seen = 1;
			  $todays_total_sec=0;
			  
			  //print out client name if its a new client
			  if ($client_id == 0 && $last_client_id != $data["client_id"]) {
			  	$last_client_id = $data["client_id"];
			  	$clientName = $data["organisation"];
 					print "<tr><td valign=\"top\" class=\"client_name_small\">$clientName</td></tr>";
			  }

			  //print out project name if its a new project
			  if ($proj_id == 0 && $last_proj_id != $data["proj_id"]) {
					$last_proj_id = $data["proj_id"];  
					$projectName = $data["title"];
 					print "<tr><td valign=\"top\" class=\"project_name_small\">$projectName</td></tr>";
			  }
			    
			  // Print out task name if it's a new task
			  if ($last_task_id != $data["task_id"]) {
					$last_task_id = $data["task_id"];
					$taskName = $data["name"];
					print "<tr><td valign=\"top\" class=\"task_name_small\">$taskName</td></tr>";
				}
				
				if ($data["diff_sec"] > 0) {
					//if both start and end time are not today
					if ($data["start_time"] < mktime(0,0,0,$month,$day,$year) && $data["end_time"] > mktime(23,59,59,$month,$day,$year)) {
						$today_diff_sec = 24*60*60; //all day - no one should work this hard!
						print "<tr><td valign=\"top\" class=\"task_time_small\">...-...</td></tr>";
					}		
					//if end time is not today
					elseif ($data["end_time"] > mktime(23,59,59,$month,$day,$year)) {
						$today_diff_sec = mktime(0,0,0, $month,$day,$year) + 24*60*60 - $data["start_time"];
				    print "<tr><td valign=\"top\" class=\"task_time_small\">$data[start]-...</td></tr>";
					}
					//elseif start time is not today
					elseif ($data["start_time"] < mktime(0,0,0,$month,$day,$year)) {
						$today_diff_sec = $data["end_time"] - mktime(0,0,0, $month,$day,$year); 
						print "<tr><td valign=\"top\" class=\"task_time_small\">...-$data[endd]</td></tr>";
					}
					else {
						$today_diff_sec = $data["diff_sec"];
						$startTimeStr = $data["start"];
						$endTimeStr = $data["endd"];
				    print "<tr><td valign=\"top\" class=\"task_time_small\">$startTimeStr-$endTimeStr</td></tr>";
					}
			
			    $tot_sec += $today_diff_sec;
			    $week_tot_sec += $today_diff_sec;
			    $day_tot_sec += $today_diff_sec;
	  		}
				else {
					$startTimeStr = $data["start"];
					print "<tr><td valign=\"top\" class=\"task_time_small\">$startTimeStr-...</td></tr>";
				}
			}
		}
		
    if ($data_seen == 1)	{
      print "<tr><td valign=\"top\" class=\"task_time_total_small\">" . formatSeconds($day_tot_sec) ."</td></tr>";
    } 
		else {
      print "<tr><td>&nbsp;</td></tr>";
    }

		//end subtable
		print "		</table>\n";
		print " </td>\n";

    $day++;
    $dayRow++;
  }
  // Print the rest of the calendar.
	while (($dayRow % 7) != 0) {
		if (($dayRow % 7) == 6)
			print " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_right\">&nbsp;</TD>\n ";
		else
			print " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</TD>\n ";
		$dayRow++;
	}
	
	print_totals($week_tot_sec, "weekly", $year, $month, $day);
	$week_tot_sec = 0;
	print "</tr>\n<tr>\n";
	print_totals($tot_sec, "monthly", $year, $month, $day);
}?>
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
