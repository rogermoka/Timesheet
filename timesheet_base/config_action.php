<?
// $Header: /cvsroot/tsheet/timesheet.php/config_action.php,v 1.6 2005/02/03 08:06:10 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION["contextUser"]);

//load local vars from superglobals
$action = $_REQUEST["action"];
$headerhtml = isset($_REQUEST["headerhtml"]) ? $_REQUEST["headerhtml"]: "";
$bodyhtml = isset($_REQUEST["bodyhtml"]) ? $_REQUEST["bodyhtml"]: "";
$footerhtml = isset($_REQUEST["footerhtml"]) ? $_REQUEST["footerhtml"]: "";
$errorhtml = isset($_REQUEST["errorhtml"]) ? $_REQUEST["errorhtml"]: "";
$bannerhtml = isset($_REQUEST["bannerhtml"]) ? $_REQUEST["bannerhtml"]: "";
$tablehtml = isset($_REQUEST["tablehtml"]) ? $_REQUEST["tablehtml"]: "";
$locale = isset($_REQUEST["locale"]) ? $_REQUEST["locale"]: "";
$timezone = isset($_REQUEST["timezone"]) ? $_REQUEST["timezone"]: "";
$timeformat= isset($_REQUEST["timeformat"]) ? $_REQUEST["timeformat"]: "";
$headerReset = isset($_REQUEST["headerReset"]) ? $_REQUEST["headerReset"]: false;
$bodyReset = isset($_REQUEST["bodyReset"]) ? $_REQUEST["bodyReset"]: false;
$footerReset = isset($_REQUEST["footerReset"]) ? $_REQUEST["footerReset"]: false;
$errorReset = isset($_REQUEST["errorReset"]) ? $_REQUEST["errorReset"]: false;
$bannerReset = isset($_REQUEST["bannerReset"]) ? $_REQUEST["bannerReset"]: false;
$tableReset = isset($_REQUEST["tableReset"]) ? $_REQUEST["tableReset"]: false;
$localeReset = isset($_REQUEST["localeReset"]) ? $_REQUEST["localeReset"]: false;
$timezoneReset = isset($_REQUEST["timezoneReset"]) ? $_REQUEST["timezoneReset"]: false;
$timeformatReset = isset($_REQUEST["timeformatReset"]) ? $_REQUEST["timeformatReset"]: false;
$useLDAP = isset($_REQUEST["useLDAP"]) ? $_REQUEST["useLDAP"]: false;
$LDAPScheme = $_REQUEST["LDAPScheme"];
$LDAPHost = $_REQUEST["LDAPHost"];
$LDAPPort = $_REQUEST["LDAPPort"];
$LDAPBaseDN = $_REQUEST["LDAPBaseDN"];
$LDAPUsernameAttribute = $_REQUEST["LDAPUsernameAttribute"];
$LDAPSearchScope = $_REQUEST["LDAPSearchScope"];
$LDAPFilter = $_REQUEST["LDAPFilter"];
$LDAPProtocolVersion = $_REQUEST["LDAPProtocolVersion"];
$LDAPBindUsername = $_REQUEST["LDAPBindUsername"];
$LDAPBindPassword = $_REQUEST["LDAPBindPassword"];
$weekstartday = isset($_REQUEST["weekstartday"]) ? $_REQUEST["weekstartday"]: 0;
$weekStartDayReset = isset($_REQUEST["weekStartDayReset"]) ? $_REQUEST["weekStartDayReset"]: false;
 
	function resetConfigValue($fieldName)	{	
		include("table_names.inc");
		
		//get the default value
		list($qh, $num) = dbQuery("SELECT $fieldName FROM $CONFIG_TABLE WHERE config_set_id='0';");
		$resultset = dbResult($qh);
		
		//set it
		dbQuery("UPDATE $CONFIG_TABLE SET $fieldName='" . $resultset[$fieldName] . "' WHERE config_set_id='1';");
	}

if (!isset($action))	{
	Header("Location: $HTTP_REFERER");
}
elseif ($action == "edit") {	
	$headerhtml = addslashes(unhtmlentities(trim($headerhtml)));
	$bodyhtml = addslashes(unhtmlentities(trim($bodyhtml)));
	$footerhtml = addslashes(unhtmlentities(trim($footerhtml)));
	$errorhtml = addslashes(unhtmlentities(trim($errorhtml)));
	$bannerhtml = addslashes(unhtmlentities(trim($bannerhtml)));
	$tablehtml = addslashes(unhtmlentities(trim($tablehtml)));
	$locale = addslashes(unhtmlentities(trim($locale)));
	$timezone = addslashes(unhtmlentities(trim($timezone)));
	$query = "UPDATE $CONFIG_TABLE SET ".
		"headerhtml='$headerhtml',".
		"bodyhtml='$bodyhtml',".
		"footerhtml='$footerhtml',".
		"errorhtml='$errorhtml',".
		"bannerhtml='$bannerhtml',".
		"tablehtml='$tablehtml',".
		"locale='$locale',".
		"timezone='$timezone',".
		"timeformat='$timeformat', ".		
		"useLDAP='$useLDAP', " .
		"LDAPScheme='$LDAPScheme', " .
		"LDAPHost='$LDAPHost', " .
		"LDAPPort='$LDAPPort', " .
		"LDAPBaseDN='$LDAPBaseDN', " .
		"LDAPUsernameAttribute='$LDAPUsernameAttribute', " .
		"LDAPSearchScope='$LDAPSearchScope', " .
		"LDAPFilter='$LDAPFilter', " .	
		"LDAPProtocolVersion='$LDAPProtocolVersion', " .
		"LDAPBindUsername='$LDAPBindUsername', ".
		"LDAPBindPassword='$LDAPBindPassword', ".
		"weekstartday='$weekstartday' " .	
		"WHERE config_set_id='1';";
	list($qh,$num) = dbquery($query);

	if ($headerReset == true)
		resetConfigValue("headerhtml");
	if ($bodyReset == true)
		resetConfigValue("bodyhtml");
	if ($footerReset == true)
		resetConfigValue("footerhtml");
	if ($errorReset == true)
		resetConfigValue("errorhtml");
	if ($bannerReset == true)
		resetConfigValue("bannerhtml");
	if ($tableReset == true)
		resetConfigValue("tablehtml");
	if ($localeReset == true)
		resetConfigValue("locale");
	if ($timezoneReset == true)
		resetConfigValue("timezone");
	if ($timeformatReset == true)
		resetConfigValue("timeformat");
	if ($weekStartDayReset == true)
		resetConfigValue("weekstartday");
}

//return to the config.php page  
Header("Location: config.php");

?>
