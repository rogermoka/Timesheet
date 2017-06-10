<?php 
// $Header: /cvsroot/tsheet/timesheet.php/admin_report_specific_user.php,v 1.11 2005/05/23 10:42:46 vexil Exp $

// Authenticate
require_once("class.AuthenticationManager.php");
require_once("class.CommandMenu.php");

// Connect to database.
$dbh = dbConnect();
$users = getAllUsers();
//load local vars from superglobals
include_once("timesheet_menu.inc");
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
		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
															// be a space after the . operator for this to work.
	return "$hour:$minutes:$sec";
}

$todayDate = mktime(0, 0, 0,date('m'), date('j'), date('Y'));
$dateValues = getdate($todayDate);
$currentDate = date('Y-m-d');
$todayDayOfWeek = $dateValues["wday"];
$last_proj_id = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;

	if ($todayDayOfWeek == 6) {
		list($checkNoti, $numq) = dbQuery("SELECT * from $NOTIF_TABLE WHERE mailTime = '$currentDate' AND type='weekly_send'");
		$notiResults = dbResult($checkNoti);
		if ( empty($notiResults) ) {

			//define working variables  
			$overAlldata[] = array('Name','Username','Project','Task','Date','Logs','Duration' );

				$uid = 0; 
				$num = 0;
				foreach ($users as $key => $value) {
					$uid = $value['username'];
					$name = $value['first_name']." ".$value['last_name'];
					// Change the date-format for internationalization...
					$query = "SELECT $TIMES_TABLE.proj_id, ".
								"$TIMES_TABLE.task_id, ".
								"$TIMES_TABLE.log_message, " .
								"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
								"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, ".
								"$PROJECT_TABLE.title, ".
								"$TASK_TABLE.name, ".
								"date_format(start_time, '%Y/%m/%d') as start_date, ".
								"trans_num ".
							"FROM $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE ".
							"WHERE $TIMES_TABLE.uid=$USER_TABLE.username AND ".
								"end_time > 0 AND ".
								"$TIMES_TABLE.uid='$uid' ".
								"AND start_time >= '$year-$month-$day' AND ".
								"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND ".
								"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
								"end_time < '".date('Y-m-d',$next_week)."' ".
							"ORDER BY proj_id, task_id, start_time";							
					//run the query  
					list($qh,$numOld) = dbQuery($query);

				if ($numOld == 0) {
					$num++;
					$singleData = array();
					$singleData[] = $name;
					$singleData[] = $uid;
					$singleData[] = "---";
					$singleData[] = "---";
					$singleData[] = "---";
					$singleData[] = "---";

				}
				else {
					$singleData = array();
					$tempName = "";
					$b = array();
					while ($data = dbResult($qh)) {
						$num++;
						$newVar = array();
						// New project, so print out last project total time and start a new table cell.
						if ($tempName != $uid) {
							$tempName = $uid;
					    	
					    	$newVar[] = $name;  
					    	$newVar[] = $uid;
						}else{
								$newVar[] = "---";
								$newVar[] = "---";
						}

						$newVar[] = stripslashes($data["title"]);      
						$newVar[] = stripslashes($data["name"]);

						$newVar[] = $data['start_date'];

						if ($data['log_message']){
							$newVar[] = stripslashes($data['log_message']);
						}
						else {
							$newVar[] = "---";
						}
						$newVar[] = $data['diff_time'];
						
						$total_time += $data["diff"];
						$grand_total_time += $data["diff"];
						$b[] = $newVar;
					}
					foreach ($b as $key => $value) {
						$overAlldata[] = $value;
					}
					$formatted_time = format_seconds($grand_total_time);
				}

				if ( !empty($singleData) ) {
					$overAlldata[] = $singleData; 
				}
			}
			/* include the timesheet face up until the end */

			//Get the result set for the config set 1
			list($qhq, $numq) = dbQuery("select autoMail from $CONFIG_TABLE where config_set_id = '1'");
			$configdata = dbResult($qhq);
			
			$mailto = $configdata["autoMail"];
			$subject = "Weekly Timesheet All Users";

			$filename = "csv/timesheet.csv";
			$fp = fopen($filename, 'w');
			foreach ($overAlldata as $key => $value) {
			  fputcsv($fp, $value);
			}
			fclose($fp);

			$file = __DIR__."/".$filename;

		    $content = file_get_contents($file);
		    $content = chunk_split(base64_encode($content));

		    $message = 'Automatic mail from Itron.';

		    // a random hash will be necessary to send mixed content
		    $separator = md5(time());

		    // carriage return type (RFC)
		    $eol = "\r\n";

		    // main header (multipart mandatory)
		    $headers = "From: Admin <test@test.com>" . $eol;
		    $headers .= "MIME-Version: 1.0" . $eol;
		    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
		    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
		    $headers .= "This is a MIME encoded message." . $eol;

		    // message
		    $body = "--" . $separator . $eol;
		    $body .= "Content-Type: text/html; charset=\"UTF-8\"" . $eol;
		    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
		    $body .= $message . $eol;

		    // attachment
		    $body .= "--" . $separator . $eol;
		    $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
		    $body .= "Content-Transfer-Encoding: base64" . $eol;
		    $body .= "Content-Disposition: attachment" . $eol;
		    $body .= $content . $eol;
		    $body .= "--" . $separator . "--";

		    //SEND Mail
			if( mail($mailto,$subject,$body,$headers) ){
				echo "inserted";
				dbquery("INSERT INTO $NOTIF_TABLE (mailTime, users, type ) VALUES ('$currentDate','".$mailto."','weekly_send')");
			}else{
				echo "Error : Email not sent.";

			}

		}
	}
?>