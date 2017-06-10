<?php
//$Header: /cvsroot/tsheet/timesheet.php/stopwatch.php,v 1.5 2005/05/17 03:38:37 vexil Exp $
   
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

//load local vars from superglobals
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;
$destination = $_REQUEST["destination"];

//check that the client id is valid
if ($client_id == 0)
	$client_id = getFirstClient();

//check that project id is valid
if ($proj_id == 0)
	$task_id = 0;
 
?>
<html>
<head>
	<title>	Timesheet.php Stopwatch, <? echo $contextUser; ?></title>
<?php 
include ("header.inc");
include("client_proj_task_javascript.inc");
?>

<script language="javascript">

function doClockonoff(clockon) {
	document.mainForm.clockonoff.value = clockon;
	onSubmit();
}

function resizePopupWindow() {
	//now resize the window
	var outerTable = document.getElementById('outer_table');
	var newWidth = outerTable.offsetWidth + window.outerWidth - window.innerWidth;
	var newHeight = outerTable.offsetHeight + window.outerHeight - window.innerHeight;
	window.resizeTo(newWidth, newHeight);
}

</script>
</head>
<body style="margin: 0;"  class="face_padding_cell" <? include ("body.inc"); ?> onload="doOnLoad();">

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="outer_table">	
		<tr>
			<td width="100%" class="face_padding_cell">
					
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>			
			
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Clock On / Off Now
						</td>
					</tr>
				</table>
								
<!-- include the timesheet face up until the next start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">

		<form action="action.php" method="post" name="mainForm" id="theForm">					
		<input type="hidden" name="year" value="<? echo date('Y'); ?>">
		<input type="hidden" name="month" value="<? echo date('m'); ?>">	
		<input type="hidden" name="day" value="<? echo date('j'); ?>">
		<input type="hidden" id="client_id" name="client_id" value="<? echo $client_id; ?>">						
		<input type="hidden" id="proj_id" name="proj_id" value="<? echo $proj_id; ?>">
		<input type="hidden" id="task_id" name="task_id" value="<? echo $task_id; ?>">
		<input type="hidden" name="clockonoff" value="">
		<input type="hidden" name="fromPopupWindow" value="true">																			
		<input type="hidden" name="destination" value="<? echo $destination; ?>">
		<input type="hidden" name="origin" value="<? echo $_SERVER["PHP_SELF"]; ?>">
	
		<tr>
			<td>				
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">			
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Client:</td></tr></table></td>
													<td width="100%">
														<select id="clientSelect" name="clientSelect" onChange="onChangeClientSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																									
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Project:</td></tr></table></td>
													<td width="100%">
														<select id="projectSelect" name="projectSelect" onChange="onChangeProjectSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																		
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Task:</td></tr></table></td>
													<td width="100%">
														<select id="taskSelect" name="taskSelect" onChange="onChangeTaskSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>									
								</tr>																										
					<tr>
						<td colspan="2" valign="top">
							<table width="100%" height="100%" border="0" cellpadding="0" cellaspacing="0">				
								<tr height="100%">
									<td valign="center">
										<table width="100%" height="100%" border="0" cellpadding="0" cellaspacing="0">
											<tr height="100%">
												<td align="right">							
													<a href="javascript:doClockonoff('clockonnow')"><img src="images/clock-green.gif" width="48" height="48" border="0" align="absmiddle"></a>
												</td>
												<td nowrap>
													<a href="javascript:doClockonoff('clockonnow')"><font size="4" color="#0DB400" face="Arial">Clock on now</font></a>
												</td>
												<td align="right">							
													<a href="javascript:doClockonoff('clockoffnow')"><img src="images/clock-red.gif" width="48" height="48" border="0" align="absmiddle"></a>
												</td>
												<td nowrap>
													<a href="javascript:doClockonoff('clockoffnow')"><font size="4" color="#E81500" face="Arial">Clock off now</font></a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						</form>													
					</tr>
				</table>
			</td>
		</tr>
<!--		<tr height="0">						
			<td valign="bottom">
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="left">&copy; 2002-2003 Dominic J. Gamble</td>
						<td align="right">&copy; 1998-1999 Peter D. Kovacs.</td>
						</td>
					</tr>
				</table>
			</td>
		</tr>-->			
	</table>

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>
				
			</td>			
		</tr>
	</table>
	
		
</body>
</html>
