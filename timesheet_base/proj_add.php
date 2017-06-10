<?
//$Header: /cvsroot/tsheet/timesheet.php/proj_add.php,v 1.9 2005/05/16 01:39:57 vexil Exp $
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
$commandMenu->add(new TextCommand("Back", true, "javascript:history.back()"));

//load client id from superglobals
$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id']: 1;
	
?>
<html>
<head>
<title>Add New Project</title>
<?php include ("header.inc"); ?>
</head>
<body <? include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>
	
<form action="proj_action.php" method="post">
<input type="hidden" name="action" value="add">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Add New Project
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
						<td><input type="text" name="title" size="42" style="width: 100%;" maxlength="200"></td>
					</tr>
					<tr>
						<td align="right">Client:</td>
						<td><? client_select_list($client_id, 0, false, false, false, true, "", false); ?></td>
					</tr>
					<tr>
						<td align="right" valign="top">Description:</td>
						<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%;"></textarea></td>
					</tr>
					<tr>
						<td align="right">Start Date:</td>
						<td><? day_button("start_day"); month_button("start_month"); year_button("start_year"); ?></td>
					</tr>
					<tr>
						<td align="right">Deadline:</td>
						<td><? day_button("end_day"); month_button("end_month"); year_button("end_year"); ?></td>
					</tr>
					<tr>
						<td align="right">Status:</td>
						<td><? proj_status_list("proj_status", "Started"); ?></td>
					</tr>
					<tr>
						<td align="right">URL:</td>
						<td><input type="text" name="url" size="42" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right" valign="top">Assignments:</td>
						<td><? multi_user_select_list("assigned[]"); ?></td>
					</tr>
					<tr>
						<td align="right">Project Leader:</td>
						<td><? single_user_select_list("project_leader"); ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" name="add" value="Add New Project">
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
	
<?php include("footer.inc"); ?>
</body>
</html>
