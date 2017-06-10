<?
// $Header: /cvsroot/tsheet/timesheet.php/user_maint.php,v 1.7 2005/02/03 09:15:44 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");	
	exit;
}

// Connect to database.
$dbh = dbConnect();

//define the command menu
include("timesheet_menu.inc");

?>
<head><title>User Management Page</title>
<?
include ("header.inc");
?>
<script language="javascript">

	function deleteUser(uid, username)
	{
		//get confirmation
		if (confirm("Deleting user '" + username + "' will also remove all related project and task assignments."))
		{	
			document.userForm.action.value = "delete";
			document.userForm.uid.value = uid;
			document.userForm.username.value = username;
			document.userForm.submit();
		}
	}

	function editUser(uid, firstName, lastName, username, emailAddress, phone, billRate, password, isAdministrator)
	{
		document.userForm.uid.value = uid;
		document.userForm.first_name.value = firstName;
		document.userForm.last_name.value = lastName;
		document.userForm.username.value = username;
		document.userForm.email_address.value = emailAddress;
		document.userForm.phone.value = phone
		document.userForm.bill_rate.value = billRate;
		document.userForm.password.value = password;
		document.userForm.checkAdmin.checked = isAdministrator;
		onCheckAdmin();
		document.location.href = "#AddEdit";
	}

	function addUser()
	{
		//validation
		if (document.userForm.username.value == "")
			alert("You must enter a username that the user will log on with.");
		else if (document.userForm.password.value == "")
			alert("You must enter a password that the user will log on with.");
		else
		{
			document.userForm.action.value = "addupdate";
			document.userForm.submit();
		}
	}
	
	function onCheckAdmin() {
		document.userForm.isAdministrator.value =
			document.userForm.checkAdmin.checked;
	}
	
</script>
</HEAD>
<BODY <? include ("body.inc"); ?> >
<?
include ("banner.inc");
?>
<form action="user_action.php" name="userForm" method="post">
<input type="hidden" name="action" value="">
<input type="hidden" name="uid" value="">
	
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>
	
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
								Employees/Contractors:
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
						<td class="inner_table_column_heading">First Name</td>
						<td class="inner_table_column_heading">Last Name</td>
						<td class="inner_table_column_heading">Access</td>
						<td class="inner_table_column_heading">Login Username</td>
						<td class="inner_table_column_heading">Email Address</td>
						<td class="inner_table_column_heading">Phone Number</td>
						<td class="inner_table_column_heading">Bill Rate</td>
						<td class="inner_table_column_heading"><i>Actions</i></td>
					</tr>				
<?

list($qh,$num) = dbQuery("select * from $USER_TABLE where username!='guest' order by last_name, first_name");

while ($data = dbResult($qh)) {
	$firstNameField = empty($data["first_name"]) ? "&nbsp;": $data["first_name"];
	$lastNameField = empty($data["last_name"]) ? "&nbsp;": $data["last_name"];
	$usernameField = empty($data["username"]) ? "&nbsp;": $data["username"];
	$emailAddressField = empty($data["email_address"]) ? "&nbsp;": $data["email_address"];
	$phoneField = empty($data["phone"]) ? "&nbsp;": $data["phone"];
	$billRateField = empty($data["bill_rate"]) ? "&nbsp;": $data["bill_rate"];
	$isAdministrator = ($data["level"] >= 10);

	print "<tr>\n";
	print "<td class=\"calendar_cell_middle\">$firstNameField</td>";
	print "<td class=\"calendar_cell_middle\">$lastNameField</td>";
	if ($isAdministrator)
		print "<td class=\"calendar_cell_middle\"><span class=\"calendar_total_value_weekly\">Admin</span></td>";
	else
		print "<td class=\"calendar_cell_middle\">Basic</td>";	
	print "<td class=\"calendar_cell_middle\">$usernameField</td>";
	print "<td class=\"calendar_cell_middle\">$emailAddressField</td>";
	print "<td class=\"calendar_cell_middle\">$phoneField</td>";
	print "<td class=\"calendar_cell_middle\">$billRateField</td>";
	print "<td class=\"calendar_cell_disabled_right\">";
	print "	<a href=\"javascript:deleteUser('$data[uid]', '$data[username]')\">Delete</a>,&nbsp;\n";
	print "	<a href=\"javascript:editUser('$data[uid]', '$data[first_name]', '$data[last_name]', '$data[username]', '$data[email_address]', '$data[phone]', '$data[bill_rate]', '$data[password]', '$isAdministrator')\">Edit</a>\n";
	print "</td>\n";
	print "</tr>\n";
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
		
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							<a name="AddEdit">	Add/Update Employee/Contractor:</a>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>			
				<table width="100%" border="0" class="table_body">
					<tr>
						<td>First name:<br><input size="20" name="first_name" style="width: 100%;"></td>
						<TD>Last name:<br><input size="20" name="last_name" style="width: 100%;"></td>
						<TD>Login username:<br><input size="20" name="username" style="width: 100%;"></td>
						<TD>Email address:<br><input size="35" name="email_address" style="width: 100%;"></td>
						<TD>Phone:<br><input size="20" name="phone" style="width: 100%;"></td>
						<TD>Bill rate:<br><input size="20" name="bill_rate" style="width: 100%;"></td>
					</tr>
					<tr>
						<td colspan="5" align="left">
							<input type="checkbox" name="checkAdmin" id="checkAdmin" value="" onClick="onCheckAdmin();">This user is an administrator</input>
							<input type="hidden" name="isAdministrator" id="isAdministrator" value="false" />							
						</td>
						<td align="left">Password:<br><input type="password" size="20" NAME="password" style="width: 100%;"></td>
					</tr>
				</table>
			</td>			
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="button" name="addupdate" value="Add/Update Employee/Contractor" onclick="javascript:addUser()" class="bottom_panel_button">
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
<?
include ("footer.inc");
?>
</BODY>
</HTML>
