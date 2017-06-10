<?php

	//load local vars from superglobals
	$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: (int)date("Y");
	$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: (int)date("m");
	$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: (int)date("j");
	$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
	$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
	$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;

	//get todays values
	$today = time();
	$todayYear = date("Y", $today);
	$todayMonth = date("n", $today);
	$todayDay = date("j", $today);

	//default values to today if not set
	if (empty($_REQUEST['year']))
		$year = $todayYear;
	if (empty($_REQUEST['month']))
		$month = $todayMonth;
	if (empty($_REQUEST['day']))
		$day = $todayDay;

	// View mode (monthly, weekly, all)
	if (isset($_REQUEST['mode']))
		$mode = $_REQUEST['mode'];
	else
		$mode = "all";
	if (!($mode == "all" || $mode == "monthly" || $mode == "weekly")) 
		$mode = "all";


	//define the command menu
	$commandMenu->add(new IconTextCommand("Home", true, "simple.php", "images/icon_clients.gif"));
	$commandMenu->add(new IconTextCommand("Dashboard", true, "timesheet-dashboard.php", "images/icon_clients.gif"));
	$commandMenu->add(new IconTextCommand("Full Report", true, "timesheet-full_report.php", "images/icon_clients.gif"));
	$commandMenu->add(new IconTextCommand("Projects Report", true, "timesheet-project_report.php", "images/icon_users.gif"));
	$commandMenu->add(new IconTextCommand("Tasks Report", true, "timesheet-task_report.php", "images/icon_projects.gif"));
	$commandMenu->add(new IconTextCommand("Logout", true, "logout.php?logout=true", "images/icon_logout.gif"));

	//disable yourself
	$commandMenu->disableSelf();

?>