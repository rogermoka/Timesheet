<?

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

?>
<html>
<head>
<title>Add a new Client</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>
<form action="client_action.php" method="post">
<input type="hidden" name="action" value="add">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Add New Client
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
						<td align="right">Organisation:</td>
						<td><input size="60" name="organisation" style="width: 100%;" maxlength="64"></td>
					</tr>
					<tr>
						<td valign="top" align="right">Description:</td>
						<td><textarea name="description" rows="4" cols="58" style="width: 100%;"></textarea></td>
					</tr>
					<tr>
						<td align="right">Address1:</td>
						<td><input size="60" name="address1" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Address2:</td>
						<td><input size="60" name="address2" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">City:</td>
						<td><input size="60" name="city" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Country:</td>
						<td><input size="60" name="country" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Postal Code:</td>
						<td><input size="13" name="postal_code"></td>
					</tr>
					<tr>
						<td align="right">Contact Firstname:</td>
						<td><input size="60" name="contact_first_name" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Contact Lastname:</td>
						<td><input size="60" name="contact_last_name" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Username:</td>
						<td><input size="32" name="client_username" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Contact email:</td>
						<td><input size="60" name="contact_email" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Phone Number:</td>
						<td><input size="20" name="phone_number" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Fax Number:</td>
						<td><input size="20" name="fax_number" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Mobile Number:</td>
						<td><input size="20" name="gsm_number" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Website:</td>
						<td><input size="60" name="http_url" style="width: 100%;"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" name="add" value="Add New Client">
						</td>
					</tr>
				</table>
			</td>		</tr>
	</table>	

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>
		
</form>
	
<?php include ("footer.inc"); ?>
</BODY>
</HTML>
