<?{

	//Get the result set for the config set 1
	$dbh = dbConnect();
  list($qhq, $numq) = dbQuery("SELECT locale, timezone FROM $CONFIG_TABLE WHERE config_set_id = '1'");
  $configdata = dbResult($qhq);

	//set the locale
	if ($configdata["locale"] != "")
		setlocale(LC_ALL, stripslashes($configdata["locale"]));

	//set the timezone
	if ($configdata["timezone"] != "") {
		putenv("PHP_TZ=" . stripslashes($configdata["timezone"]));
		putenv("TZ=" . stripslashes($configdata["timezone"]));
	}

}?>
