<?
// $Header: /cvsroot/tsheet/timesheet.php/task_info.php,v 1.5 2004/07/02 14:15:56 vexil Exp $
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
$task_id = $_REQUEST['task_id'];

//build query
$query_task = "select distinct task_id, name, description,status, ".
           "DATE_FORMAT(assigned, '%M %d, %Y') as assigned,".
           "DATE_FORMAT(started, '%M %d, %Y') as started,".
           "DATE_FORMAT(suspended, '%M %d, %Y') as suspended,".
           "DATE_FORMAT(completed, '%M %d, %Y') as completed ".
           "from $TASK_TABLE ".
           "where $TASK_TABLE.task_id=$task_id ".
							 "order by $TASK_TABLE.task_id";

//get the proj_id for this task
if (!isset($proj_id)) {
	list($qh, $num) = $proj_id = dbQuery("SELECT proj_id FROM $TASK_TABLE where task_id='$task_id'");
	$results = dbResult($qh);
	$proj_id = $results["proj_id"];
}

$query_project = "select distinct title, description,".
           "DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
           "DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
           "proj_status, proj_leader ".
           "from $PROJECT_TABLE ".
           "where $PROJECT_TABLE.proj_id=$proj_id";

?>
<html>
<head>
<title>Task Info</title>
<?
include ("header.inc");
?>
</head>
<body width="100%" height="100%" style="margin: 0px;" <? include ("body.inc"); ?> >
<table border="0" width="100%" height="100%" align="center" valign="center">
<?

  list($qh, $num) = dbQuery($query_task);
  if ($num > 0) {
    $data_task = dbResult($qh);

?>
		<tr>
			<td>
				<table width="100%" border="0" class="section_body">
					<tr>
						<td valign="center">
							<span class="project_title"><?php echo stripslashes($data_task["name"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data_task["status"]; ?>&gt;</span><br>
								<? echo stripslashes($data_task["description"]); ?>
						</td>
					</tr>
					<tr>
						<td align="left" colspan="2" align="top">
							<span class="label">Assigned persons:</span><br>
<?
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
?>
</BODY>
</HTML>

