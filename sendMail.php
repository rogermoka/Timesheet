<?php 
	require("class.AuthenticationManager.php");
	require("class.CommandMenu.php");
	require("table_names.inc");
	@session_start();
	
	if ( isset($_POST['data']) ) {

		$content  = $_POST['data'];
		$dbData   = $_POST['mail'];

		$username = $_SESSION['loggedInUser'];

		list($qh, $num) = dbQuery("SELECT first_name, last_name, email_address, phone, bill_rate FROM $USER_TABLE WHERE username='$username'");			
		$existingUserDetails = dbResult($qh);

		$name = $existingUserDetails['first_name']." ".$existingUserDetails['last_name'];
		$email = $existingUserDetails['email_address'];

		//Get the result set for the config set 1
		list($qhq, $numq) = dbQuery("select pmMail from $CONFIG_TABLE where config_set_id = '1'");
		$configdata = dbResult($qhq);
		$to = $configdata["pmMail"];
		$subject = "Weekly Timesheet - ".$name;

		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		//$headers .= "From: $email"; 
		
		$message = "<table width='100%' height='100%' border='0' cellpadding='0' cellspacing='0'>";
		$message .= $content;
		$message .= "</table>";
		$message .= "<p style='font-size:11px; color:#433B94; margin-top:5px;'>Report was sent by ".$name.".</p>";

		if( mail($to,$subject,$message,$headers) ){
			echo "Success : Email sent.";
			dbquery("INSERT INTO $EMAIL_TABLE (timesheet_id, user, pm ) VALUES ('$dbData','$username','$to')");
		}else{
			echo "Error : Email not sent.";
		}
	}
?>