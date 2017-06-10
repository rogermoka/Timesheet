<?
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}
		
//load local vars from superglobals
$client_id = $_REQUEST['client_id'];

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);
	
?>
<HTML>
<HEAD>
<TITLE>Client Info</TITLE>
<?
include ("header.inc");
?>
</HEAD>
<BODY <? include ("body.inc"); ?> >
<?
  $query = "select organisation, description, address1, address2,".
           "city, country, postal_code, contact_first_name, contact_last_name,".
           "username, contact_email, phone_number, fax_number, gsm_number, ".
           "http_url ".
           "from $CLIENT_TABLE ".
           "where $CLIENT_TABLE.client_id=$client_id";

  print "<center><table border=0 ";
  include("table.inc");
  print " width=\"100%\">\n";

  list($qh, $num) = dbQuery($query);
  if ($num > 0) {

      $data = dbResult($qh);
      print "<TR><TD COLSPAN=3><FONT SIZE=+1><B>$data[organisation]</B></FONT></TD></TR>\n";
      print "<TR><TD COLSPAN=3><I>$data[description]</I></TD></TR>\n";
      print "<TR><TD>Address1:</TD><TD COLSPAN=2 WIDTH=80%> $data[address1]</TD></TR>\n";
      print "<TR><TD>Address2:</TD><TD COLSPAN=2> $data[address2]</TD></TR>\n";
      print "<TR><TD>ZIP, City:</TD><TD COLSPAN=2> $data[postal_code] $data[city]</TD></TR>\n";
      print "<TR><TD>Country:</TD><TD COLSPAN=2> $data[country]</TD></TR>\n";
      print "<TR><TD>Contract:</TD><TD COLSPAN=2> $data[contact_first_name] $data[contact_last_name]</TD></TR>\n";
      print "<TR><TD>Phone:</TD><TD COLSPAN=2> $data[phone_number]</TD></TR>\n";
      print "<TR><TD>Fax::</TD><TD COLSPAN=2> $data[fax_number]</TD></TR>\n";
      print "<TR><TD>GSM:</TD><TD COLSPAN=2> $data[gsm_number]</TD></TR>\n";
      } else {
        print "None.";
      }
      print "</TD></tr>";
    print "</table></center>\n";
?>
</BODY>
</HTML>

