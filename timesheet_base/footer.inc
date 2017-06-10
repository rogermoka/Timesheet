<?
	//Get the result set for the config set 1
  list($qhq, $numq) = dbQuery("select footerhtml from $CONFIG_TABLE where config_set_id = '1'");
  $configData = dbResult($qhq);
	parse_and_echo(stripslashes($configData["footerhtml"]));
?>
<p>
<table width="100%" class="grey_area">
	<tr>
		<td class="timesheet_logo">
			TimeSheet
		</td>
		<td align="left">
			&copy; 2017 TimeSheet
		</td>
		<td align="right">
			INTERNAL USE ONLY
		</td>
	</tr>
</table>
