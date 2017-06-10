<?
// $Header: /cvsroot/tsheet/timesheet.php/config.php,v 1.8 2005/02/03 08:06:10 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//define the command menu
include("timesheet_menu.inc");

//Get the result set for the config set 1
list($qh, $num) = dbQuery("select locale, timezone, timeformat, headerhtml, bodyhtml, footerhtml, " .
		"errorhtml, bannerhtml, tablehtml, useLDAP, LDAPScheme, LDAPHost, LDAPPort, " .
		"LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, ".
		"LDAPBindUsername, LDAPBindPassword, weekstartday " .
		"from $CONFIG_TABLE where config_set_id = '1'");
$resultset = dbResult($qh);

?>
<html>
<head>
<title>Timesheet.php Configuration Parameters</title>
<?
include ("header.inc");
?>
</head>
<script language="Javascript">

//store the current LDAP entry method in this variable
var currentLDAPEntryMethod = 'normal';

function onChangeLDAPEntryMethod() {
	if (document.configurationForm.LDAPEntryMethod.value == 'normal') {
		document.getElementById('normalLDAPEntry').style.display='block';
		document.getElementById('advancedLDAPEntry').style.display='none';		
	}
	else {
		document.getElementById('normalLDAPEntry').style.display='none';
		document.getElementById('advancedLDAPEntry').style.display='block';		
	}
	
	//copy data from one to the other when it changes
	if (currentLDAPEntryMethod == 'normal' && document.configurationForm.LDAPEntryMethod.value != 'normal')
		buildLDAPUrlFromForm();
	else if (currentLDAPEntryMethod != 'normal' && document.configurationForm.LDAPEntryMethod.value == 'normal')
		fillOutLDAPFieldsFromUrl();
	
	//update the current LDAP entry method variable
	currentLDAPEntryMethod = document.configurationForm.LDAPEntryMethod.value;
}

function enableLDAP(value) {
	document.getElementById('LDAPEntryMethod').disabled = !value;
	document.getElementById('LDAPScheme').disabled = !value;
	document.getElementById('LDAPHost').disabled = !value;
	document.getElementById('LDAPPort').disabled = !value;
	document.getElementById('LDAPBaseDN').disabled = !value;
	document.getElementById('LDAPUsernameAttribute').disabled = !value;
	document.getElementById('LDAPSearchScope').disabled = !value;
	document.getElementById('LDAPFilter').disabled = !value;
	document.getElementById('LDAPUrl').disabled = !value;
	document.getElementById('LDAPProtocolVersion').disabled = !value;
	document.getElementById('LDAPBindUsername').disabled = !value;
	document.getElementById('LDAPBindPassword').disabled = !value;
}

function buildLDAPUrlFromDb() {
	//get values from database
	var scheme = '<? echo $resultset['LDAPScheme']; ?>';
	var host = '<? echo $resultset['LDAPHost']; ?>';
	var port = '<? echo $resultset['LDAPPort']; ?>';
	var baseDN = '<? echo $resultset['LDAPBaseDN']; ?>';
	var usernameAttribute = '<? echo $resultset['LDAPUsernameAttribute']; ?>';
	var searchScope = '<? echo $resultset['LDAPSearchScope']; ?>';
	var filter = '<? echo $resultset['LDAPFilter']; ?>';
	
	buildLDAPUrl(scheme, host, port, baseDN, usernameAttribute, searchScope, filter);		
}

function buildLDAPUrlFromForm() {
	buildLDAPUrl(
		document.getElementById('LDAPScheme').value,
		document.getElementById('LDAPHost').value,
		document.getElementById('LDAPPort').value,
		document.getElementById('LDAPBaseDN').value,
		document.getElementById('LDAPUsernameAttribute').value,
		document.getElementById('LDAPSearchScope').value,
		document.getElementById('LDAPFilter').value);
}

