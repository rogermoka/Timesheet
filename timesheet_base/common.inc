<?php {
//$Header: /cvsroot/tsheet/timesheet.php/common.inc,v 1.23 2005/05/23 05:39:39 vexil Exp $
if (!defined("COMMON_INC")) {
		define("COMMON_INC", 1);
		
		include("database_credentials.inc");    
		include("mysql.db.inc");
    
		$BREAK_RATIO = (0); 
		// For an hour break every 8 hours this would be: (1/8)

		$BODY_BGCOLOR="#FFFFFF";	// Background color for every page.
		//$BODY_BGCOLOR="#AFAFEF";	// Background color for every page.
		$TABLE_BGCOLOR="#b3c4de";	// Background color for table cells
		$TABLE_BGCOLOR2="#AFAFEF";	// Background color for table cells

		function get_time_date($month, $year, $id, $proj_id, $client_id) {
			include("table_names.inc");
			list($qhq, $numq) = dbQuery("SELECT timeformat FROM $CONFIG_TABLE WHERE config_set_id = '1'");
			$configData = dbResult($qhq);
			
			$last_day = get_last_day($month, $year);
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
			"$TASK_TABLE.name, $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id, ". 
			"$PROJECT_TABLE.title, $CLIENT_TABLE.organisation, $PROJECT_TABLE.client_id ". 
			"FROM $TIMES_TABLE, $TASK_TABLE, $PROJECT_TABLE, $CLIENT_TABLE ".
			"WHERE uid='$id' AND ";
			
			if ($proj_id > 0) //otherwise want all records no matter what project
				$query .=	"$TIMES_TABLE.proj_id=$proj_id AND ";
			else if ($client_id > 0) //only records for projects of the given client
				$query .= "$PROJECT_TABLE.client_id=$client_id AND ";
				
			$query .= 
				"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
				"$TASK_TABLE.proj_id = $PROJECT_TABLE.proj_id AND ".
				"$PROJECT_TABLE.client_id = $CLIENT_TABLE.client_id AND ".
				"((start_time >= '$year-$month-1 00:00:00' AND start_time <= '$year-$month-$last_day 23:59:59') ".
				" OR (end_time >= '$year-$month-1 00:00:00' AND end_time <= '$year-$month-$last_day 23:59:59') ".
				" OR (start_time < '$year-$month-1 00:00:00' AND end_time > '$year-$month-$last_day 23:59:59')) ".
				"ORDER BY day_of_month, proj_id, task_id, start_time";
			
			//print "<p>Query: $query</p>";
      
			list($my_qh, $num) = dbQuery($query);
			return array($num, $my_qh);
		}
		    
		function get_daily_times($month, $day, $year, $id, $proj_id) {
			include("table_names.inc");
			list($qhq, $numq) = dbQuery("select timeformat from $CONFIG_TABLE where config_set_id = '1'");
			$configData = dbResult($qhq);
			
			$query = "select date_format(start_time,'%d') as day_of_month, trans_num, ";
		
			if ($configData["timeformat"] == "12")
				$query .= "date_format(end_time, '%l:%i%p') as endd, date_format(start_time, '%l:%i%p') as start, ";
			else
				$query .= "date_format(end_time, '%k:%i') as endd, date_format(start_time, '%k:%i') as start, ";

			$query .= "unix_timestamp(end_time) - unix_timestamp(start_time) as diff_sec, ".
			"unix_timestamp(start_time) as start_time, ".
			"unix_timestamp(end_time) as end_time, ".
			"end_time as end_time_str, ".
			"start_time as start_time_str, ".
			"$TASK_TABLE.name, $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id  from $TIMES_TABLE, $TASK_TABLE WHERE uid='$id' AND ";
		
			if ($proj_id > 0) //otherwise want all records no matter what project
				$query .=	"$TIMES_TABLE.proj_id=$proj_id AND ";
				
			$query .= "$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
			"((start_time >= '$year-$month-$day 00:00:00' AND start_time <= '$year-$month-$day 23:59:59') ".
			" OR (end_time >= '$year-$month-$day 00:00:00' AND end_time <= '$year-$month-$day 23:59:59') ".
			" OR (start_time < '$year-$month-$day 00:00:00' AND end_time > '$year-$month-$day 23:59:59')) ".
			" order by day_of_month, proj_id, task_id, start_time";
		
			list($my_qh, $num) = dbQuery($query);

			return array($num, $my_qh);
		}
    
		function get_last_day($month, $year) {
			$last_day = 28;
			while (checkdate($month, $last_day, $year)) { $last_day++; }
			return $last_day-1;
		}

		function formatSeconds($seconds) 
		{
			$hours = (int)($seconds/3600);
			$seconds -= $hours * 3600;
			$minutes = (int)($seconds/60);
			$seconds -= $minutes * 60;

			return "${hours}h ${minutes}m";
		}

		function get_project_info($proj_id) {
			include("table_names.inc");
			
			// Retreives title, $CLIENT_TABLE, description, deadline and link from database for a given proj_id
			$result = array();
			if ($proj_id > 0) {
				$sql = "SELECT * from $PROJECT_TABLE where proj_id=$proj_id";
				list($my_qh, $num) = dbQuery($sql);
				$result = dbResult($my_qh);
			}
			return $result;
		}
   
		function get_trans_info($trans_num) {
			include("table_names.inc");
			
			$result = array();	
			if ($trans_num > 0) {
				$query = "SELECT $PROJECT_TABLE.client_id, $TIMES_TABLE.proj_id, task_id, log_message, ".
								"end_time as end_time_str, ".
								"start_time as start_time_str, ".
								"unix_timestamp(start_time) as start_time, ".
								"unix_timestamp(end_time) as end_time ".
								"FROM $TIMES_TABLE, $PROJECT_TABLE WHERE ".
								"trans_num='$trans_num' AND ".
								"$TIMES_TABLE.proj_id = $PROJECT_TABLE.proj_id";
		
				list($my_qh, $num) = dbQuery($query);
				$result = dbResult($my_qh);
			}
			return $result;
		}
		
		function day_button($name, $today=0) {
			if (!$today) 
				$today = date("d");
				
			$i = 0;
			echo "<select name=\"$name\">\n";
			print "<option value=0>None</A>";

			while ($i < 31) {
				$i++;
				switch($i) {
				case $today:
					echo "<option value=\"$i\" selected>$i\n";
					break;
				default:
					echo "<option value=\"$i\">$i\n";
				}
			}
			echo "</select>";
		}
    
		function month_button ($name, $month=0) {
			if(!$month)
				$month = date("m");
      
			$i = 1;
			echo "<select name=\"$name\">\n";
			print "<option value=0>None</A>";
			while ($i <= 12) {
				switch($i) {
				case $month:
					echo "<option value=\"$i\" selected>" . date("M",mktime(0,0,0,$i,1,1999)) . "\n";
	  			break;
				default:
					echo "<option value=\"$i\">". date("M",mktime(0,0,0,$i,1,1999)) . "\n";
				}
				$i++;
			}
			echo "</select>";
		}
    
		function year_button ($name, $year=0) {
			if(!$year)
				$year = date("Y");

			$i = $year;
			echo "<select name=\"$name\">\n";
			print "<option value=0>None</A>";
			while ($i <= $year+10) {
				switch($i) {
				case $year:
					echo "<option value=\"$i\" selected>$i\n";
					break;
				default:
					echo "<option value=\"$i\">$i\n";
				}
				$i++;
     }
     echo "</select>";
		}


	function single_user_select_list($name, $default='')
	{
		include("table_names.inc");
		
		list($qh,$num) = dbQuery("select username from $USER_TABLE ORDER BY username");
		print "<SELECT NAME=\"$name\">";
		while ($data = dbResult($qh)) {
				print "<OPTION NAME=\"$data[username]\"";
				if ($default == $data["username"]) print " SELECTED ";
				print ">$data[username]</OPTION>\n";
		}
		print "</SELECT>";
	}

	function multi_user_select_list($name, $selected_array=array())
	{
		include("table_names.inc");
		
		list($qh,$num) = dbQuery("select username from $USER_TABLE ORDER BY username");
		print "<SELECT NAME=\"$name\" multiple size=\"11\">\n";
		while ($data = dbResult($qh)) {
				print "<OPTION NAME=\"$data[username]\"";
				if (in_array($data["username"], $selected_array))
					print " SELECTED ";
				print ">$data[username]</OPTION>\n";
		}
		print "</SELECT>";
	}
    
	function client_select_list($currentClientId, $contextUser, $isMultiple, $showSelectClient, $showAllClients, $showNoClient, $onChange="", $restrictedList=true) {
		include("table_names.inc");
		
		if ($restrictedList) {
				list($qh,$num) = dbQuery(									
						"SELECT $CLIENT_TABLE.client_id, $CLIENT_TABLE.organisation, ".
						"$PROJECT_TABLE.client_id, $PROJECT_TABLE.proj_id, ".
						"$ASSIGNMENTS_TABLE.proj_id, $ASSIGNMENTS_TABLE.username ".
						"from $CLIENT_TABLE, $PROJECT_TABLE, $ASSIGNMENTS_TABLE ".
						"WHERE $CLIENT_TABLE.client_id > 1 ".
						"AND $CLIENT_TABLE.client_id=$PROJECT_TABLE.client_id ".
						"AND $PROJECT_TABLE.proj_id=$ASSIGNMENTS_TABLE.proj_id ".
						"AND $ASSIGNMENTS_TABLE.username='$contextUser' ".
						"GROUP BY $CLIENT_TABLE.client_id ".
						"ORDER BY organisation");									
		}
		else {
				list($qh,$num) = dbQuery(
						"SELECT client_id, organisation ".
						"FROM $CLIENT_TABLE WHERE client_id > 1 " .
						"ORDER BY organisation");		
		}
		
		print "<select name=\"client_id\" onChange=\"$onChange\" style=\"width:100%;\"";
		if ($isMultiple) 
			print "multiple size=\"4\"";
		print ">\n";
		
		//should we show the 'Select Client' option
		if ($showSelectClient)
			print "<option value=\"0\">Select Client</option>\n";
		else if ($showAllClients)
			print "<option value=\"0\">All Clients</option>\n";
			
		//should we show the 'No Client' option
		if ($showNoClient) {
			print "<option value=\"1\"";
			if ($currentClientId == 1)
				print " selected";
			print ">No Client</option>\n";			
		}
			
		while ($result = dbResult($qh)) {
			print "<option value=\"$result[client_id]\"";
			if ($currentClientId == $result["client_id"]) 
				echo " selected";
			print ">";
			/*$printComma = false;
			$printSpace = false;
			if (!empty($result["contact_first_name"])) {			
				echo $result["contact_first_name"];
				$printSpace = true;
				$printComma = true;
			}			
			if (!empty($result["contact_last_name"])) {
				if ($printSpace)
					print " ";
				echo $result["contact_last_name"];
				$printComma = true;
			}
			if ($printComma)
				print ", ";*/
			print "$result[organisation]</OPTION>\n";
     }
     print "</select>";
   }
   
	function project_select_list($currentClientId, $needsClient, $currentProjectId, $contextUser, $showSelectProject, $showAllProjects, $onChange="", $disabled=false) {
		include("table_names.inc");

		if ($currentClientId == 0 && $needsClient) {
			print "<select name=\"dummy\" disabled=\"true\" style=\"width: 100%;\">\n";
			print "  <option>Please select a client</option>\n";
			print "</select>\n";
			return;
		}
					
		if (empty($contextUser)) {
			$query = "SELECT proj_id, title FROM $PROJECT_TABLE ";
			if ($currentClientId != 0)
				$query .= "WHERE $PROJECT_TABLE.client_id = $currentClientId ";
			$query .= "ORDER BY title";
		}
		else {
			$query = "SELECT DISTINCT $ASSIGNMENTS_TABLE.proj_id, $PROJECT_TABLE.title FROM " .
							"$ASSIGNMENTS_TABLE, $PROJECT_TABLE WHERE ";
			if ($currentClientId != 0)
				$query .= "$PROJECT_TABLE.client_id = $currentClientId AND ";
			$query .= "$ASSIGNMENTS_TABLE.proj_id = $PROJECT_TABLE.proj_id AND " .
							"$ASSIGNMENTS_TABLE.username='$contextUser' AND " .
							"$ASSIGNMENTS_TABLE.proj_id > 0 AND " .
							"$PROJECT_TABLE.proj_status='Started' " .
							"ORDER BY $PROJECT_TABLE.title,$ASSIGNMENTS_TABLE.proj_id";
		}

		list($qh, $num) = dbQuery($query);
		if ($num == 0) {
			if (!empty($contextUser)) {
				print "<select name=\"dummy\" disabled=\"true\" style=\"width: 100%;\">\n";
				print "  <option>There are no projects assigned to you</option>\n";
				print "</select>\n";
				return;
			}
			print "<select name=\"dummy\" disabled=\"true\" style=\"width: 100%;\">\n";
			print "  <option>There are no projects for this client</option>\n";
			print "</select>\n";
			return;
		}
		
		print "<select name=\"proj_id\" onChange=\"$onChange\" style=\"width:100%;\"";
		if ($disabled == 'true')
			print " disabled";
		print ">\n";
		
		//should we show the 'Select Project' option
		if ($showSelectProject)
			print "<option value=\"0\">Select Project</option>\n";
		
		if ($showAllProjects) {
			print "<option value=\"0\"";
			if ($currentProjectId == 0)
				print " selected";
			print ">All Projects</option>\n";
		}

		if ($num > 0) {	
			while ($return = dbResult($qh)) {
				$title = stripslashes($return["title"]);
				print "<option value=\"$return[proj_id]\"";
				if ($currentProjectId == $return["proj_id"])
					print " selected";
				print ">$title</option>\n";
			}
		}
		print "</select>";
	}

	function task_select_list ($currentProjectId, $currentTaskId, $contextUser="", $onChange="") {
		include("table_names.inc");

		if ($currentProjectId == 0) {
			print "<select name=\"dummy\" disabled=\"true\" style=\"width: 100%;\">\n";
			print "  <option>Please select a project</option>\n";
			print "</select>\n";
			return;
		}
					
		if ($contextUser == '')
			$query = "select task_id, name, status from $TASK_TABLE where proj_id=$currentProjectId";
		else {
// 	$query = "select distinct $TASK_ASSIGNMENTS_TABLE.task_id, $TASK_TABLE.name from $TASK_ASSIGNMENTS_TABLE, $TASK_TABLE, $ASSIGNMENTS_TABLE where ".
// 	  "$ASSIGNMENTS_TABLE.proj_id=$proj_id AND $TASK_ASSIGNMENTS_TABLE.task_id = $TASK_TABLE.task_id and ".
// 	  "$TASK_ASSIGNMENTS_TABLE.task_id > 1 AND $TASK_TABLE.status='Started' order by $TASK_ASSIGNMENTS_TABLE.task_id";
			$query = "SELECT DISTINCT $TASK_ASSIGNMENTS_TABLE.task_id, $TASK_TABLE.name " .
				"FROM $TASK_ASSIGNMENTS_TABLE, $TASK_TABLE WHERE ".
				"$TASK_TABLE.proj_id=$currentProjectId AND ".
				"$TASK_ASSIGNMENTS_TABLE.task_id = $TASK_TABLE.task_id AND ".
				"$TASK_ASSIGNMENTS_TABLE.username = '$contextUser' AND ".
				"$TASK_TABLE.status='Started' " .
				"ORDER BY $TASK_ASSIGNMENTS_TABLE.name,$TASK_ASSIGNMENTS_TABLE.task_id";
		}

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {
			print "<select name=\"task_id\" onChange=\"$onChange\" style=\"width:100%;\">\n";
			while ($return = dbResult($qh)) {
				print "<option value=\"$return[task_id]\"";
				if ($currentTaskId == $return["task_id"])
					print " selected";
				print ">$return[name]</option>\n";
			}
			print "</select>";
		}
		else {
			print "<select name=\"dummy\" disabled=\"true\" style=\"width: 100%;\">\n";
			print "  <option>There are no tasks assigned</option>\n";
			print "</select>\n";
		}
	}
    
		
	function user_select_droplist($username='', $disabled='false')
	{
		include("table_names.inc");
			
		$query = "select uid, username, first_name, last_name from $USER_TABLE order by first_name, last_name";

		print "<select name=\"uid\" onChange=\"submit()\" style=\"width: 100%;\"";
		if ($disabled == 'true')
			print " disabled";
		print ">\n";
		
		list($qh, $num) = dbQuery($query);
		if ($num > 0) {	
			while ($return = dbResult($qh)) {
				$current_username = stripslashes($return["username"]);
				$current_name = stripslashes($return["first_name"] . " " . $return["last_name"] );
				print "<option value=\"$current_username\"";
				if ($current_username == $username)
					print " selected";
				if ($current_name == " ")
					print ">$current_username</option>\n";
				else
					print ">$current_name</option>\n";
			}
		}
		print "</select>";			
	}

	function client_select_droplist($client_id=1, $disabled='false') {
		include("table_names.inc");
			
			$query = "select client_id, organisation from $CLIENT_TABLE order by organisation";

		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"100%\">";
		print "<select name=\"client_id\" Onchange=\"submit()\" style=\"width: 100%;\"";
		if ($disabled == 'true')
			print " disabled";
		print ">\n";
		
		list($qh, $num) = dbQuery($query);
		if ($num > 0) {	
			while ($return = dbResult($qh)) {
				$current_organisation = stripslashes($return["organisation"]);
				print "<option value=\"$return[client_id]\"";
				if ($return["client_id"] == $client_id)
					print " selected";
				print ">$current_organisation</option>\n";
			}
			print "</select></td><td width=\"0\">";
			print "<input type=\"button\" name=\"info\" value=\"Info\"";
			print	"onclick=window.open(\"client_info.php?client_id=$client_id\",";
			print	"\"Client_Info\",";
			print	"\"location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=200\")>";			
			print "</td></tr></table>";
		}
		else
			print "</select></td></tr></table>";			
	}
	
	function project_select_droplist($proj_id=1, $disabled='false') {
		include("table_names.inc");
			
			$query = "select " .
							"proj_id, " .
							"title, " .
							"organisation " .
							"FROM $PROJECT_TABLE, $CLIENT_TABLE ".
							"ORDER BY $CLIENT_TABLE.organisation, $PROJECT_TABLE.title";

		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"100%\">";
		print "<select name=\"proj_id\" Onchange=\"submit()\" style=\"width: 100%;\"";
		if ($disabled == 'true')
			print " disabled";
		print ">\n";
		
		list($qh, $num) = dbQuery($query);
		$current_organisation = NULL;
		if ($num > 0) {	
			while ($return = dbResult($qh)) {
				if ($current_organisation != stripslashes($return["organisation"])) {
						if ($current_organisation != NULL)
							print "</optgroup>";
						$current_organisation = stripslashes($return["organisation"]);
						print "<optgroup label=\"".$current_organisation."\">\n";
				}
				$current_title = $return['title'];
				print "<option value=\"$return[proj_id]\"";
				if ($return["proj_id"] == $proj_id)
					print " selected";
				print ">$current_title</option>\n";
			}
			print "</optgroup>\n";
			print "</select></td><td width=\"0\">";
			print "</td></tr></table>";
		}
		else
			print "</select></td></tr></table>";			
	}

        
	function present_log_message($action) {
      global $check_in_time_hour, $check_out_time_hour,$check_in_time_min, $check_out_time_min, $year,
	$month, $day, $proj_id, $task_id, $destination;
	?>
<HTML>
<BODY BGCOLOR="#FFFFFF" >
<FORM ACTION="action.php" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER>
   <?{
  if ($destination)
    print "<INPUT TYPE=HIDDEN NAME=destination VALUE=$destination>\n";
  print "<INPUT TYPE=HIDDEN NAME=check_in_time_hour VALUE=\"$check_in_time_hour\">\n";
  print "<INPUT TYPE=HIDDEN NAME=check_out_time_hour VALUE=\"$check_out_time_hour\">\n";
  print "<INPUT TYPE=HIDDEN NAME=check_in_time_min VALUE=\"$check_in_time_min\">\n";
  print "<INPUT TYPE=HIDDEN NAME=check_out_time_min VALUE=\"$check_out_time_min\">\n";
  print "<INPUT TYPE=HIDDEN NAME=year VALUE=\"$year\">\n";
  print "<INPUT TYPE=HIDDEN NAME=month VALUE=\"$month\">\n";
  print "<INPUT TYPE=HIDDEN NAME=day VALUE=\"$day\">\n";
  print "<INPUT TYPE=HIDDEN NAME=proj_id VALUE=\"$proj_id\">\n";
  print "<INPUT TYPE=HIDDEN NAME=task_id VALUE=\"$task_id\">\n";
  switch($action) {
  case "inout":
    print "<INPUT TYPE=HIDDEN NAME=check_in_out_x VALUE=1>\n";
    break;
  case "at":
    print "<INPUT TYPE=HIDDEN NAME=check_out_at_x VALUE=1>\n";
    break;
  case "now":
    print "<INPUT TYPE=HIDDEN NAME=check_out_now_x VALUE=1>\n";
  }
  print "<INPUT TYPE=HIDDEN NAME=log_message_presented VALUE=1>\n";
}?>

   <TR><TD>Please Enter Log message: (max 255 characters)</TD></TR>
   <TR><TD><TEXTAREA NAME=log_message COLS=60 ROWS=4></TEXTAREA></TD></TR>
   <TR><TD><INPUT TYPE=SUBMIT VALUE="Done"></TD></TR>
</TABLE>
   </FORM>
   </BODY>
   </HTML>
      <?
   }

    function proj_status_list($name, $status='') {
      ?>
	<SELECT NAME=<? echo $name ?>>
	<OPTION VALUE="Pending" <? if ($status == 'Pending') echo 'SELECTED'?>>Pending</OPTION>
	<OPTION VALUE="Started" <? if ($status == 'Started') echo 'SELECTED'?>>Started</OPTION>
	<OPTION VALUE="Suspended" <? if ($status == 'Suspended') echo 'SELECTED'?>>Suspended</OPTION>
	<OPTION VALUE="Complete" <? if ($status == 'Complete') echo 'SELECTED'?>>Complete</OPTION>
	</SELECT>
      <?
	   }
      

  } // end if

	function parse_and_echo($text) {
		
		//replace commandMenu string
		if (isset($GLOBALS["commandMenu"]))
			$text = str_replace("%commandmenu%", $GLOBALS["commandMenu"]->toString(), $text);
	
		global $errormsg;
	
		//replace errormsg string
		$text = str_replace("%errormsg%", $errormsg, $text);

		//replace username
		if (isset($_SESSION['loggedInUser']))
			$text = str_replace("%username%", $_SESSION["loggedInUser"], $text);

		//replace time
		$text = str_replace("%time%", date("g:ia"), $text);

		//replace date
		$text = str_replace("%date%", strftime("%A %B %d, %Y"), $text);

		//replace timezone
		$text = str_replace("%timezone%", getenv("TZ"), $text);
		
		//output the result
		echo $text;
	}


	//reverses the effects of htmlentities (see PHP manual)
	function unhtmlentities($str) {
		$trans = get_html_translation_table(HTML_ENTITIES);
		$trans = array_flip($trans);
		return strtr($str, $trans);
	}


	function errorPage($message, $from_popup = false) {
		$targetWindowLocation = "error.php?errormsg=$message";
	
		if (!$from_popup)
			Header("Location: $targetWindowLocation");
		else
			loadMainPageAndCloseWindow($targetWindowLocation);
		exit;
	}

	function loadMainPageAndCloseWindow($targetWindowLocation) {
		//now close this window, and open the target page in the main window
		//(passing it all the parms it needs)

		?>
			<html>
				<head>
					<script language="javascript">
						function loadAndClose() {
							if (window.opener.closed) {
								//create a new window
								window.open("<? echo $targetWindowLocation; ?>", "newMainWindow");
							}
							else {
								//get the main window's location and store it as the destination
								var targetWindowLocation = "<? echo $targetWindowLocation; ?>";
								var mainWindowLocation = window.opener.location.href;
								var questionPos = mainWindowLocation.indexOf('?');
								if (questionPos != -1)
									mainWindowLocation = mainWindowLocation.substring(0,questionPos); 
								var destinationPos = targetWindowLocation.indexOf('destination=');
								if (destinationPos == -1)
									//just append it
									targetWindowLocation += '&desination=' + mainWindowLocation;
								else {
									var nextAmpPos = targetWindowLocation.indexOf('&', destinationPos);
									if (nextAmpPos == -1) {
										targetWindowLocation = 
											targetWindowLocation.substring(destinationPos, targetWindowLocation.length);									
									}
									else {
										var myRegex = new RegExp("destination=(.+?)&", "g")
										targetWindowLocation = targetWindowLocation.replace(myRegex, 'destination=' + mainWindowLocation + '&');
									}									
								}
								window.opener.location=targetWindowLocation;
							}
							
							//close the popup window
							window.close();
						}
					</script>
				</head>
				<body onLoad="javascript:loadAndClose();">
					You don't have javascript enabled. Javascript is required for timesheet.php
				</body>
			</html>
		<?
		exit;
	}

	function isValidProjectForClient($projectId, $clientId) {
		include("table_names.inc");
		list($qh, $num) = dbQuery("SELECT proj_id from $PROJECT_TABLE " .
						"WHERE client_id='$clientId' AND proj_id='$projectId'");
		
		return ($num > 0);
	}
	
	function getValidProjectForClient($clientId) {
		include("table_names.inc");
		list($qh, $num) = dbQuery("SELECT proj_id from $PROJECT_TABLE " .
						"WHERE client_id='$clientId'");
		if ($num == 0)
			return 0;
			
		//get the first result
		$data = dbResult($qh);
		return $data["proj_id"];
	}

	function getFirstClient() {
		include("table_names.inc");
		list($qh, $num) = dbQuery("SELECT client_id from $CLIENT_TABLE");
		if ($num == 0)
			return 0;
			
		//get the first result
		$data = dbResult($qh);
		return $data["client_id"];
	}
	
	function getFirstProject() {
		include("table_names.inc");
		list($qh, $num) = dbQuery("SELECT proj_id from $PROJECT_TABLE");
		if ($num == 0)
			return 0;
			
		//get the first result
		$data = dbResult($qh);
		return $data["proj_id"];
	}
	
	function getWeekStartDay() {
		include("table_names.inc");
		list($qhq, $numq) = dbQuery("select weekstartday from $CONFIG_TABLE where config_set_id = '1'");
		$configData = dbResult($qhq);
		return $configData["weekstartday"];
	}
	
	function getFirstUser() {
		include("table_names.inc");
		list($qh, $num) = dbQuery("SELECT username from $USER_TABLE ");
		if ($num == 0)
			return 0;
			
		//get the first result
		$data = dbResult($qh);
		return $data["username"];	
	}
	
	function getTimeFormat() {
			include("table_names.inc");
			list($qhq, $numq) = dbQuery("SELECT timeformat FROM $CONFIG_TABLE WHERE config_set_id = '1'");
			$configData = dbResult($qhq);
			return $configData["timeformat"];	
	}

	function setReportDate(&$year, &$month, &$day, &$next_week, &$prev_week, &$next_month, &$prev_month, &$time, &$time_middle_month) {
		// Current date
		if (!$month) $month = $todayMonth;
		if (!$year) $year = $todayYear;
		if (!$day) $day = $todayDay;
		$time = mktime( 0, 0, 0, $month, $day, $year);
		if (date('D',$time) != "Mon") $time = strtotime("last Monday", $time);
		$time_middle_month = mktime( 0, 0, 0, $month, 15, $year);
		$day = date('d', $time);
		
		// Calculate the previous and next month and week.
		$next_week =  strtotime("+1 week",$time);
		$prev_week = strtotime("-1 week",$time);
		$next_month =  strtotime("+1 month",$time_middle_month);
		$prev_month = strtotime("-1 month",$time_middle_month);
	}
	
	function printPrevNext($time, $next_week, $prev_week, $next_month, $prev_month, $time_middle_month, $post, $mode = "all") {
		if ($mode == "all" || $mode == "monthly") {	
			print "<a href=\"$_SERVER[PHP_SELF]?";
			if ($post) print "$post";
			print "&month=".date('n',$prev_month).
				  "&year=".date('Y',$prev_month).
				  "&day=15".
				  "&mode=".$mode."\" class=\"outer_table_action\">Prev Month</a>&nbsp;/&nbsp;";
			print "<a HREF=\"$_SERVER[PHP_SELF]?";
			if ($post) print "$post";
			print "&month=".date('n',$next_month).
				  "&year=".date('Y',$next_month).
				  "&day=15".
				  "&mode=".$mode."\" class=\"outer_table_action\">Next Month</a>";
		}
		if ($mode == "all") {
			print "<br>";
		}
		if ($mode == "all" || $mode == "weekly") {
			print "<a href=\"$_SERVER[PHP_SELF]?";
			if ($post) print "$post";
			print "&month=".date('n',$prev_week).
				  "&year=".date('Y',$prev_week).
				  "&day=".date('j',$prev_week).
				  "&mode=".$mode."\" class=\"outer_table_action\">Prev Week</a>&nbsp;/&nbsp;";
			print "<a HREF=\"$_SERVER[PHP_SELF]?";
			if ($post) print "$post";
			print "&month=".date('n',$next_week).
				  "&year=".date('Y',$next_week).
				  "&day=".date('j',$next_week).
				  "&mode=".$mode."\" class=\"outer_table_action\">Next Week</a>";
		}
	}
}?>
