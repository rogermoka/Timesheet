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

//load local vars from superglobals
$client_id = $_REQUEST['client_id'];

//define the command menu
$commandMenu->add(new TextCommand("Back", true, "javascript:history.back()"));

//build the query
$query = "select client_id, organisation, description, address1, address2,".
           "city, country, postal_code, contact_first_name, contact_last_name,".
           "username, contact_email, phone_number, fax_number, gsm_number, ".
           "http_url ".
           "from $CLIENT_TABLE ".
           "where $CLIENT_TABLE.client_id=$client_id";

//run the query
list($qh, $num) = dbQuery($query);
$data = dbResult($qh);

?>
<html>
<head>
<title>Modify client information</title>
<?php include ("header.inc"); ?>
</head>
<body <? include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="client_action.php" method="post">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="client_id" value="<? echo $client_id ?>">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Edit Client: <? echo $data["organisation"]; ?>
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
						<td><input size="60" name="organisation" value="<? echo $data["organisation"]; ?>" style="width: 100%;" maxlength="64"></td>
					</tr>
					<tr>
						<td valign="top" align="right">Description:</td>
						<td>
							<textarea name="description" rows="4" cols="58" style="width: 100%;"><? echo trim($data["description"]); ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right">Address1:</td>
						<td><input size="60" name="address1" value="<? echo $data["address1"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Address2:</td>
						<td><input size="60" name="address2" value="<? echo $data["address2"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">City:</td>
						<td><input size="60" name="city"  value="<? echo $data["city"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Country:</td>
						<td><input size="60" name="country" value="<? echo $data["country"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Postal Code:</td>
						<td><input size="13" name="postal_code" value="<? echo $data["postal_code"]; ?>"></td>
					</tr>
					<tr>
						<td align="right">Contact Firstname:</td>
						<td><input size="60" name="contact_first_name" value="<? echo $data["contact_first_name"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Contact Lastname:</td>
						<td><input size="60" name="contact_last_name" value="<? echo $data["contact_last_name"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Username:</td>
						<td><input size="32" name="username" value="<? echo $data["username"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Contact email:</td>
						<td><input size="60" name="contact_email" value="<? echo $data["contact_email"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Phone Number:</td>
						<td><input size="20" name="phone_number" value="<? echo $data["phone_number"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Fax Number:</td>
						<td><input size="20" name="fax_number" value="<? echo $data["fax_number"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Mobile Number:</td>
						<td><input size="20" name="gsm_number" value="<? echo $data["gsm_number"]; ?>" style="width: 100%;"></td>
					</tr>
					<tr>
						<td align="right">Website:</td>
						<td><input size="60" name="http_url" value="<? echo $data["http_url"]; ?>" style="width: 100%;"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>						
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" name="edit" value="Submit Changes">
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
</BODY>
</HTML>
