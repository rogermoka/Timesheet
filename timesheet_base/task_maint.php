<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_maint.php,v 1.11 2005/05/17 03:38:37 vexil Exp $
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

//define the command menu
include("timesheet_menu.inc");

if (empty($proj_id))
	$proj_id = 1;
	
//make sure the selected project is valid for this client
if ($client_id != 0) {
	if (!isValidProjectForClient($proj_id, $client_id))
		$proj_id = getValidProjectForClient($client_id);
}

//set up the required queries
$query_task = "select distinct task_id, name, description,status, ".
           "DATE_FORMAT(assigned, '%M %d, %Y') as assigned,".
           "DATE_FORMAT(started, '%M %d, %Y') as started,".
           "DATE_FORMAT(suspended, '%M %d, %Y') as suspended,".
           "DATE_FORMAT(completed, '%M %d, %Y') as completed ".
           "from $TASK_TABLE ".
           "where $TASK_TABLE.proj_id=$proj_id ".
    "order by $TASK_TABLE.task_id";

 $query_project = "select distinct title, description,".
           "DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
           "DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
           "proj_status, proj_leader ".
           "from $PROJECT_TABLE ".
           "where $PROJECT_TABLE.proj_id=$proj_id";
?>

<html>
<head>
	<title>Tasks</title>
<?php
include ("header.inc");
?>
<script language="Javascript">

	function delete_task(projectId, taskId) {
				if (confirm('Deleting a task which has been used in the past will make those timesheet ' +
												'entries invalid, and may cause errors. This action is not recommended. ' + 
												'Are you sure you want to delete this task?'))
					location.href = 'task_action.php?proj_id=' + projectId + '&task_id=' + taskId + '&action=delete';
	}

</script>
</head>
<body <? include ("body.inc"); ?> >
<?
include ("banner.inc");
?>

<form name="changeForm" action="<? echo $_SERVER["PHP_SELF"]; ?>" style="margin-bottom: 0px;">										

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
												<td width="100%"><? client_select_list($client_id, 0, false, false, true, false, "submit();", false); ?></td>
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
												<td width="100%"><? project_select_list($client_id, false, $proj_id, 0, false, false, "submit();", false); ?></td>
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
						<td align="center" class="outer_table_heading" nowrap>
							Tasks</td>
						</td>
						<td align="right" nowrap>
							<? if ($proj_id != 0) { ?>
							<a href="task_add.php?proj_id=<? echo $proj_id; ?>">Add new task</a>
							<? } else { ?>
								<span class="disabledLink">Add new task</span>
							<? } ?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>
				
			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
				<tr>
					<td>					
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">					
<?  
	//execute query
	list($qh_task, $num_task) = dbQuery($query_task);
	
	//were there any results
	if ($num_task == 0) {    
		if ($proj_id == 0) {
			print "	<tr>\n";
			print "		<td align=\"center\">\n";
			print "			<i><br>Please select a client with projects, or 'All Clients'.<br><br></i>\n";
			print "		</td>\n";
			print "	</tr>\n";		
		}
		else {
			print "	<tr>\n";
			print "		<td align=\"center\">\n";
			print "			<i><br>There are no tasks for this project.<br><br></i>\n";
			print "		</td>\n";
			print "	</tr>\n";
		}
	}
	else {		
		//iterate through tasks
		for ($j=0; $j<$num_task; $j++) {
			$data_task = dbResult($qh_task);
			//start the row
?>
		<tr>
			<td>
				<table width="100%" border="0"<? if ($j+1<$num_task) print "class=\"section_body\""; ?>>
					<tr>
						<td valign="center">
							<span class="project_title"><?php echo stripslashes($data_task["name"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data_task["status"]; ?>&gt;</span><br>
								<? echo stripslashes($data_task["description"]); ?>
						</td>
						<td align="right" valign="top" nowrap>
							<span class="label">Actions:</span>
							<a href="task_edit.php?task_id=<?php echo $data_task["task_id"]; ?>">Edit</a>,
							<a href="javascript:delete_task(<?php echo $proj_id; ?>,<?php echo $data_task["task_id"]; ?>);">Delete</a>
						</td>
					</tr>
					<tr>
						<td align="left" colspan="2" align="top">
							<span class="label">Assigned persons:</span><br>
<?php
			//get assigned users
			list($qh3, $num_3) = dbQuery("select username, task_id from $TASK_ASSIGNMENTS_TABLE where task_id=$data_task[task_id]");
			if ($num_3 > 0) {
				while ($data_3 = dbResult($qh3)) {
					print "$data_3[username] ";
				}
			}
			else {
				print "<i>None</i>";
			}
?>
						</td>
					<tr>
				</table>
			</td>
		</tr>
								
<?php
		} 
	}
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
</BODY>
</HTML>

