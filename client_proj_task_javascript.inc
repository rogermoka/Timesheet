<script language="Javascript">
	//define the hash table
	var projectTasksHash = {};	
	var clientProjectsHash = {};
<?php
//get all of the clients and put them into a hastable
$getClientsQuery = "SELECT $CLIENT_TABLE.client_id, $CLIENT_TABLE.organisation from " .
													"$CLIENT_TABLE ORDER BY $CLIENT_TABLE.organisation";

list($qh2, $num2) = dbQuery($getClientsQuery);
//iterate through results
for ($i=0; $i<$num2; $i++) {
	//get the current record
	$data = dbResult($qh2, $i);
	print("clientProjectsHash['" . $data["client_id"] . "'] = {};\n");
	print("clientProjectsHash['" . $data["client_id"] . "']['name'] = '". addslashes($data["organisation"]) . "';\n");
	print("clientProjectsHash['" . $data["client_id"] . "']['projects'] = new Array();\n");	
}								

//get all of the projects and put them into the hashtable
$getProjectsQuery = "SELECT $PROJECT_TABLE.proj_id, $PROJECT_TABLE.client_id, $PROJECT_TABLE.title, ".
													"$PROJECT_TABLE.proj_status FROM ".
													"$PROJECT_TABLE, $ASSIGNMENTS_TABLE where ".
													"$PROJECT_TABLE.proj_id=$ASSIGNMENTS_TABLE.proj_id AND ".
													"$ASSIGNMENTS_TABLE.username='$contextUser' AND ".
													"$PROJECT_TABLE.proj_status!='Suspended' AND $PROJECT_TABLE.proj_status!='Complete' ".
													"ORDER BY $PROJECT_TABLE.title";
													 
