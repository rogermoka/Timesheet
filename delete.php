<?
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

dbQuery("delete from $TIMES_TABLE where trans_num=$trans_num AND uid='$contextUser'");
Header("Location: $_SERVER[HTTP_REFERER]");
?>   
