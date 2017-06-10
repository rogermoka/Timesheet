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

//load local vars from superglobals
$action = $_REQUEST["action"];
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;
$organisation = isset($_POST["organisation"]) ? $_POST["organisation"]: "";
$description = isset($_POST['description']) ? $_POST['description']: "";
$address1 = isset($_POST['address1']) ? $_POST['address1']: "";
$address2 = isset($_POST['address2']) ? $_POST['address2']: "";
$city = isset($_POST['city']) ? $_POST['city']: "";
$country = isset($_POST['country']) ? $_POST['country']: "";
$postal_code = isset($_POST['postal_code']) ? $_POST['postal_code']: "";
$contact_first_name = isset($_POST['contact_first_name']) ? $_POST['contact_first_name']: "";
$contact_last_name = isset($_POST['contact_last_name']) ? $_POST['contact_last_name']: "";
$client_username = isset($_POST['client_username']) ? $_POST['client_username']: "";
$contact_email = isset($_POST['contact_email']) ? $_POST['contact_email']: "";
$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number']: "";
$fax_number = isset($_POST['fax_number']) ? $_POST['fax_number']: "";
$gsm_number = isset($_POST['gsm_number']) ? $_POST['gsm_number']: "";
$http_url = isset($_POST['http_url']) ? $_POST['http_url']: "";
  
if ($_REQUEST['action'] == "add") {
	dbquery("INSERT INTO $CLIENT_TABLE VALUES ('$client_id','$organisation','$description','$address1','$city'," .
	"'L','$country','$postal_code','$contact_first_name','$contact_last_name','$client_username'," .
	"'$contact_email','$phone_number','$fax_number','$gsm_number','$http_url','$address2')");
} 
elseif ($action == "edit") {	
	//create the query
	$query = "UPDATE $CLIENT_TABLE SET organisation='$organisation',".
		"description='$description',address1='$address1',city='$city',".
		"country='$country',postal_code='$postal_code',".
		"contact_first_name='$contact_first_name',".
		"contact_last_name='$contact_last_name',username='$client_username',".
		"contact_email='$contact_email',phone_number='$phone_number',".
		"fax_number='$fax_number',gsm_number='$gsm_number',".
		"http_url='$http_url',address2='$address2' ".
		"WHERE client_id=$client_id ";

	//run the query
	list($qh,$num) = dbquery($query);
}
elseif ($action == "delete") {
	//find out if this client is in use
	list($qh,$num) = dbQuery("select * from $PROJECT_TABLE where client_id='$client_id'");
	if ($num > 0)
		errorPage("You cannot delete a client for which there are projects. Please delete the projects first.");
	else		
		dbquery("DELETE from $CLIENT_TABLE WHERE client_id='$client_id'");
}

Header("Location: client_maint.php");
?>
