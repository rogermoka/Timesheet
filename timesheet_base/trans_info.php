<?
// $Header: /cvsroot/tsheet/timesheet.php/trans_info.php,v 1.6 2004/07/04 09:51:07 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
if (!$authenticationManager->isLoggedIn()) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);
  
//load local vars from superglobals
$trans_num = $_REQUEST['trans_num'];

//get the timeformat
$timeFormat = getTimeFormat();

$dateFormatString = ($timeFormat == "12") ? "%m/%d/%Y %h:%i%p": "%m/%d/%Y %H:%i";

$query = "SELECT DATE_FORMAT(start_time, '$dateFormatString') as formattedStartTime, ".
					"DATE_FORMAT(end_time, '$dateFormatString') as formattedEndTime, ".
			   "(unix_timestamp(end_time) - unix_timestamp(start_time)) as time,".
			   "log_message, " .
			   "$PROJECT_TABLE.title AS projectTitle, " .
			   "$PROJECT_TABLE.proj_status AS projectStatus, ".
			   "$TASK_TABLE.name AS taskName, " .
			   "$TASK_TABLE.status AS taskStatus, ".
			   "$CLIENT_TABLE.organisation, ".
			   "$USER_TABLE.first_name, ".
			   "$USER_TABLE.last_name " .
			   "FROM $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE, $USER_TABLE, $CLIENT_TABLE ".
			   "WHERE $PROJECT_TABLE.proj_id=$TIMES_TABLE.proj_id ".
			   "AND $TASK_TABLE.task_id=$TIMES_TABLE.task_id ".
			   "AND $TIMES_TABLE.trans_num=$trans_num ".
			   "AND $PROJECT_TABLE.client_id = $CLIENT_TABLE.client_id ".
			   "AND $USER_TABLE.username = $TIMES_TABLE.uid";
  

//print "<PRE>$data[date]\n$data[time]\n$data[log_message]\n$data[title]\n$data[client]\n$data[first_name]\n$data[last_name]</PRE>";
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

  list($qh, $num) = dbQuery($query);
  if ($num > 0) {
    $data = dbResult($qh);

?>
		<tr>
			<td>
				<table width="100%" border="0" class="section_body">
					<tr>
						<td>
							<span class="label">Project:</span>
							<span class="project_title"><?php echo stripslashes($data["projectTitle"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data["projectStatus"]; ?>&gt;</span>
						</td>
					</tr>
					<tr>
						<td>
							<span class="label">Task:</span>
							<span class="task_title"><?php echo stripslashes($data["taskName"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data["taskStatus"]; ?>&gt;</span>
						</td>
					</tr>								
					<tr>
						<td>
							&nbsp;
						</td>
					</tr>								
					<tr>
						<td>
							<span class="label">Clocked On:</span>
							<? echo $data["formattedStartTime"]; ?>&nbsp;
							<span class="label">Clocked Off:</span>
							<? echo $data["formattedEndTime"]; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="label">Duration:</span>
							<? echo formatSeconds($data["time"]); ?>
						</td>
					</tr>								
					<tr>
						<td valign="top" align="left">
							<span class="label">Log Message:</span>						
								<? echo $data["log_message"]; ?>
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
							