<?php
//$Header: /cvsroot/tsheet/timesheet.php/simple.php,v 1.7 2005/05/23 05:39:39 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
require("class.Pair.php");
include('timesheet-menu.php');
if (!$authenticationManager->isLoggedIn()) {
    Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
    exit;
}

@session_start();

if (isset($_GET['user'])) {
	$_SESSION['userFilter'] = $_GET['user'];
}

if ( !isset($_GET['user']) && !isset($_GET['mode'])) {
	unset($_SESSION['userFilter']);
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

$uid = getFirstUser();
$projects = getAllprojects();
$users = getAllusers();

if ( !$authenticationManager->hasClearance(MANAGER) ) {
    $_SESSION['userFilter'] = $contextUser;
}
// Set default months
setReportDate($year, $month, $day, $next_week, $prev_week, $next_month, $prev_month, $time, $time_middle_month);

function format_seconds($seconds) {
    $temp = $seconds;
    $hour = (int) ($temp / (60*60));
    if ($hour < 10)
        $hour = '0'. $hour;
    
    $temp -= (60*60)*$hour;    
    $minutes = (int) ($temp / 60);
    if ($minutes < 10)
        $minutes = '0'. $minutes;
  
    $temp -= (60*$minutes);    
    $sec = $temp;
    
    if ($sec < 10)
        $sec = '0'. $sec;       // Totally wierd PHP behavior.  There needs to
                                                            // be a space after the . operator for this to work.
    return "$hour:$minutes horas";
}

if (isset($_SESSION['userFilter'])) {
	$andWhere = "AND $USER_TABLE.username ='".$_SESSION['userFilter']."'";
	foreach ($users as $key => $value) {
		if ($value['username'] == $_SESSION['userFilter']) {
			$cUser = $value['first_name']." ".$value['last_name'];
		}
	}
}else{
	$andWhere = "";
	$cUser = "UID";
}

function userDropDowm(){
	global $users;
	global $cUser;
	global $authenticationManager;

	if ( $authenticationManager->hasClearance(MANAGER) ) {
		echo '<div class="dropdown">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">'.$cUser.'
			<span class="caret"></span> <i class="fa fa-filter pull-right" aria-hidden="true"></i>
			</button>
			<ul class="dropdown-menu">';
			echo "<li><a href='timesheet-dashboard.php'>UID</a></li>";
			foreach ($users as $key => $value) {
				echo "<li><a href='?user=".$value['username']."'>".$value['first_name']." ".$value['last_name']."</a></li>";
			}
			echo '</ul>
		</div>';
	}else{
		echo "";
	}
}


    //define working varibales  
    $last_uid = -1;
    $last_task_id = -1;
    $total_time = 0;
    $grand_total_time = 0;
    $taskTotal = 0; 
    $finalTotal = 0;

	$fArr = array();
	foreach ($projects as $key => $value) {
		$proj_id = $value['proj_id'];
		$grand_total_time = 0;
		$total_time = 0;
		$last_uid = -1;
		$formatted_time = "";
		$proj_title = $value['title'];

		$query = "select $TIMES_TABLE.proj_id, $TIMES_TABLE.task_id, ".
				"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
				"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, $PROJECT_TABLE.title, $TASK_TABLE.name, ".
				"date_format(start_time, '%Y/%m/%d') as start_date, trans_num, $TIMES_TABLE.uid, ".
				"$USER_TABLE.first_name, $USER_TABLE.last_name, $TIMES_TABLE.log_message " .
				"from $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE ".
				"WHERE $TIMES_TABLE.uid=$USER_TABLE.username and end_time > 0 AND $TIMES_TABLE.proj_id='$proj_id' AND start_time >= '$year-$month-$day' AND ".
				"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND $TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
				"end_time < '".date("Y-m-d",$next_week)."' ".$andWhere." order by $USER_TABLE.uid, task_id, start_time";

		//run the query  
		list($qh,$num) = dbQuery($query);
		$nuum = 0;

		while ($data = dbResult($qh)) {
			// New project, so print out last project total time and start a new table cell.
			if ($last_uid != $data["uid"]) {
				$last_uid = $data["uid"];
				if ($grand_total_time) {
					$formatted_time = format_seconds($total_time);
					$fArr[$proj_title]['time'][] = $total_time;
				}
	  
				//$fArr[$proj_title]['users'][] = $data['uid'];
				$total_time = 0;
			}
			if ($last_task_id != $data["task_id"]) {
				$last_task_id = $data["task_id"];
				$current_task_name = stripslashes($data["name"]);
				$taskTotal = 0;
			}
            ///$tasksArr[$current_task_name]['users'][] = $data['uid'];
            $tasksArr[$current_task_name]['time'][] = $data["diff"];
            					
			$total_time += $data["diff"];
			$grand_total_time += $data["diff"];
		}

		if ($total_time) {
			$formatted_time = format_seconds($total_time);
			$fArr[$proj_title]['time'][] = $total_time;
		}
		$formatted_time = format_seconds($grand_total_time);
		$gTotal[$proj_title] = $grand_total_time;
	}

	if ( !empty($fArr) ) {
		foreach ($fArr as $key => $value) {
			//$myFinal[$key] = array_combine($value['users'],$value['time']);
			$myFinal[$key] = array_sum($value['time']);
		}
		foreach ($tasksArr as $key => $value) {
			$taskWaArrr[$key] = array_sum($value['time']);
		}
	}

	// foreach ($projects as $pKey => $pValue) {
 //      	if (array_key_exists($pValue['title'],$myFinal)) {
	//       	echo "<tr>
	//         	<td>".$pValue['title']."</td>
	//           	<td data-graph-item-color='#448fee'>".format_seconds($myFinal[$pValue['title']])."</td>
	// 	    </tr>";
 //  		}else{
 //  			echo "<tr>
	//         	<td>".$pValue['title']."</td>
	//           	<td data-graph-item-color='#448fee'>0</td>
	// 	    </tr>";
 //  		}
 //  	}
  	
?>

<!DOCTYPE html>
<html lang="en">
	<head>

	  	<title>Timesheet Dashboard</title>
	    <!-- Required meta tags always come first -->
	    <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <meta http-equiv="x-ua-compatible" content="ie=edge">

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.css">  
	 
	   <!-- jQuery first, then Tether, then Bootstrap JS. -->
   
	</head>
	<body class="newDashboard">
	<?php include ("banner.inc"); ?>
	<section class="col-md-12 heading">
        <?php $mode = "weekly"; ?>
        <div class="col-md-4 date"><?= date('F d, Y',$time); ?></div>
        <h2 class="col-md-4">TIMESHEET DASHBOARD</h2>
        <div class="col-md-4 nav">
            <? printPrevNext($time, $next_week, $prev_week, $next_month, $prev_month, $time_middle_month,"proj_id=$proj_id", $mode);?>
        </div>
    </section>


	<div class="container-fluid">

  <section class="graphp_sec">
     <div class="row">
	    <div class="col-md-6">
			<div class="left_sec">
			    <div class="row">
			      	<div class="col-md-6">
					  	<?php userDropDowm(); ?>
				  	</div>
			      	<div class="col-md-6">
						<h6> Task Timesheet</h6>
					</div>
					<div class="col-md-12">
				      	<?php if ( !empty($fArr) ) { ?>
						<table class="highchart" data-graph-container-before="1" data-graph-type="column" data-graph-color-1="#999">
						 	<thead>
						    	<tr>
							        <th>Task</th>
							        <th>Horas</th>
							    </tr>
						   </thead>
						   <tbody>
						      	<?php
							      	foreach ($taskWaArrr as $key => $value) {
								      	echo "<tr>
								        	<td>".$key."</td>
								          	<td data-graph-item-color='#448fee'>".format_seconds($value)."</td>
									    </tr>";
							      	}?>
					      	
						 	</tbody>
						</table>
						<?php }else{ echo "<center><i>No hours recorded</i></center>"; } ?>
					</div>						  
				</div>
		 	</div>
		</div>
		<div class="col-md-6">
			<div class="left_sec">
				<div class="row">
					<div class="col-md-6">
						<?php userDropDowm(); ?>
					</div>
					<div class="col-md-6">
						<h6> Project Timesheet</h6>
					</div>
					<div class="col-md-12">
						<?php if ( !empty($fArr) ) { ?>
						<table class="highchart" data-graph-container-before="1" data-graph-type="column">
						 	<thead>
						    	<tr>
							        <th>Month</th>
							        <th data-graph-item-color='#448fee'>Horas</th>
							    </tr>
						   </thead>
						   <tbody>
						      	<?php
						      	foreach ($myFinal as $key => $value) {
							      	echo "<tr>
							        	<td>".$key."</td>
							          	<td data-graph-item-color='#448fee'>".format_seconds($value)."</td>
								    </tr>";
						      	}?>
						 	</tbody>
						</table>
						<?php }else{ echo "<center><i>No hours recorded</i></center>"; } ?>
					</div>					  
				</div>
		 	</div>
		</div>

	    <div class="col-md-6">
			<div class="left_sec">
				<div class="row">
					<div class="col-md-6">
						<?php userDropDowm(); ?>
					</div>
					<div class="col-md-6">
						<h6> Task Timesheet</h6>
					</div>
					<div class="col-md-12">
						<?php if ( !empty($fArr) ) { ?>
						<table class="highchart" data-graph-container-before="1" data-graph-type="pie" data-graph-datalabels-enabled="1">
						 	<thead>
						    	<tr>
							        <th>Month</th>
							        <th>Horas</th>
							    </tr>
						   </thead>
						   <tbody>
						   		<?php
							      	foreach ($taskWaArrr as $key => $value) {
								      	echo "<tr>
								        	<td>".$key."</td>
								          	<td data-graph-name='".$key."'>".format_seconds($value)."</td>
									    </tr>";
							      	}?>

						 	</tbody>
						</table>
						<?php }else{ echo "<center><i>No hours recorded</i></center>"; } ?>
					</div>					  
				</div>
		 	</div>
		</div>
		<div class="col-md-6">
			<div class="left_sec">
				<div class="row">
					<div class="col-md-6">
						<?php userDropDowm(); ?>
					</div>
					<div class="col-md-6">
						<h6> Project Timesheet</h6>
					</div>
					<div class="col-md-12">
						<?php if ( !empty($fArr) ) { ?>
						<table class="highchart" data-graph-container-before="1" data-graph-type="pie" data-graph-datalabels-enabled="1">
						 	<thead>
						    	<tr>
							        <th>Month</th>
							        <th>Horas</th>
							    </tr>
						   </thead>
						   <tbody>
   						      	<?php
						      	foreach ($myFinal as $key => $value) {
							      	echo "<tr>
							        	<td>".$key."</td>
							          	<td data-graph-name='".$key."'>".format_seconds($value)."</td>
								    </tr>";
						      	}?>
						 	</tbody>
						</table>
						<?php }else{ echo "<center><i>No hours recorded</i></center>"; } ?>
					</div>					  
				</div>
		 	</div>
		</div>
	</div>
</section>
</div>
<? include ("footer.inc"); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/highcharts.js"></script>
<script type="text/javascript" src="js/jquery.highchartTable.js"></script>
<script>
	jQuery(document).ready(function(){
		jQuery('table.highchart').bind('highchartTable.beforeRender', function(event, highChartConfig) {
		    highChartConfig.colors = ['#df7c03', '#f2b705', '#86ad00', '#d9534f', '#0092b9', '#cd3901'];
		  }).highchartTable();
	});
  </script>
  </body>
</html>