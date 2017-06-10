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

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

$uid = getFirstUser();
$projects = getAllprojects();

if ( $authenticationManager->hasClearance(MANAGER) ) {
    $users = getAllusers();
}else{
    $users['0']['username'] = $contextUser;
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

    //define working varibales  
    $last_uid = -1;
    $last_task_id = -1;
    $total_time = 0;
    $grand_total_time = 0;
    $taskTotal = 0; 
    $finalTotal = 0;

?>

<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<title>Timesheet Full Report</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="newDashboard">
    <div class="containe">
        <?php include ("banner.inc"); ?>
        <div class="row">
            <div class="col-md-12 table-title">
                <section class="col-md-12 heading">
                    <?php $mode = "weekly"; ?>
                    <div class="col-md-4 date"><?= date('F d, Y',$time); ?></div>
                    <h2 class="col-md-4">FULL TIMESHEET REPORT</h2>
                    <div class="col-md-4 nav">
                        <? printPrevNext($time, $next_week, $prev_week, $next_month, $prev_month, $time_middle_month,"proj_id=$proj_id", $mode);?>
                        <div class="downLoad">
                            <a href="csv/timesheet-full_report.csv"><i class="fa fa-download" aria-hidden="true"></i> CSV</a>
                        </div>
                    </div>
                </section>
                    <?php
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
                        "end_time < '".date("Y-m-d",$next_week)."' order by $USER_TABLE.uid, task_id, start_time";

                        //run the query  
                        list($qh,$num) = dbQuery($query);
                        $nuum = 0;

                        while ($data = dbResult($qh)) {
                            // New project, so print out last project total time and start a new table cell.
                            if ($last_uid != $data["uid"]) {
                                $last_uid = $data["uid"];
                                if ($grand_total_time) {
                                    $formatted_time = format_seconds($total_time);
                                }
                                $total_time = 0;
                            }
                            if ($last_task_id != $data["task_id"]) {
                                $last_task_id = $data["task_id"];
                                $current_task_name = stripslashes($data["name"]);
                                $taskTotal = 0;
                            }
                            $fullArr[$proj_title][$current_task_name]['users'][] = $data['uid'];
                            $taskTotal += $data["diff"];
                            $total_time += $data["diff"];
                            $grand_total_time += $data["diff"];

                            if ($last_task_id == $data["task_id"] ) {
                                if ($taskTotal) {
                                    $fullArr[$proj_title][$current_task_name]['time'][] = $taskTotal;
                                }
                            }
                        }
                        
                        if ($total_time) {
                            $formatted_time = format_seconds($total_time);
                            }
                        $formatted_time = format_seconds($grand_total_time);

                        $finalTotal += $grand_total_time;
                        $gTotal[$proj_title] = $grand_total_time;

                    }

                    $headerArr = array();
                    $overAlldata = array();
                    $footerArr = array();

                    if ( !empty($fullArr) ) {

                        foreach ($fullArr as $key => $value) {
                            foreach ($value as $Ikey => $Ivalue) {
                                $myFinal[$key][$Ikey] = array_combine($Ivalue['users'],$Ivalue['time']);
                            }
                        }?>
                        <table class="table table-bordered table-fill">
                            <tr class="table_doc">
                                <th>
                                    PROJECTS/TASKS 
                                </th>
                                <?php 
                                    $headerArr[] = "PROJECTS/TASKS";
                                    foreach ($users as $key => $value) {
                                        echo "<th>".$value['username']."</th>";
                                        $headerArr[] = $value['username'];
                                    }
                                ?>
                                <th>Grand Total</th>
                            </tr>

                            <?php 
                                $headerArr[] = "Grand Total";
                                $overAlldata[] = $headerArr;
                                foreach ($myFinal as $key => $value) {
                                    $newVar = array();
                                    echo "<tr class='fisrt_part'>";
                                        echo "<td>".$key."</td>";
                                        $newVar[] = $key;
                                        foreach ($users as $uKey => $uValue) {
                                            
                                            if(array_key_exists($uValue['username'], $value)){
                                                $r = format_seconds(@$value[$uValue['username']]);
                                                $newVar[] = $r;
                                            }else{
                                                $r = "";
                                                $newVar[] = '--';
                                            }
                                            echo '<td>'.$r.'</td>';
                                            $rowTotal[$uValue['username']][] =  @$value[$uValue['username']];
                                        }
                                        if ( !$authenticationManager->hasClearance(MANAGER) ) {
                                            $myTotal = 0;
                                            foreach ($value as $taskKey => $taskValue) {
                                                $myTotal += @$taskValue[$contextUser];
                                            }
                                            echo '<td><b>'.format_seconds($myTotal).'</b></td>'; 
                                            $newVar[] = format_seconds($myTotal);   
                                        }else{

                                            echo '<td><b>'.format_seconds($gTotal[$key]).'</b></td>';
                                            $newVar[] = format_seconds($gTotal[$key]);
                                        }

                                    echo "</tr>";
                                    $overAlldata[] = $newVar;
                                    foreach ($value as $taskKey => $taskValue) {
                                        $newVar = array();
                                        echo "<tr>";
                                            echo "<td align='center'>";
                                                echo $taskKey;
                                            echo "</td>";
                                            $newVar[] = $taskKey;
                                            $colTotal = 0;  
                                            foreach ($users as $uKey => $uValue) {
                                               
                                                if(array_key_exists($uValue['username'], $taskValue)){
                                                    $colTotal += @$taskValue[$uValue['username']];
                                                    $rowTotal[$uValue['username']][] =  @$taskValue[$uValue['username']];
                                                    $taim = format_seconds(@$taskValue[$uValue['username']]);
                                                    $newVar[] = $taim;
                                                }else{
                                                    $taim = "";
                                                    $newVar[] = "--";
                                                }

                                                echo "<td align='center'>";
                                                    echo $taim;
                                                echo "</td>";
                                            }
                                            echo '<td align=\'center\'>'.format_seconds($colTotal).'</td>';
                                            $newVar[] = format_seconds($colTotal);

                                        echo "</tr>";

                                        $overAlldata[] = $newVar;
                                    }
                                    echo "<tr>
                                            <td> </td>
                                        </tr>";
                                }

                            ?>
                            <tr class="grand_total">
                                <td> <strong>Grand Total </strong> </td>
                                <?php 
                                    $gTotal = 0;
                                    $footerArr[] = "Grand Total";
                                    foreach ($users as $uKey => $uValue) {
                                        $rowT = 0;
                                        foreach ($rowTotal[$uValue['username']] as $rKey => $rValue) {
                                            $rowT += $rValue; 
                                            $gTotal += $rValue;
                                        }
                                        echo '<td><strong>'.format_seconds($rowT).'</strong></td>';
                                        $footerArr[] = format_seconds($rowT);
                                    } 
                                    echo '<td><strong>'.format_seconds($finalTotal).'</strong></td>';
                                    $footerArr[] = format_seconds($finalTotal);

                                    $overAlldata[] = $footerArr; 

                                    $filename = "csv/timesheet-full_report.csv";
                                    $fp = fopen($filename, 'w');
                                    foreach ($overAlldata as $key => $value) {
                                      fputcsv($fp, $value);
                                    }
                                    fclose($fp);
                                ?>
                            </tr>
                        </table>
                        <?php
                        }else{
                            print " <table class=\"table no-records\">\n";
                            print " <tr>\n";
                            print "     <td align=\"center\">\n";
                            print "         <i><br>No hours recorded.<br><br></i>\n";
                            print "     </td>\n";
                            print " </tr>\n";
                            print " </table>\n";
                        } ?>
                </div>
            </div>
        </div>      
    <? include ("footer.inc"); ?>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    </body>
</html>