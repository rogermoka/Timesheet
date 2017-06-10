<?
require("table_names.inc");
include('weekly_report_notify.php');
include('weekly_report_send.php');
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("select headerhtml from $CONFIG_TABLE where config_set_id = '1'");
$configdata = dbResult($qhq);
parse_and_echo(stripslashes($configdata["headerhtml"]));
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">
	$(function () {
		$(".newSelect").select2();
	});
</script>