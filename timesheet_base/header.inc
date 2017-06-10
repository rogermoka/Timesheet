<?
require("table_names.inc");

//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("select headerhtml from $CONFIG_TABLE where config_set_id = '1'");
$configdata = dbResult($qhq);
parse_and_echo(stripslashes($configdata["headerhtml"]));
?>
