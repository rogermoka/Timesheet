<?
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
$proj_id = $_REQUEST['proj_id'];

	$query_project = "select distinct title, description,".
           "DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
           "DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
           "proj_status, proj_leader ".
           "from $PROJECT_TABLE ".
           "where $PROJECT_TABLE.proj_id=$proj_id";

	$query = "select distinct $PROJECT_TABLE.title, $PROJECT_TABLE.proj_id, $PROJECT_TABLE.client_id, $CLIENT_TABLE.organisation, ".
    "$PROJECT_TABLE.description, DATE_FORMAT(start_date, '%M %d, %Y') as start_date, DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
    "$PROJECT_TABLE.proj_status, http_link ".
    "from $PROJECT_TABLE, $CLIENT_TABLE, $USER_TABLE ".
    "where $PROJECT_TABLE.proj_id=$proj_id  ";
    
//set up query
$query = "select distinct $PROJECT_TABLE.title, $PROJECT_TABLE.proj_id, $PROJECT_TABLE.client_id, ".
						"$CLIENT_TABLE.organisation, $PROJECT_TABLE.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
    				"$PROJECT_TABLE.proj_status, http_link, proj_leader ".
    				"FROM $PROJECT_TABLE, $CLIENT_TABLE, $USER_TABLE ".
         "WHERE $PROJECT_TABLE.proj_id=$proj_id AND ".
 						"$CLIENT_TABLE.client_id=$PROJECT_TABLE.client_id ".
    				"ORDER BY $PROJECT_TABLE.proj_id";
?>
<html>
<head>
<title>Project Info</title>
<?
include ("header.inc");
?>
</head>
<body width="100%" height="100%" style="margin: 0px;" <? include ("body.inc"); ?> >
<table border="0" width="100%" height="100%" align="center" valign="center">
<?
  list($qh, $num) = dbQuery($query);
  if ($num > 0) {

			//get the current record
			$data = dbResult($qh);

			//strip slashes
			$data["title"] = stripslashes($data["title"]);
			$data["organisation"] = stripslashes($data["organisation"]);
			$data["description"] = stripslashes($data["description"]);

			list($billqh, $bill_num) = dbquery("select sum(unix_timestamp(end_time) - unix_timestamp(start_time)) as total_time, ".
					 "sum(bill_rate * ((unix_timestamp(end_time) - unix_timestamp(start_time))/(60*60))) as billed ".
					 "from $TIMES_TABLE, $USER_TABLE ".
					 "where end_time > 0 AND $TIMES_TABLE.proj_id = $data[proj_id] AND $USER_TABLE.username = $TIMES_TABLE.uid ");
			$bill_data = dbResult($billqh);      
			
			//start the row
?>
								<tr>
									<td>
										<table width="100%" border="0" class="section_body">
											<tr>
												<td valign="center">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\"><span class=\"project_title\">$data[title]</span></a>";
			else
				print "<span class=\"project_title\">$data[title]</span>";

			print "&nbsp;&nbsp;<span class=\"project_status\">&lt;$data[proj_status]&gt;</span>"
?>
												</td>
												<td align="right">
<?php
			if (isset($data["start_date"]) && $data["start_date"] != '' && $data["deadline"] != '')			
				print "<span class=\"label\">Start:</span> $data[start_date]<br><span class=\"label\">Deadline:</span> $data[deadline]";
			else
				print "&nbsp;";
?>			
												</td>
											</tr>
											<tr>
												<td><?php echo $data["description"]; ?><br></td>			
												<td valign="top" align="right"><span class="label">Client:</span> <?php echo $data["organisation"]; ?></td>
											</tr>			
											<tr>
												<td colspan="2" width="100%">
													<table width="100%" border="0" cellpadding="0" cellspacing="0">
														<tr>
															<td width="70%">
																<table border="0" cellpadding="0" cellspacing="0">
																	<tr>
																		<td>											
																			<span class="label">Total time:</span> <?php echo (isset($bill_data["total_time"]) ? formatSeconds($bill_data["total_time"]): "0h 0m"); ?>
																			<? if ($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) { ?>
																			<br><span class="label">Total bill:</span> <b>$<?php echo (isset($bill_data["billed"]) ? $bill_data["billed"]: "0.00"); ?></b>
																			<? } ?>
																		</td>												
																	</tr>
																	<tr><td>&nbsp;</td></tr>

																		
<?php      

			//display project leader
			print "<tr><td><span class=\"label\">Project Leader:</span> $data[proj_leader] </td></tr>";

			//display assigned users      
			list($qh2, $num_workers) = dbQuery("select distinct username from $ASSIGNMENTS_TABLE where proj_id = $data[proj_id]");
			if ($num_workers == 0) {
				print "<tr><td><font size=\"-1\">Nobody assigned to this project</font></td></tr>\n";
			}
			else {
				$workers = '';
				print "<tr><td><span class=\"label\">Assigned Users:</span> ";
				for ($k = 0; $k < $num_workers; $k++) {
					$worker = dbResult($qh2);
					$workers .= "$worker[username], ";
				}

				$workers = ereg_replace(", $", "", $workers);
				print $workers;
				print "</td></tr>";
			}
			
?>


																</table>							    	  
															</td>
															<td width="30%">
																<div class="project_task_list">
																	<a href="task_maint.php?proj_id=<?php echo $data["proj_id"]; ?>"><span class="label">Tasks:</span></a>&nbsp; &nbsp;<br>
<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("select name, task_id FROM $TASK_TABLE WHERE proj_id=$data[proj_id]");
			
			//are there any tasks?
			if ($num_tasks > 0) {
				while ($task_data = dbResult($qh3)) {
					$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					print "<a href=\"javascript:void(0)\" onclick=window.open(\"task_info.php?proj_id=$data[proj_id]&task_id=$task_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220\")>$taskName</a><br>";
				}
			} 
			else
				print "None.";
?>						
																</div>
															</td>
														</tr>
													</table>
						 						</td>
						 					</tr>
						 				</table>
						 			</td>
						 		</tr>
 					
<?php      
		}																																											
?>
				</table>
</BODY>
</HTML>

