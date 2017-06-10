<?
  //Get the result set for the config set 1
  list($qhq, $numq) = dbQuery("select errorhtml from $CONFIG_TABLE where config_set_id = '1'");
  $configData = dbResult($qhq);
	parse_and_echo(stripslashes($configData["errorhtml"]));
?>
