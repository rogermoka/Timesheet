<?php
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
$loggedInUser = strtolower($_SESSION['loggedInUser']);

$passwd1 = "";
$passwd2 = "";
$old_pass = "";

//load local vars from superglobals
if (isset($_POST["action"])) {
	if (!isset($_POST["passwd1"]) || !isset($_POST["passwd2"]) || !isset($_POST["old_pass"]))
		errorPage("Please fill out all fields.");
	$passwd1 = $_POST['passwd1'];
	$passwd2 = $_POST['passwd2'];
	$old_pass = $_POST['old_pass'];
}

//get todays values
$today = time();
$today_year = date("Y", $today);
$today_month = date("n", $today);
$today_day = date("j", $today);

//define the command menu
include("timesheet_menu.inc");

//check for guest user
if ($loggedInUser == 'guest')
  $errormsg = "Guest may not change password.";

//check that passwords match
if ($passwd1 != $passwd2)
  $errormsg = "Passwords do not match, please try again";

if (empty($errormsg) && !empty($old_pass))
{
	$qh = mysql_query("select password, $DATABASE_PASSWORD_FUNCTION('$old_pass') from $USER_TABLE where username='$contextUser'") or die("Unable to select ". mysql_error());
	list($check1, $check2) = mysql_fetch_row($qh);
	if ($check1 != $check2)	{
  	$errormsg = "Wrong password, sorry!";
	}
	else	{
		$qh = mysql_query("update $USER_TABLE set password=$DATABASE_PASSWORD_FUNCTION('$passwd1') where username='$contextUser'");
		Header("Location: calendar.php");
		exit;
	}
}

//if errors, redirect to an error page.
if (!empty($errormsg)) {
  Header("Location: error.php?errormsg=$errormsg");
  exit;
}

?>
<html>
<head>
<title>Change Password for user <? echo $contextUser; ?></title>
<?php include ("header.inc"); ?>
</head>
<body <? include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="changepwd.php" method="post">
<input type="hidden" name="action" value="changePassword" />

<table width="436" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Change Password:
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
						<td width="150" align="right" nowrap>Old Password:</td>
						<td><input type="password" name="old_pass" style="width: 100%;"></td>
					</tr>
					<tr>
						<td width="150" align="right" nowrap>New Password:</td>
						<td><input type="password" name="passwd1" style="width: 100%;"></td>
					</tr>
					<tr>
						<td width="150" align="right" nowrap>New Password (again):</td>
						<td><input type="password" name="passwd2" style="width: 100%;"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Change!">
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
