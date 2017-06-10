<?php 
	require_once("class.AuthenticationManager.php");
	require_once("class.CommandMenu.php");
	require_once("table_names.inc");
	require_once("class.Pair.php");
	@session_start();

	$todayDate = mktime(0, 0, 0,date('m'), date('j'), date('Y'));
	$dateValues = getdate($todayDate);
	
	$currentDate = date('Y-m-d');
	$todayDayOfWeek = $dateValues["wday"];
	$checkDate = date('j-m-Y', strtotime('-2 days'));
	$link = "http://172.23.25.174/timesheet/simple.php?client_id=0&proj_id=0&task_id=0&month=".$dateValues["mon"]."&year=".$dateValues["year"]."&day=".($dateValues["mday"]-2);


	if ($todayDayOfWeek == 1) {
		
		list($checkNoti, $numq) = dbQuery("SELECT * from $NOTIF_TABLE WHERE mailTime = '$currentDate' AND type='weekly_notify'");
		$notiResults = dbResult($checkNoti);

		if ( empty($notiResults) ) {
			$whereVal = '.*;s:[0-9]+:"'.$checkDate.'"*';
			$condition = "WHERE timesheet_id REGEXP '".$whereVal."'";
			list($mailList, $numq) = dbQuery("select * from $EMAIL_TABLE $condition");
			while ($results = dbResult($mailList) ) {
				$mailSent[] = $results['user'];
			}

			list($userList, $numq) = dbQuery("select * from $USER_TABLE WHERE level < ".MANAGER);
			while ($userRes = dbResult($userList) ) {
				if ( !in_array($userRes['username'], $mailSent) ) {
					$finalArr[] = $userRes;
					$dbData[] =   $userRes["username"];
				}
			}

			foreach ($finalArr as $key => $value) {

				$to = $value["email_address"];
				$subject = "Notification weekly timesheet";
				$name = $value["first_name"]." ".$value["last_name"];
				

				// Always set content-type when sending HTML email
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$message = "Hello ".$name."<br>";
				$message .= "You haven't declared your hours yet, please access <a href='".$link."'>timesheet</a> to declare it and send to PM approvation.\r\n";

				mail($to,$subject,$message,$headers);

			}
			dbquery("INSERT INTO $NOTIF_TABLE (mailTime, users, status ) VALUES ('$currentDate','".serialize($dbData)."','1')");
		}
	}

?>