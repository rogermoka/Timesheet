<?
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");

//continue session
session_start();

//get the logged in user
$loggedInUser = $_SESSION['loggedInUser'];

//load local vars from superglobals
$errormsg = stripslashes($_REQUEST['errormsg']);

//define the command menu
$commandMenu->add(new TextCommand("Back", true, "javascript:back()"));

?>
<HTML>
	<HEAD>
    <TITLE>Error, <? echo $loggedInUser; ?></TITLE>
<?
include ("header.inc");
?>
</HEAD>
<BODY <? include ("body.inc"); ?> >
<?
include ("banner.inc");
include ("error.inc");
include ("footer.inc");
?>
</BODY>
</HTML>
