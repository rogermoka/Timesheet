<?
//$Header: /cvsroot/tsheet/timesheet.php/proj_edit.php,v 1.7 2005/05/16 01:39:57 vexil Exp $
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

//load local vars from superglobals
$proj_id = $_REQUEST['proj_id'];

//define the command menu
$commandMenu->add(new TextCommand("Back", true, "javascript:history.back()"));

$dbh = dbConnect();
list($qh, $num) = dbQuery("select proj_id, title, client_id, description, DATE_FORMAT(start_date, '%m') as start_month, ".
			    "date_format(start_date, '%d') as start_day, date_format(start_date, '%Y') as start_year, ".
			    "DATE_FORMAT(deadline, '%m') as end_month, date_format(deadline, '%d') as end_day, date_format(deadline, '%Y') as end_year, ".
			    "http_link, proj_status, proj_leader from $PROJECT_TABLE where proj_id = $proj_id order by proj_id");
$data = dbResult($qh);

list($qh, $num) = dbQuery("SELECT username from $ASSIGNMENTS_TABLE where proj_id = $proj_id");
$selected_array = array();
$i = 0;
while ($datanext = dbResult($qh)) {	
	$selected_array[$i] = $datanext["username"];
	$i++;
}
?> 

<html>
<head>
<title>Edit Project</title>
<?php include ("header.inc"); ?>
</head>
<body <? include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>
	
<form action="proj_action.php" method="post">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="proj_id" value="<? echo $data["proj_id"]; ?>">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Edit Project: <? echo stripslashes($data["title"]); ?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>			
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr>
						<td align="right">Project Title:</td>
						<td><input type="text" name="title" size="42" value="<? echo stripslashes($data["title"]); ?>" style="width: 100%;" maxlength="200"></td>
					</tr>
					<tr>
						<td align="right">Client:</td>
						<td><? client_select_list($data["client_id"], 0, false, false, false, true, "", false); ?></td>												
					</tr>
					<tr>
						<td align="right" valign="top">Description:</td>
						<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%;"><? $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></td>
					</tr>
					<tr>
						<td align="right">Start Date:</td>
						<td><? day_button("start_day",$data["start_day"]); month_button("start_month",$data["start_month"]); year_button("start_year",$data["start_year"]); ?></td>
					</tr>
					<tr>
						<td align="right">Deadline:</td>
						<td><? day_button("end_day",$data["end_day"]); month_button("end_month",$data["end_month"]); year_button("end_year",$data["end_year"]); ?></td>
					</tr>
					<tr>
						<td align="right">Status:</td>
						<td><? proj_status_list("proj_status", $data["proj_status"]); ?></td>
					</tr>
					<tr>
						<td align="right">URL:</td>
						<td><input type="text" name="url" size="42" value="<? echo $data["http_link"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right" valign="top">Assignments:</td>
						<td><? multi_user_select_list("assigned[]",$selected_array); ?></td>
					</tr>
					<tr>
						<td align="right">Project Leader:</td>
						<td><? single_user_select_list("project_leader", $data["proj_leader"]); ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Update">
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

<?php include ("footer.inc"); ?>
</BODY>
