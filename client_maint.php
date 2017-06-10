<?php
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

//make sure "No Client exists with client_id of 1
//execute the query	
tryDbQuery("INSERT INTO $CLIENT_TABLE VALUES (1,'No Client', 'This is required, do not edit or delete this client record', '', '', '', '', '', '', '', '', '', '', '', '', '', '');");
tryDbQuery("UPDATE $CLIENT_TABLE set organisation='No Client' WHERE client_id='1'");

//define the command menu
include("timesheet_menu.inc");

?>

<HTML>
<HEAD>
<TITLE>Client Management Page</TITLE>
<?
include ("header.inc");
?>
<script language="Javascript">

	function delete_client(clientId) {
				if (confirm('Are you sure you want to delete this client?'))
					location.href = 'client_action.php?client_id=' + clientId + '&action=delete';
	}

</script>
</HEAD>
<BODY <? include ("body.inc"); ?> >
<?
include ("banner.inc");
?>
<form action="client_action.php" method="post">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							Clients
						</td>
						<td align="right">
							<a href="client_add.php" class="outer_table_action">Add new client</A>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>			
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
<?php

//execute the query	
list($qh,$num) = dbQuery("select * from $CLIENT_TABLE where client_id > 1 order by organisation");

//are there any results?
if ($num == 0) {
		print "<tr><td align=\"center\" colspan=\"5\"><br>There are currently no clients.<br><br></td></tr>";
}
else {

?>
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Organisation</td>
						<td class="inner_table_column_heading">Contact Name</td>
						<td class="inner_table_column_heading">Phone</td>
						<td class="inner_table_column_heading">Contact Email</td>
						<td class="inner_table_column_heading"><i>Actions</i></td>
					</tr>			
<?php

	while ($data = dbResult($qh)) {
		$organisationField = stripslashes($data["organisation"]);
		if (empty($organisationField))
			$organisationField = "&nbsp;";
		$contactNameField = $data["contact_first_name"] . "&nbsp;" . $data["contact_last_name"];
		$phoneField = $data["phone_number"];
		if (empty($phoneField))
			$phoneField = "&nbsp;";
		$emailField = $data["contact_email"];
		if (empty($emailField))
			$emailField = "&nbsp;";
   print "<tr>";
   print "<td class=\"calendar_cell_middle\"><A HREF=\"javascript:void(0)\" ONCLICK=window.open(\"client_info.php?client_id=$data[client_id]\",\"ClientInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=240\")>$organisationField</A></TD>";
   print "<td class=\"calendar_cell_middle\">$contactNameField</td>";   
   print "<td class=\"calendar_cell_middle\">$phoneField</td>";
   print "<td class=\"calendar_cell_middle\">$emailField</td>";
   print "<td class=\"calendar_cell_disabled_right\">\n";
		print "	<a href=\"javascript:delete_client($data[client_id]);\">Delete</a>,&nbsp;\n";
		print "	<a href=\"client_edit.php?client_id=$data[client_id]\">Edit</a>\n";
		print "</td>\n";
  }
}
?>
				</TABLE>
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
