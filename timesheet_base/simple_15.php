<?php
//$Header: /cvsroot/tsheet/timesheet.php/simple.php,v 1.7 2005/05/23 05:39:39 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
require("class.Pair.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclSimple')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&amp;clearanceRequired=" . get_acl_level('aclSimple'));
	exit;
}

// Connect to database.
$dbh = dbConnect();

//define the command menu & we get these variables from $_REQUEST:
//  $month $day $year $client_id $proj_id $task_id
include("timesheet_menu.inc");

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

//bug fix - we must display all projects
$proj_id = 0;
$task_id = 0;

//get the passed date (context date)
$todayStamp = mktime(0, 0, 0,$month, $day, $year);
$todayValues = getdate($todayStamp);
$curDayOfWeek = $todayValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = $tsx_config->get('weekstartday');

$daysToMinus = $curDayOfWeek - $startDayOfWeek;
if ($daysToMinus < 0)
	$daysToMinus += 7;

$startDate = strtotime(date("d M Y H:i:s",$todayStamp) . " -$daysToMinus days");
$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");
$layout = $tsx_config->get('simpleTimesheetLayout');

$post="";
?>
<html>
<head>
<title>Simple Weekly Timesheet for <?php echo "$contextUser" ?></title>
<?php
include ("header.inc");
?>
<script type="text/javascript">
	//define the hash table
	var projectTasksHash = {};
<?php
//get all of the projects and put them into the hashtable
$getProjectsQuery = "SELECT $PROJECT_TABLE.proj_id, " .
							"$PROJECT_TABLE.title, " .
							"$PROJECT_TABLE.client_id, " .
							"$CLIENT_TABLE.client_id, " .
							"$CLIENT_TABLE.organisation " .
						"FROM $PROJECT_TABLE, $ASSIGNMENTS_TABLE, $CLIENT_TABLE " .
						"WHERE $PROJECT_TABLE.proj_id=$ASSIGNMENTS_TABLE.proj_id AND ".
							"$ASSIGNMENTS_TABLE.username='$contextUser' AND ".
							"$PROJECT_TABLE.client_id=$CLIENT_TABLE.client_id ".
						"ORDER BY $CLIENT_TABLE.organisation, $PROJECT_TABLE.title";

list($qh3, $num3) = dbQuery($getProjectsQuery);

//iterate through results
for ($i=0; $i<$num3; $i++) {
	//get the current record
	$data = dbResult($qh3, $i);
	print("projectTasksHash['" . $data["proj_id"] . "'] = {};\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['name'] = '". addslashes($data["title"]) . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['clientId'] = '". $data["client_id"] . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['clientName'] = '". addslashes($data["organisation"]) . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['tasks'] = {};\n");
}

//get all of the tasks and put them into the hashtable
$getTasksQuery = "SELECT $TASK_TABLE.proj_id, " .
						"$TASK_TABLE.task_id, " .
						"$TASK_TABLE.name " .
					"FROM $TASK_TABLE, $TASK_ASSIGNMENTS_TABLE " .
					"WHERE $TASK_TABLE.task_id = $TASK_ASSIGNMENTS_TABLE.task_id AND ".
						"$TASK_ASSIGNMENTS_TABLE.username='$contextUser' ".
					"ORDER BY $TASK_TABLE.name";

list($qh4, $num4) = dbQuery($getTasksQuery);
//iterate through results
for ($i=0; $i<$num4; $i++) {
	//get the current record
	$data = dbResult($qh4, $i);
	print("if (projectTasksHash['" . $data["proj_id"] . "'] != null)\n");
	print("  projectTasksHash['" . $data["proj_id"] . "']['tasks']['" . $data["task_id"] . "'] = '" . addslashes($data["name"]) . "';\n");
}