function buildLDAPUrl(scheme, host, port, baseDN, usernameAttribute, searchScope, filter) {
	//fill out defaults for those which are empty
	if (scheme == '')
		scheme = 'ldaps';
	if (host == '')
		host = 'localhost';
	if (port == '')
		port = 389;
	if (baseDN == '')
		baseDN = 'dc=yourOrganisation, dc=com, ou=yourOrganisationalUnit';
	if (usernameAttribute == '')
		usernameAttribute = 'uid';
	if (searchScope == '')
		searchScope = 'base';

	//combine into one string
	var url = scheme + '://' + host + ':' + port + '/' + baseDN + '?' + usernameAttribute + '?' 
		+ searchScope;
	
	if (filter != '')
		url += '?' + filter;
	
	//set in the form
	document.getElementById('LDAPUrl').value = url;
}

function fillOutLDAPFieldsFromUrl() {

	//get the url from the form
	var url = document.getElementById('LDAPUrl').value;

	if (url.indexOf('ldaps') == 0)
		document.getElementById('LDAPScheme').selectedIndex = 1;
	else
		document.getElementById('LDAPScheme').selectedIndex = 0;
	
	//find the host
	var pos1 = url.indexOf('://') + 2;
	if (pos1 == -1)
		return false;
	var pos2 = url.indexOf(':', pos1+1);
	if (pos2 == -1)
		return;
	document.getElementById('LDAPHost').value = url.substring(pos1+1, pos2);
	
	//find the port
	var pos3 = url.indexOf('/', pos2+1);
	if (pos3 == -1)
		return false;
	document.getElementById('LDAPPort').value = url.substring(pos2+1, pos3);
	
	//find the base dn
	var pos4 = url.indexOf('?', pos3+1);
	if (pos4 == -1)
		return false;
	document.getElementById('LDAPBaseDN').value = url.substring(pos3+1, pos4);
	
	//find the username attribute
	var pos5 = url.indexOf('?', pos4+1);
	if (pos5 == -1)
		return false;
	document.getElementById('LDAPUsernameAttribute').value = url.substring(pos4+1, pos5);
	
	//find the search scope	
	var pos6 = url.indexOf('?', pos5+1);
	if (pos6 == -1)
		pos6 = url.length;
	var searchScope = url.substring(pos5+1, pos6);
	if (searchScope == 'one')
		document.getElementById('LDAPSearchScope').selectedIndex = 1;
	else if (searchScope == 'sub')
		document.getElementById('LDAPSearchScope').selectedIndex = 2;
	else
		document.getElementById('LDAPSearchScope').selectedIndex = 0;
	if (pos6 == -1)
		return true;		
	
	//the filter
	document.getElementById('LDAPFilter').value = url.substring(pos6+1, url.length);
	return true;
}

function onSubmit() {
	if (document.configurationForm.LDAPEntryMethod.value != 'normal') {
		if (!fillOutLDAPFieldsFromUrl()) {
			alert('There was an error parsing the LDAP Url. Please correct it and try again.');
			return;
		}
	}

	if (document.getElementById('useLDAPCheck').checked)
		document.getElementById('useLDAP').value = 1;
	else
		document.getElementById('useLDAP').value = 0;
	
	//re-enable the fields just before submitting because otherwise they are not send in mozilla
	enableLDAP(true);
	
	//submit the form
	document.configurationForm.submit();
}

</script>
<body <? include ("body.inc"); ?> onload="enableLDAP(<? echo $resultset["useLDAP"]?>);">
<?
include ("banner.inc");
?>
<form action="config_action.php" name="configurationForm" method="post">
<input type="hidden" name="action" value="edit">							

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
		
