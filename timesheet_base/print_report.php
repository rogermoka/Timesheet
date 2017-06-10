<?
//$Header: /cvsroot/tsheet/timesheet.php/print_report.php,v 1.4 2004/07/02 14:15:56 vexil Exp $

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$loggedInUser = strtolower($_SESSION['loggedInUser']);

//change the context user
if ($_SESSION['contextUser'] == 'admin')
	$_SESSION['contextUser'] = $name;

if (!isset($proj_id)) {
	Header("Location: $PHP_SELF?proj_id=1&$QUERY_STRING");
	exit;
}

$week_tot_h = 0; $week_tot_m = 0; $week_tot_s = 0;

if (isset($first_day)) {
	$start_date = $first_day;
	$end_date = $start_date + $TIMEPERIOD_LENGTH-1;
} else {
	Header("Location: calendar.php");
	exit;
}

$qh = mysql_query("select first_name, last_name, ssn from $USER_TABLE where username='$contextUser'") or die("Select failed: ". mysql_error());
list($first, $last, $ssn) = mysql_fetch_row($qh);
mysql_free_result($qh);

list($proj_title, $proj_client, $proj_description, $proj_deadline, $proj_link) = get_project_info($proj_id);

?>
<HTML>
<BODY BGCOLOR="#FFFFFF">
<B><? echo "$first $last </b><br>$ssn<br><br>$proj_title: $proj_client<br>$proj_description" ?>
<TABLE ALIGN=CENTER BORDER=1 WIDTH="100%">
<TR>
<?

for ($i=$start_date; $i <= $end_date; $i++) {
   $date = jdtogregorian($i);
   $tot_sec = 0; $tot_min = 0; $tot_hor = 0;
   list($day,$month,$year) = explode("/",$date);
   
   list($num, $qh) = get_time_date($i, $id, $proj_id);
   if ($num > 0) {
      print "<TD WIDTH=\"14%\" VALIGN=TOP><tt>$day/$month</tt><br>";
      while (list($start_time,$end_time,$diff,$num_end) = mysql_fetch_row($qh)) {
      	list(,$data_s_time) = explode(' ',$start_time);
      	list(,$data_e_time) = explode(' ',$end_time);
      	list($s_hour,$s_min,$s_sec) = explode(':',$data_s_time);
      	$s_hour = (int) $s_hour;
      	if ($s_hour > 12)
      	   $s_hour -= 12;
      	list($e_hour,$e_min,$e_sec) = explode(':',$data_e_time);
      	$e_hour = (int) $e_hour;
      	if ($e_hour > 12)
      	   $e_hour -= 12;
      	if ($num_end > 0) {
      	   list($diffh, $diffm, $diffs) = explode(':',$diff);
            $tot_sec += $diffs;
            $tot_min += $diffm;
            $tot_hor += $diffh;
            $week_tot_s += $diffs;
            $week_tot_m += $diffm;
            $week_tot_h += $diffh;
         }
         if ($tot_sec >= 60) {
            $tot_min++;
            $tot_sec -= 60;
         }
         if ($tot_min >= 60) {
            $tot_hor++;
            $tot_min -= 60;
         }
         if ($week_tot_s >= 60) {
            $week_tot_m++;
            $week_tot_s -= 60;
         }
         if ($week_tot_m >= 60) {
            $week_tot_h++;
            $week_tot_m -= 60;
         }
         if ($num_end > 0) 
               print "<font size=\"-1\"><br><tt>$s_hour:$s_min:$s_sec&nbsp;to<br>$e_hour:$e_min:$e_sec</tt><br></font>";
            else
               print "<font size=\"-1\"><br><tt>$s_hour:$s_min:$s_sec&nbsp;</tt><br></font>";
      }
      print "<font size=\"-1\"><tt>${tot_hor}h ${tot_min}m ${tot_sec}s</tt><br></font>";
      print "</TD>";
   } else {
      //Print blank day.
      print "<TD WIDTH=\"14%\" VALIGN=TOP><TT>$day/$month<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TT></TD>";
   }
   if (($i-5)%7 == 0) {
      print_weekly_totals();
      print "</TR>\n<TR>";
   }
}
print "</TR><TR><TD COLSPAN=7 ALIGN=RIGHT><br>Pay Period Total: ${pp_h}h ${pp_m}m ${pp_s}s<br>&nbsp;</TD>";
?>
</TR>
</TABLE>
<P ALIGN=RIGHT><br><br>
<tt>Adjustments:&nbsp;______________<br><br><br>
Total for Pay Period: ______________</tt>
<p ALIGN=LEFT>
<TT>
<br><br>Employee Signature: _______________________________________________<br><br><br><br>
Supervisor Signature: ____________________________________________