?>

	//function to populate existing rows with project and task names and select the right one in each
	function populateExistingSelects() {
		//get the number of existing rows
		var existingRows = parseInt(document.getElementById('existingRows').value);
		//alert('There are ' + existingRows + ' existing rows');

		//iterate to plus one to do the additional row
		for (var i=0; i<=existingRows; i++) {
			//alert('existing row ' + i);

			//get the client, project and task id for this row
			var clientId = document.getElementById('client_row' + i).value;
			var projectId = document.getElementById('project_row' + i).value;
			var taskId = document.getElementById('task_row' + i).value;

			//get the selects
			var clientSelect = document.getElementById('clientSelect_row' + i);
			var projectSelect = document.getElementById('projectSelect_row' + i);
			var taskSelect = document.getElementById('taskSelect_row' + i);

			//add None to the selects
			clientSelect.options[clientSelect.options.length] = new Option('None', '-1');
			projectSelect.options[projectSelect.options.length] = new Option('None', '-1');
			taskSelect.options[taskSelect.options.length] = new Option('None', '-1');

			//add the clients
			//var clientId = -1;
			for (var key in projectTasksHash) {
				if (projectTasksHash[key]['clientId'] != clientId) {
					//projectSelect.options[projectSelect.options.length] = new Option('[' + projectTasksHash[key]['clientName'] + ']', -1);
					clientSelect.options[clientSelect.options.length] = new Option(projectTasksHash[key]['clientName'], projectTasksHash[key]['clientId']);
					clientId = projectTasksHash[key]['clientId'];
				}

				if (key == projectId && projectTasksHash[key]['clientId'] == clientId)
				{
					populateProjectSelect(i, clientId, key);
					clientSelect.options[clientSelect.options.length-1].selected = true;
				}
			}

			if (projectId != -1) {
				//add the tasks
				var thisProjectTasks = projectTasksHash[projectId]['tasks'];
				for (taskKey in thisProjectTasks) {
					taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

					if (taskKey == taskId)
						taskSelect.options[taskSelect.options.length-1].selected = true;
				}
			}
		}
	}

	function populateTaskSelect(row, projectId, selectedTaskId) {
		//get the task select for this row
		var taskSelect = document.getElementById('taskSelect_row' + row);

		//add the tasks
		var thisProjectTasks = projectTasksHash[projectId]['tasks'];
		for (var taskKey in thisProjectTasks) {
			taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

			if (taskKey == selectedTaskId)
				taskSelect.options[taskSelect.options.length-1].selected = true;
		}
	}

	function populateProjectSelect(row, clientId, selectedProjectId) {
		//get the project select for this row
		var projectSelect = document.getElementById('projectSelect_row' + row);

		//add the projects
		for (key in projectTasksHash) {
			if (projectTasksHash[key]['clientId'] == clientId) {
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[key]['name'], key);
				if (key == selectedProjectId)
				{
					projectSelect.options[projectSelect.options.length-1].selected = true;
				}
			}
		}
	}

	function clearTaskSelect(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		for (var i=1; i<taskSelect.options.length; i++)
			taskSelect.options[i] = null;

		//set the length back to 1
		taskSelect.options.length = 1;

		//select the 'None' option
		taskSelect.options[0].selected = true;

		onChangeTaskSelectRow(row);
	}

	function clearProjectSelect(row) {
		projectSelect = document.getElementById('projectSelect_row' + row);
		for (i=1; i<projectSelect.options.length; i++) {
			projectSelect.options[i] = null;
		}

		projectSelect.options.length = 1;
		projectSelect.options[0].selected = true;
	}

	function clearWorkDescriptionField(row) {
		descField = document.getElementById("description_row" + row);
		descField.value = "";
	}

	function rowFromIdStr(idStr) {
		var pos1 = idStr.indexOf("row") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function colFromIdStr(idStr) {
		var pos1 = idStr.indexOf("col") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function onChangeProjectSelect(idStr) {
		row = rowFromIdStr(idStr);
		clearTaskSelect(row);

		//get the project id
		var projectSelect = document.getElementById('projectSelect_row' + row);
		var projectId = projectSelect.options[projectSelect.selectedIndex].value;

		if (projectId != -1)
			//populate the select with tasks for this project
			populateTaskSelect(row, projectId);

		setDirty();
	}

	function onChangeClientSelect(idStr) {
		row = rowFromIdStr(idStr);
		clearProjectSelect(row);
		clearTaskSelect(row);

		var clientSelect = document.getElementById('clientSelect_row' + row);
		var clientId = clientSelect.options[clientSelect.selectedIndex].value;

		if (clientId != -1) {
			populateProjectSelect(row, clientId);
		}
	}

	function onChangeTaskSelect(idStr) {
		var rowNum = rowFromIdStr(idStr);
		//alert('octs called for row ' + rowNum);
		onChangeTaskSelectRow(rowNum);
	}

	function onChangeTaskSelectRow(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		//alert('octsr called for row ' + row);
		if (taskSelect.options[0].selected == true) {
			//disable fields
			for (var i=1; i<=7; i++) {
				document.getElementById('hours_row' + row + '_col' + i).disabled = true;
				document.getElementById('mins_row' + row + '_col' + i).disabled = true;
			}
		} 
		else {
			//get the total number of rows
			var totalRows = parseInt(document.getElementById('totalRows').value);
			//alert('change task droplist on row ' + row + ', totalRows=' + totalRows);

			if (row == (totalRows-1)) {
				//get the row to copy
				var tempNode = document.getElementById('row' + row);

				//clone the row
				var newNode = tempNode.cloneNode(true);

				//setup the pattern to match
				var rowRegex = new RegExp("row(\\d+)");

				//iterate through with dom and replace all name and id attributes with regexp
				replaceIdAndNameAttributes(newNode, rowRegex, totalRows);

				//increment totalRows by one
				//alert('totalRows was ' + document.getElementById('totalRows').value);
				document.getElementById('totalRows').value = parseInt(document.getElementById('totalRows').value) + 1;
				//alert('totalRows is now ' + document.getElementById('totalRows').value);

				//get the totals node
				var totalsNode = document.getElementById('totalsRow');

				//insert the new node before the totals node
				totalsNode.parentNode.insertBefore(newNode, totalsNode);

				//enable fields
				for (var i=1; i<=7; i++) {
					document.getElementById('hours_row' + row + '_col' + i).disabled = false;
					document.getElementById('mins_row' + row + '_col' + i).disabled = false;
				}

				//clear the task select
				clearTaskSelect(totalRows);

				// clear the work description field
				clearWorkDescriptionField(totalRows);

				clearProjectSelect(totalRows);

				/* //select default project
				var oldProjectSelect = document.getElementById('projectSelect_row' + row);
				var newProjectSelect = document.getElementById('projectSelect_row' + (row+1));
				newProjectSelect.options[oldProjectSelect.selectedIndex].selected = true;

				//repopulate task
				var projectId = newProjectSelect.options[newProjectSelect.selectedIndex].value;
				populateTaskSelect(row+1, projectId); */

			}

		}
		setDirty();
	}

	function onChangeWorkDescription(idStr) {
		setDirty();
	}

	//clear row and make it invisible
	function onDeleteRow(idStr) {
		var row = rowFromIdStr(idStr);
		var tr = document.getElementById('row' + row)

		// clear the task select
		clearTaskSelect(row);

		// clear the work description field
		clearWorkDescriptionField(row);

		// clear hours and minutes
		for (var i=1; i<=7; i++) {
			document.getElementById("hours_row" + row + "_col" + i).value = "";
			document.getElementById("mins_row" + row + "_col" + i).value = "";
			recalculateCol(i,idStr);
		}

		tr.style.display = "none";
	}

	function replaceIdAndNameAttributes(node, rowRegex, rowNumber) {
		while (node != null) {
			if (node.getAttribute != null && node.getAttribute("id") != null)
				node.setAttribute("id", node.getAttribute("id").replace(rowRegex, "row" + rowNumber));
			if (node.getAttribute != null && node.getAttribute("name") != null)
				node.setAttribute("name", node.getAttribute("name").replace(rowRegex, "row" + rowNumber));

			// call this function recursively for children
			// did not to work recursely with if statement like it was:
			// if (node.firstChild != null && node.firstChild.tagName != null)
			if (node.firstChild != null)
				replaceIdAndNameAttributes(node.firstChild, rowRegex, rowNumber);

			//do the same for the next sibling
			node = node.nextSibling;
		}
	}

	function recalculateRowCol(idStr) {
		recalculateRow(rowFromIdStr(idStr));
		recalculateCol(colFromIdStr(idStr),idStr);
		setDirty();
	}

	function recalculateRow(row) {
		var totalMins = 0;
		for (i=1; i<=7; i++) {
			minsinday = parseInt(document.getElementById("minsinday_" + i).value);
			//var hrsinday = minsinday/60;
			hours = parseInt(document.getElementById("hours_row" + row + "_col" + i).value);
			mins = parseInt(document.getElementById("mins_row" + row + "_col" + i).value);
			if (isNaN(hours)) {
				hours = 0;
			}
			if (isNaN(mins)) {
				mins = 0;
			}

			var minutes = hours * 60 + mins;

			if (minutes > minsinday) {
				alert("Too much time, date only has " + minsinday/60 + " hours in the day");
				document.getElementById("hours_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("mins_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				document.getElementById("mins_row" + row + "_col" + i).select();  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				return false;
			}
			totalMins += minutes;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the total cell
		var totalCell = document.getElementById("subtotal_row" + row);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function recalculateCol(col,idStr) {
		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);
		var minsinday = parseInt(document.getElementById("minsinday_" + col).value);

		var totalMins = 0;
		var row="";
		for (var i=0; i<totalRows; i++) {
			hours = parseInt(document.getElementById("hours_row" + i + "_col" + col).value);
			mins = parseInt(document.getElementById("mins_row" + i + "_col" + col).value);
			if (isNaN(hours)) {
				hours = 0;
			}
			if (isNaN(mins)) {
				mins = 0;
			}

			var minutes = hours * 60 + mins;

			totalMins += minutes;
		}

		if (totalMins > minsinday) {
			alert("Too much time, only " + minsinday/60 + " hours in the day, check your column");
			row=rowFromIdStr(idStr);
			document.getElementById("hours_row" + row + "_col" + col).value="";  //=true;
			document.getElementById("mins_row" + row + "_col" + col).value="";  //=true;
			document.getElementById("hours_row" + row + "_col" + col).select();  //=true;
			document.getElementById("mins_row" + row + "_col" + col).select();  //=true;
			document.getElementById("hours_row" + row + "_col" + col).select();  //=true;
			return false;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the total cell
		var totalCell = document.getElementById("subtotal_col" + col);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';

		recalculateGrandTotal();
	}

	function recalculateGrandTotal() {
		var totalMins = 0;
		for (var i=1; i<=7; i++) {
			var currentInnerHTML = document.getElementById("subtotal_col" + i).innerHTML;
			//get the hours
			hPos = currentInnerHTML.indexOf('h');
			hours = parseInt(currentInnerHTML.substring(0, hPos));
			if (!isNaN(hours))
				totalMins += hours * 60;

			//get the minutes
			mPos = currentInnerHTML.indexOf('m');
			mins = parseInt(currentInnerHTML.substring(hPos+7, mPos));
			if (!isNaN(mins))
				totalMins += mins;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the grand total cell
		var grandTotalCell = document.getElementById("grand_total");
		grandTotalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function setDirty() {
		document.getElementById("saveButton").disabled = false;
	}

	function validate() {
		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);

		//iterate through rows
		for (var i=0; i<totalRows; i++) {
			//iterate through cols
			for (var j=1; j<=7; j++) {
				hours = parseInt(document.getElementById("hours_row" + i + "_col" + j).value);
				mins = parseInt(document.getElementById("mins_row" + i + "_col" + j).value);
				if (isNaN(hours)) {
					hours = 0;
				}
				if (isNaN(mins)) {
					mins = 0;
				}

				var minsinday = parseInt(document.getElementById("minsinday_" + j).value);

				var minutes = hours * 60 + mins;

				if (minutes > minsinday) {
					alert("Too much time, date only has " + minsinday/60 + " hours in the day");
					document.getElementById("hours_row" + i + "_col" + j).value="";  //=true;
					document.getElementById("mins_row" + i + "_col" + j).value="";  //=true;
					document.getElementById("hours_row" + i + "_col" + j).select();  //=true;
					document.getElementById("mins_row" + i + "_col" + j).select();  //=true;
					document.getElementById("hours_row" + i + "_col" + j).select();  //=true;
					return false;
				}
			}
		}

		document.theForm.submit();
	}

</script>
</head>
<?php
echo "<body width=\"100%\" height=\"100%\" onLoad=\"populateExistingSelects();\"";
include ("body.inc");
if (isset($popup))
	echo "onLoad=window.open(\"clock_popup.php?proj_id=$proj_id&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
echo ">\n";

include ("banner.inc");
include("navcal/navcalendars.inc");
?>

<form name="theForm" action="simple_action.php" method="post">
<input type="hidden" name="year" value=<?php echo $year; ?> />
<input type="hidden" name="month" value=<?php echo $month; ?> />
<input type="hidden" name="day" value=<?php echo $day; ?> />
<input type="hidden" name="startStamp" value=<?php echo $startDate; ?> />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							Timesheet
						</td>
						<td align="middle" nowrap class="outer_table_heading">
							<?php
								$sdStr = date("M d, Y",$startDate);
								//just need to go back 1 second most of the time, but DST 
								//could mess things up, so go back 6 hours...
								$edStr = date("M d, Y",$endDate - 6*60*60); 
								echo "Week: $sdStr - $edStr"; 
							?>
						</td>
						<td align="right" nowrap>
							<!--prev / next buttons used to be here -->
						</td>
						<td align="right" nowrap>
							<input type="button" name="saveButton" id="saveButton" value="Save Changes" disabled="true" onClick="validate();" />
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading" align="center">
							Client / Project / Task<?php if(strstr($layout, 'no work description') == '') echo ' / Work Description'; ?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<?php
						//print the days of the week
						$currentDayDate = $startDate;
						$dstadj=array();
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%a", $currentDayDate);
							$dst_adjustment = get_dst_adjustment($currentDayDate);
							$dstadj[]=$dst_adjustment;
							$minsinday = ((24*60*60) - $dst_adjustment)/60;
							print "<input type=\"hidden\" id=\"minsinday_".($i+1)."\" value=\"$minsinday\" />";
							print
								"<td align=\"center\" width=\"65\">" .
								"<table width=\"65\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>" .
								"<td class=\"inner_table_column_heading\" align=\"center\">" .
								"$currentDayStr<br />" .
								//Output the numerical date in the form of day of the month
								date("d", $currentDayDate) .
								"</td></tr></table></td>\n";
							$currentDayDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
						}
						?>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">Total</td>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">delete</td>
					</tr>
					<tr>
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";


	class TaskInfo extends Pair {
		var $clientId;
		var $projectId;
		var $projectTitle;
		var $taskName;
		var $workDescription;

		function TaskInfo($value1, $value2, $projectId, $projectTitle, $taskName, $workDescription) {
			parent::Pair($value1, $value2);
			$this->projectId = $projectId;
			$this->projectTitle = $projectTitle;
			$this->taskName = $taskName;
			$this->workDescription = $workDescription;
		}
	}

	function printSpaceColumn() {
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}

	/*=======================================================================
	 ==================== Function PrintFormRow =============================
	 =======================================================================*/

	// taskId = $matchedPair->value1, daysArray = $matchedPair->value2
	// $allTasksDayTotals = int[7] and sums up the minutes for all tasks at one day
	// usage: provide an index to generate an empty row or ALL parameters to prefill the row
	function printFormRow($rowIndex, $layout, $projectId = "", $taskId = "", $workDescription = "", $startDate = null, $daysArray = NULL) {
		// print project, task and optionally work description
		global $allTasksDayTotals; //global because of PHP4 thing about passing by reference?
		$clientId="";
		?>
		<tr id="row<?php echo $rowIndex; ?>">
			<td class="calendar_cell_middle" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<?php
					switch ($layout) {
						case "no work description field":
							?>
							<td align="left" style="width:33%;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:33%;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:33%;">
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<?php
							break;

						case "big work description field":
							// big work description field
							?>
							<td align="left" style="width:100px;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:160px;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
								<br/>
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo $workDescription; ?></textarea>
							</td>
							<?php
							break;

						case "small work description field":
						default:
							// small work description field = default layout
							?>
							<td align="left" style="width:100px;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:100px;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:140px;">
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<input type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onChange="onChangeWorkDescription(this.id);" value="<?php echo $workDescription; ?>" style="width: 100%;" />
							</td>
							<?php
							break;
					}

				?>
				</tr>
			</table>
		</td>
		<?php

		printSpaceColumn();

		$weeklyTotal = 0;
		$isEmptyRow = ($daysArray == null);

		//print_r($daysArray); print "<br />";

		//print hours and minutes input field for each day

		for ($currentDay = 0; $currentDay < 7; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";

			//while we are printing times set the style
			print "<span class=\"task_time_small\">";

			//declare current days vars
			$curDaysTotal = 0;
			$curDaysHours = "";
			$curDaysMinutes = "";

			// if there is an $daysArray calculate current day's minutes and hours

			if (!$isEmptyRow) {
				$currentDayArray = $daysArray[$currentDay];

				foreach ($currentDayArray as $taskDuration) {
					$curDaysTotal += $taskDuration;
				}
				$curDaysHours = floor($curDaysTotal / 60 );
				$curDaysMinutes = $curDaysTotal - ($curDaysHours * 60);
			}

			// write summary and totals of this row

			//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay+1);
			$disabled = $isEmptyRow?'disabled="disabled" ':'';

			print "<span nowrap><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$curDaysHours\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled />h</span>";
			print "<span nowrap><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$curDaysMinutes\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled />m</span>";

			//close the times class
			print "</span>";

			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $curDaysTotal;

			// add this days total to the all tasks total for this day
			// if an array is provided by the caller
			if ($allTasksDayTotals != null) {
				$allTasksDayTotals[$currentDay] += $curDaysTotal;
			}
		}

		printSpaceColumn();

		//format the weekly total
		$weeklyTotalStr = formatMinutes($weeklyTotal);

		//print the total column
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";

		printSpaceColumn();

		// print delete button
		print "<td class=\"calendar_delete_cell\" class=\"subtotal\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>";

		//end the row
		print "</tr>";
	}

	/*=======================================================================
	 ================ end Function PrintFormRow =============================
	 =======================================================================*/

	// Get the Weekly user data.
	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$order_by_str = "$CLIENT_TABLE.organisation, $PROJECT_TABLE.title, $TASK_TABLE.name";
	list($num5, $qh5) = get_time_records($startStr, $endStr, $contextUser, 0, 0, $order_by_str);

	//we're going to put the data into an array of
	//different (unique) TASKS
	//which has an array of DAYS (7) which has
	//an array of task durations for that day

	$structuredArray = array();
	$previousTaskId = -1;
	$currentTaskId = -1;

	//iterate through results
	for ($i=0; $i<$num5; $i++) {
		//get the record for this task entry
		$data = dbResult($qh5,$i);

		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		fixStartEndDuration($data);

		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_stamp"];
		$currentTaskEndDate = $data["end_stamp"];
		$currentTaskName = stripslashes($data["taskName"]);
		$currentProjectTitle = stripslashes($data["projectTitle"]);
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription = $data["log_message"];

		//debug
		//print "<p>taskId:$currentTaskId '$data[taskName]', start time:$data[start_time_str], end time:$data[end_time_str]</p>";

		// Combine multiple entries for a given project/task & description into a single line
		// look for the current task id in the array
		$taskCount = count($structuredArray);
		unset($matchedPair);
		for ($j=0; $j<$taskCount; $j++) {
			// does(taskID [value1] && workDescription) match?
			if ($structuredArray[$j]->value1 == $currentTaskId && $structuredArray[$j]->workDescription == $currentWorkDescription) {
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

			for ($j=0; $j<7; $j++) {
				//create a task event types array
				$taskEventTypes = array();

				//add the task event types array to the days array for this day
				$daysArray[] = $taskEventTypes;
			}

			//create a new pair
			$matchedPair = new TaskInfo($currentTaskId, $daysArray,
										$currentProjectId, $currentProjectTitle,
										$currentTaskName, $currentWorkDescription
										);

			//add the matched pair to the structured array
			$structuredArray[] = $matchedPair;

			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$structuredArray[count($structuredArray)-1];

			//print "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}

		//iterate through the days array  
		$currentDayDate = $startDate;
		for ($k=0; $k<7; $k++) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");

			$duration = 0;
			if(isset($data["duration"]) && ($data["duration"] > 0) ) {
				$duration = $data["duration"];
			}

			$startsToday = (($currentTaskStartDate >= $currentDayDate ) && ( $currentTaskStartDate < $tomorrowDate ));
			$endsToday =   (($currentTaskEndDate > $currentDayDate) && ($currentTaskEndDate <= $tomorrowDate));
			$startsBeforeToday = ($currentTaskStartDate < $currentDayDate);
			$endsAfterToday = ($currentTaskEndDate > $tomorrowDate);

			if($startsToday && $endsToday ) {
				$matchedPair->value2[$k][] = $duration;
			} else if($startsToday && $endsAfterToday) {
				$matchedPair->value2[$k][] = get_duration($currentTaskStartDate, $tomorrowDate);
			} else if( $startsBeforeToday && $endsToday ) {
				$matchedPair->value2[$k][] = get_duration($currentDayDate, $currentTaskEndDate);
			} else if( $startsBeforeToday && $endsAfterToday ) {
				$matchedPair->value2[$k][] = get_duration($currentDayDate, $tomorrowDate);
			}

			$currentDayDate = $tomorrowDate;
		}
	}

	//by now we should have our results structured in such a way that it it easy to output it

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
	for ($rowIndex = 0; $rowIndex<$count; $rowIndex++) {
		$matchedPair = &$structuredArray[$rowIndex];

		printFormRow($rowIndex, $layout,
					 $matchedPair->projectId,
					 $matchedPair->value1,
					 $matchedPair->workDescription,
					 $startDate,
					 $matchedPair->value2,
					 $allTasksDayTotals);


		//store the previous task and project ids
		$previousTaskId = $matchedPair->value1;
		$previousProjectId = $matchedPair->projectId;
	}

	/////////////////////////////////////////
	//add an extra row for new data entry
	/////////////////////////////////////////

	printFormRow($count, $layout, -1, -1);

	//store a hidden form field containing the number of existing rows
	print "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"" . $count . "\" />";

	//store a hidden form field containing the total number of rows
	print "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($count+1) . "\" />";

	////////////////////////////////////////////////////
	//Changes reequired to enter data on form -define 10 entry rows

//	for ($i=0; $i<10; $i

	////////////////////////////////////////////////////

	//create a new totals row
	print "<tr id=\"totalsRow\">\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Total Hours:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
		$col++;
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = formatMinutes($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">$formattedTotal</span></td>";
	}

	//print grand total
	$formattedGrandTotal = formatMinutes($grandTotal);
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\" id=\"grand_total\">$formattedGrandTotal</span></td>";
	print "</tr>";

?>

				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>
<?php
include ("footer.inc");
?>
</body>
</html>
<?php
// vim:ai:ts=4:sw=4
?>