<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Configuration Parameters:
						</td>
					</tr>
					<tr>
						<td>
						This form allows you to change the basic operating parameters of timesheet.php.
						Please be careful here, as errors may cause pages not to display properly.
						Somewhere in one of these, you should include the placeholder %commandMenu%.
						This is where timesheet.php will place the menu options.
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>			
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">					
					<tr>
						<td>
						
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					


			<!-- LDAP configurationForm -->
			<tr>
				<td align="left" valign="top">
					<b>LDAP</b>:
				</td>
				<td align="left" width="100%">
					<input type="checkbox" name="useLDAPCheck" id="useLDAPCheck" onclick="enableLDAP(this.checked);" <? if ($resultset['useLDAP'] == 1) echo "checked"; ?>>Use LDAP for authentication</input>
					<input type="hidden" name="useLDAP" id="useLDAP"></input>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">&nbsp;</td>
				<td align="left" width="100%">
					<fieldset>
						<legend>Connection Details</legend>
						<table width="100%">								
							<tr>
								<td>
									<b>&nbsp;Data entry style:</b>
									<select id="LDAPEntryMethod" name="LDAPEntryMethod" onChange="onChangeLDAPEntryMethod();">
										<option value="normal" selected>Normal</option>
										<option value="advanced">RFC 2255 URL</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<div id="normalLDAPEntry">
										<table width="100%" cellpadding="2">
											<tr>
												<td colspan="3">
													<span class="label">Scheme:</span>
													<select id="LDAPScheme" name="LDAPScheme">
														<option value="ldap" <? if ($resultset["LDAPScheme"] == "ldap") print "selected";?>>LDAP</option>
														<option value="ldaps" <? if ($resultset["LDAPScheme"] == "ldaps") print "selected";?>>LDAPS</option>
													</select>
													(LDAP=Non SSL, LDAPS=Use SSL)
												</td>
											</tr>
											<tr>
												<td width="50%">
													<span class="label">Host:</span>
													<input id="LDAPHost" name="LDAPHost" type="text" value="<? echo $resultset['LDAPHost']; ?>" style="width:100%;"></input>
												</td>
												<td width="20">&nbsp;</td>												
												<td width="50%">
													<span class="label">Port:</span>
													<input id="LDAPPort" name="LDAPPort" type="text" size="10" maxlength="10" value="<? echo $resultset['LDAPPort']; ?>"></input>
												</td>
											</tr>		
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>LDAP search base (Distinguished Name):</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<input id="LDAPBaseDN" type="text" name="LDAPBaseDN" value="<? echo $resultset["LDAPBaseDN"]; ?>" style="width:100%;"></input>
															</td>
														</tr>
													</table>
												</td>											
											</tr>
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>Username attribute to query:</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<input id="LDAPUsernameAttribute" name="LDAPUsernameAttribute" type="text" value="<? echo $resultset["LDAPUsernameAttribute"]; ?>" size="30"></input>				
															</td>
														</tr>
													</table>
												</td>											
											</tr>
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>Search scope:</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<select id="LDAPSearchScope" name="LDAPSearchScope">
																	<option value="base" <? if ($resultset["LDAPSearchScope"] == "base") print "selected"; ?>>Base DN search only</option>
																	<option value="one" <? if ($resultset["LDAPSearchScope"] == "one") print "selected"; ?>>One level search</option>
																	<option value="sub" <? if ($resultset["LDAPSearchScope"] == "sub") print "selected"; ?>>Full sub-tree search</option>
																</select>													
															</td>
														</tr>
													</table>
												</td>											
											</tr>
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>Filter:</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<input id="LDAPFilter" type="text" name="LDAPFilter" value="<? echo $resultset["LDAPFilter"]; ?>" style="width:100%;"></input>
															</td>
														</tr>
													</table>
												</td>											
											</tr>																																
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>Protocol Version:</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<select id="LDAPProtocolVersion" name="LDAPProtocolVersion">
																	<option value="3" <? if ($resultset["LDAPProtocolVersion"] == "3") print "selected"; ?>>3</option>
																	<option value="2" <? if ($resultset["LDAPProtocolVersion"] == "2") print "selected"; ?>>2</option>
																	<option value="1" <? if ($resultset["LDAPProtocolVersion"] == "1") print "selected"; ?>>1</option>
																</select>													
															</td>
														</tr>
													</table>
												</td>											
											</tr>											
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label_grey" nowrap><i>The following fields are normally only required for Microsoft's Active Directory LDAP Server:</i></span>
															</td>
														</tr>
													</table>
												</td>											
											</tr>
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0" border="0">
														<tr>
															<td width="50%">																
																<table width="100%" cellpadding="0" cellspacing="0" border="0">
																	<tr>
																		<td nowrap>
																			<span class="label" nowrap>Bind Username:</span>
																		</td>
																		<td width="5">&nbsp;</td>
																		<td width="100%">
																			<input id="LDAPBindUsername" type="text" name="LDAPBindUsername" value="<? echo $resultset["LDAPBindUsername"]; ?>" style="width:100%;"></input>
																		</td>
																	</tr>
																</table>
															</td>
															<td>&nbsp;&nbsp;&nbsp;</td>
															<td width="50%">																
																<table width="100%" cellpadding="0" cellspacing="0" border="0">
																	<tr>
																		<td nowrap>
																			<span class="label" nowrap>Bind Password:</span>
																		</td>
																		<td width="5">&nbsp;</td>
																		<td width="100%">
																			<input id="LDAPBindPassword" type="password" name="LDAPBindPassword" value="<? echo $resultset["LDAPBindPassword"]; ?>" style="width:100%;"></input>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>											
											</tr>
																						
										</table>
									</div>
									<div id="advancedLDAPEntry" style="display:none;">
										<table width="100%" cellpadding="0">
											<tr>
												<td colspan="3">
													<table width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td nowrap>
																<span class="label" nowrap>RFC 2255 URL:</span>
															</td>
															<td>&nbsp;</td>
															<td width="100%">
																<input id="LDAPUrl" name="LDAPUrl" type="text" value="" style="width:100%;"></input>
															</td>
														</tr>
													</table>
												</td>											
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			
				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">		
		
		<!-- locale -->
			<tr>
				<td align="left" valign="top">
					<b>locale</b>:
				</td>
				<td align="left" width="100%">
					The locale in which you want timesheet.php to work. This affects regional settings. Leave it blank if you want to use the system locale. An example locale is <code>en_AU</code>, for Australia.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="localeReset" value="off" valign="absmiddle" onclick="document.configurationForm.locale.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<input type="text" name="locale" size="75" maxlength="254" value="<? echo htmlentities(trim(stripslashes($resultset["locale"]))); ?>" style="width: 100%;">
				</td>
			</tr>
			
				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					
			
		<!-- timezone -->
			<tr>
				<td align="left" valign="top">
					<b>Time Zone</b>:
				</td>
				<td align="left" width="100%">
					The timezone to use when generating dates. Leave it blank to use the system timezone. An example timezone is <code>Australia/Melbourne</code>.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="timezoneReset" value="off" onclick="document.configurationForm.timezone.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<input type="text" name="timezone" size="75" maxlength="254" value="<? echo htmlentities(trim(stripslashes($resultset["timezone"]))); ?>" style="width: 100%;">
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">	
			
			<!-- timeformat -->
			<tr>
				<td align="left" valign="top">
					<b>Time Format</b>:
				</td>
				<td align="left" width="100%">
					The format in which times should be displayed.	For example:<br>
					&nbsp;&nbsp;&nbsp;&nbsp;<i> 12 hour format:</i><code>&nbsp;5:35 pm</code>
					&nbsp;&nbsp;&nbsp;&nbsp;<i> 24 hour format:</i><code>&nbsp;17:35</code>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
				 <input type="checkbox" name="timeformatReset" value="off" onclick="document.configurationForm.timeformat.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<select name="timeformat" style="width: 100%;">
						<? if ($resultset["timeformat"] == "12") { ?>
							<option value="12" selected>12 hour format</option>
							<option value="24">24 hour format</option>
						<? } else { ?>
							<option value="12">12 hour format</option>
							<option value="24" selected>24 hour format</option>
						<? } ?>
					</select>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- weekstartday -->
			<tr>
				<td align="left" valign="top">
					<b>Week Start Day</b>:
				</td>
				<td align="left" width="100%">
					The starting day of the week. Some people prefer to calculate the week starting 
					from Monday rather than Sunday.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
				 <input type="checkbox" name="weekStartDayReset" value="off" onclick="document.configurationForm.weekstartday.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<select name="weekstartday" style="width: 100%;">					
						<? 
								//get the current time
								$dowDate = time();
								
								//make it sunday
								$dowDate -= (24*60*60) * date("w", $dowDate);
						
								//for each day of the week
								for ($i=0; $i<7; $i++) {
									$dowString = strftime("%A", $dowDate);
									print "<option value=\"$i\"";
									if ($resultset["weekstartday"] == $i)
										print " selected";									
									print ">$dowString</option>";
									//increment the day
									$dowDate += (24*60*60); 								
								}
						?>
					</select>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">			
			
			<!-- headerhtml -->
			<tr>
				<td align="left" valign="top">
					<b>headerhtml</b>:
				</td>
				<td align="left" width="100%">
					Additional HTML to add to the HEAD area of documents, eg. links to stylesheets.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="headerReset" value="off" onclick="document.configurationForm.headerhtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="headerhtml" style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["headerhtml"]))); ?>	</textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					
			
			<!-- bodyhtml -->
			<tr>
				<td align="left" valign="top">
					<b>bodyhtml</b>:
				</td>
				<td align="left" width="100%">
					Additional parameters to add to the BODY tag at the beginning of documents, eg. background image/colors, link colors, etc
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="bodyReset" value="off" onclick="document.configurationForm.bodyhtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="bodyhtml"  style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["bodyhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					

			<!-- bannerhtml -->
			<tr>
				<td align="left" valign="top">
					<b>bannerhtml</b>:
				</td>
				<td align="left" width="100%">
					The html that gets emitted at the head of every page. This is a good place to insert the placeholder %commandMenu%. You may also want to include the placeholder %username% as part of a welcome message.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="bannerReset" value="off" onclick="document.configurationForm.bannerhtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="bannerhtml" style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["bannerhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					
			
			<!-- footerhtml -->
			<tr>
				<td align="left" valign="top">
					<b>footerhtml</b>:
				</td>
				<td align="left" width="100%">
					HTML to add to the bottom of every page. If you include %time%, %date%, and %timezone% here, it will print the time and date the page was loaded.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="footerReset" value="off" onclick="document.configurationForm.footerhtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="footerhtml" style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["footerhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">	
			
			<!-- errorhtml -->
			<tr>
				<td align="left" valign="top">
					<b>errorhtml</b>:
				</td>
				<td align="left" width="100%">
					This is what is printed out when a form is improperly filled out. %errormsg% is replaced by the actual error itself.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="errorReset" value="off" onclick="document.configurationForm.errorhtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="errorhtml" style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["errorhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">					
			
			
			<!-- tablehtml -->
			<tr>
				<td align="left" valign="top">
					<b>tablehtml</b>:
				</td>
				<td align="left" width="100%">
					Additional parameters to add to the TABLE tag when displaying sheets, calenders, etc. This is often used to set the background color or background image of the table.
				</td>
			</tr>
			<tr>
				<td align="left" class="label" nowrap width="90">
					<input type="checkbox" name="tableReset" value="off" onclick="document.configurationForm.tablehtml.disabled=(this.checked);">Reset</input>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="tablehtml" style="width: 100%;"><? echo htmlentities(trim(stripslashes($resultset["tablehtml"]))); ?></textarea>
				</td>
			</tr>

						</table>
					</td>
				</tr>	
						
				</table>				
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_bottom_panel">					

			<!-- form submission -->
			<tr>
				<td colspan="2" align="center">
					<table width="100%">
						<tr>
							<td align="center">
								<input type="button" value="Submit Changes" name="submitButton" id="submitButton" onClick="onSubmit();"></input>
							</td>
					</table>
				</td>
			</tr>					
		</table>
	
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>
		
</form>
		
<?
include ("footer.inc");
?>
</BODY>
</HTML>