list($qh3, $num3) = dbQuery($getProjectsQuery);
//iterate through results
for ($i=0; $i<$num3; $i++) {
	//get the current record
	$data = dbResult($qh3, $i);
	
	//add the project id to the array for this particular client
	print("var numProjects = clientProjectsHash['" . $data["client_id"] . "']['projects'].length;\n");
	print("clientProjectsHash['" . $data["client_id"] . "']['projects'][numProjects] = " . $data["proj_id"] . ";\n");	
	
	//add the project properties to the projectTasksHash
	print("projectTasksHash['" . $data["proj_id"] . "'] = {};\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['name'] = '". addslashes($data["title"]) . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['tasks'] = {};\n");	
}

//get all of the tasks and put them into the hashtable
$getTasksQuery = "SELECT $TASK_TABLE.proj_id, $TASK_TABLE.task_id, $TASK_TABLE.name from ".
											"$TASK_TABLE, $TASK_ASSIGNMENTS_TABLE where ".
											"$TASK_TABLE.task_id = $TASK_ASSIGNMENTS_TABLE.task_id AND ".
											"$TASK_ASSIGNMENTS_TABLE.username='$contextUser' ".
											"ORDER BY $TASK_TABLE.name";

list($qh4, $num4) = dbQuery($getTasksQuery);
//iterate through results
for ($i=0; $i<$num4; $i++) {
	//get the current record
	$data = dbResult($qh4, $i);
	print("if (projectTasksHash['" . $data["proj_id"] . "'] != null)\n");
	print("  projectTasksHash['" . $data["proj_id"] . "']['tasks']['" . $data["task_id"] . "'] = '" . addslashes($data["name"]) . "';\n");
}
													
?>

	//set initial values
	var initialClientId = <?php echo $client_id; ?>;
	var initialProjectId = <?php echo $proj_id; ?>;
	var initialTaskId = <?php echo $task_id; ?>;

	function enableClockOn() {
		var isChecked = document.getElementById('clock_on_check').checked;
		var isDate = true;
		if (document.getElementById('clock_on_radio_date') != null) {
			document.getElementById('clock_on_radio_date').disabled = !isChecked;
			document.getElementById('clock_on_radio_now').disabled = !isChecked;	
			isDate = document.getElementById('clock_on_radio_date').checked;	
		}
		document.getElementById('clock_on_time_hour').disabled = !(isChecked && isDate);
		document.getElementById('clock_on_time_min').disabled = !(isChecked && isDate);		
	}
	
	function enableClockOff() {
		var isChecked = document.getElementById('clock_off_check').checked;
		var isDate = true;
		if (document.getElementById('clock_off_radio_date') != null) {
			document.getElementById('clock_off_radio_date').disabled = !isChecked;
			document.getElementById('clock_off_radio_now').disabled = !isChecked;	
			isDate = document.getElementById('clock_off_radio_date').checked;	
		}
		document.getElementById('clock_off_time_hour').disabled = !(isChecked && isDate);
		document.getElementById('clock_off_time_min').disabled = !(isChecked && isDate);		
	}
	
	function onSubmit() {
		if (document.getElementById('clock_on_check') != null) {
			if (!document.getElementById('clock_on_check').checked &&
					!document.getElementById('clock_off_check').checked)
			{
				alert('Please select an action by ticking a box: clock on, clock off, or both.');
				return;
			}
		}
			
		//copy the values of the selects
		var clientSelect = document.getElementById('clientSelect');
		document.getElementById('client_id').value = clientSelect.options[clientSelect.selectedIndex].value;
		var projectSelect = document.getElementById('projectSelect');
		document.getElementById('proj_id').value = projectSelect.options[projectSelect.selectedIndex].value;
		var taskSelect = document.getElementById('taskSelect');
		document.getElementById('task_id').value = taskSelect.options[taskSelect.selectedIndex].value;		
		
		/*alert('submitting with clientId ' + document.getElementById('client_id').value + 
						'  projectId ' + document.getElementById('proj_id').value +
						'  taskId ' + document.getElementById('task_id').value);*/
						
		document.getElementById('theForm').submit();
	}
	
	function enableProjectSelect(booleanValue) {
		var projectSelect = document.getElementById('projectSelect');
		projectSelect.disabled = !booleanValue;
		enableTaskSelect(booleanValue);
	}
	
	function enableTaskSelect(booleanValue) {		
		var taskSelect = document.getElementById('taskSelect');	
		taskSelect.disabled = !booleanValue;
		if (document.getElementById('submitButton') != null)
		  document.getElementById('submitButton').disabled = !booleanValue;
	}
	
	function populateClientSelect(selectedClientId) {
		//get the client select
		var clientSelect = document.getElementById('clientSelect');
		
		clientSelect.options[clientSelect.options.length] = new Option("All Clients", 0);
	
		//add the clients
		for (clientKey in clientProjectsHash) {
			//alert("client:" + clientKey + ", name: " + clientProjectsHash[clientKey].name);			
			clientSelect.options[clientSelect.options.length] = new Option(clientProjectsHash[clientKey].name, clientKey);
			
			if (clientKey == selectedClientId)
				clientSelect.options[clientSelect.options.length-1].selected = true;
		}		
	}
	
	function populateProjectSelect(clientId, selectedProjectId) {
	
		clearProjectSelect();
	
		var projectSelect = document.getElementById('projectSelect');
									
		//add the projects
		var empty = true;
		if (clientId == 0) {
			//load all projects
			for (key in projectTasksHash) {
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[key]['name'], key);
				empty = false;
								
				if (key == selectedProjectId)
					projectSelect.options[projectSelect.options.length-1].selected = true;
			}
		}
		else {		
			//load the projects for this client only
			var currentProjectId;
			
			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=1');
			
			for (i=0; i<clientProjectsHash[clientId]['projects'].length; i++) {
	 			currentProjectId = clientProjectsHash[clientId]['projects'][i];
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[currentProjectId]['name'], currentProjectId);
				empty = false;
								
				if (currentProjectId == selectedProjectId)
					projectSelect.options[projectSelect.options.length-1].selected = true;				
			}
		}
		
		//if there were no projects then disable the field
		if (empty) {
			projectSelect.options[projectSelect.options.length] = new Option("None assigned to you", -1);
			enableProjectSelect(false);
		}
		else
			enableProjectSelect(true);			
	}
	
	function populateTaskSelect(projectId, selectedTaskId) {

		clearTaskSelect();	
	
		//get the task select for this row
		var taskSelect = document.getElementById('taskSelect');
	
		//add the tasks
		var empty = true;
		//alert('populateTaskSelect for project ' + projectId);
		if (projectId != -1) {
			var thisProjectTasks = projectTasksHash[projectId]['tasks'];
			for (taskKey in thisProjectTasks) {				
				taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);
				empty = false;
			
				if (taskKey == selectedTaskId)
					taskSelect.options[taskSelect.options.length-1].selected = true;
			}	
		}
		
		//if there were no tasks then disable the field
		if (empty) {
			taskSelect.options[taskSelect.options.length] = new Option("None assigned to you", -1);
			enableTaskSelect(false);
		}		
		else
			enableTaskSelect(true);
	}

	function clearProjectSelect() {			
		projectSelect = document.getElementById('projectSelect');
		projectSelect.disabled = false;
		for (i=projectSelect.options.length-1; i>=0; i--)
			projectSelect.options[i] = null;
	}

	function clearTaskSelect() {	
		taskSelect = document.getElementById('taskSelect');
		taskSelect.disabled = false;
		for (i=taskSelect.options.length-1; i>=0; i--)
			taskSelect.options[i] = null;
	}
	
	function doOnLoad() {
   //are there checkboxes
   if (document.getElementById('clock_on_check') != null) {
			enableClockOn(); 
			enableClockOff();
		}
		
		initialProjectId = getValidProjectIdForClient(initialClientId, initialProjectId);
		if (initialProjectId == -1)
			initialTaskId = -1;
		else
			initialTaskId = getValidTaskIdForProject(initialProjectId, initialTaskId);
				
		populateClientSelect(initialClientId);		
		populateProjectSelect(initialClientId, initialProjectId);
		populateTaskSelect(initialProjectId, initialTaskId);
		
		if (window.resizePopupWindow)
			resizePopupWindow();
	}
	
	function onChangeClientSelect() {
		//get the selects
		var clientSelect = document.getElementById('clientSelect');	
		var projectSelect = document.getElementById('projectSelect');
		initialClientId = clientSelect.options[clientSelect.selectedIndex].value;
		initialProjectId = projectSelect.options[projectSelect.selectedIndex].value;
		initialProjectId = getValidProjectIdForClient(initialClientId, initialProjectId);		
		
   //alert('onChangeClientSelect, about to populate projects with client: ' + initialClientId + ' project: ' + initialProjectId);
		
		populateProjectSelect(initialClientId, initialProjectId);
		onChangeProjectSelect();
	}
	
	function onChangeProjectSelect() {
		var projectSelect = document.getElementById('projectSelect');
		var taskSelect = document.getElementById('taskSelect');
		initialProjectId = projectSelect.options[projectSelect.selectedIndex].value;
		initialTaskId = taskSelect.options[taskSelect.selectedIndex].value;
		initialTaskId = getValidTaskIdForProject(initialProjectId, initialTaskId);
		populateTaskSelect(initialProjectId, initialTaskId);	
		
		if (window.resizePopupWindow)
			resizePopupWindow();		
	}
	
	function onChangeTaskSelect() {
		//do nothing for now
	}
	
	function getValidProjectIdForClient(clientId, suggestedProjectId) {
	
		//make sure we have a client id
		if (clientId == 0) {
			for (key in clientProjectsHash) {
				clientId = key;			
				break;
			}		
		}
	
		//check that the project is valid for the client
		var currentProjectId;
		var validProjectId = false;
		if (clientId != 0) {
		
			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=2');
		
			for (i=0; i<clientProjectsHash[clientId]['projects'].length; i++) {
	 			currentProjectId = clientProjectsHash[clientId]['projects'][i];

				if (currentProjectId == suggestedProjectId) {
					validProjectId = true;
					break;
				}
			}
		}
		
		if (validProjectId == false) {
		
			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=3');

			if (clientProjectsHash[clientId]['projects'].length > 0)
				suggestedProjectId = clientProjectsHash[clientId]['projects'][0];
			else
				suggestedProjectId = -1;
		}
		
		return suggestedProjectId;
	}
	
	function getValidTaskIdForProject(projectId, suggestedTaskId) {
	
		if (projectId == -1)
			return -1;
	
		//check that the task is valid for the project
		var currentTaskId;
		var validTaskId = false;		
		var thisProjectTasks = projectTasksHash[projectId]['tasks'];
		for (key in thisProjectTasks) {
			if (key == suggestedTaskId) {
				validTaskId = true;
				break;
			}
		}
		
		if (validTaskId == false) {
			var replacementFound = false
			for (key in thisProjectTasks) {					
				suggestedTaskId = key;
				replacementFound = true;
				break;
			}
			
			if (!replacementFound) {
				suggestedTaskId = -1;
			}
		}	
		
		return suggestedTaskId;
	}
	
</script>