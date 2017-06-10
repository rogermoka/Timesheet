<?{

//Include file for timesheet.php date related functions

	//Base class for a form input. Holds the 'name' parameter
	class FormInput {
		var $name;
	
		function FormInput($name) {
			$this->name = $name;
		}
	}

	//Base class for a <select...> input. Defines functions which print the start/end tags
	class SelectInput extends FormInput	{
		function printSelect() {
			//print the input
			echo "<select name=\"" . $this->name . "\" id=\"" . $this->name . "\">";
		}

		function printEndSelect() {
			echo "</select>";
		}
	}
	
	class HourInput extends SelectInput	{	
	
		function create($selectedIndex = 9) {
			$this->printSelect();

			//work out whether we want to display time in 12 or 24 hour format
			include("table_names.inc");
			list($qhq, $numq) = dbQuery("select timeformat from $CONFIG_TABLE where config_set_id = '1'");
			$configdata = dbResult($qhq);
			
			if ($configdata[timeformat] == "12")
				$this->create_12hour($selectedIndex);
			else
				$this->create_24hour($selectedIndex);
			
			$this->printEndSelect();
		}
	
		function create_12hour($selectedIndex = 9) {			
			for ($i=0;$i<24;$i++) {
				if ($i == 0)
					$display_time = "12 am";
				elseif ($i == 12)
					$display_time = "12 pm";
				elseif ($i > 12 && $i<24)
					$display_time = $i-12 . " pm";
				else
					$display_time = "$i am";
					
				if ($i == $selectedIndex)
					echo "<option value=\"$i\" selected>$display_time</option>";
				else
					echo "<option value=\"$i\">$display_time</option>";
			}
		}
		
		function create_24hour($selectedIndex = 9) {			
			for ($i=0;$i<24;$i++) {
				$display_time = "$i";
					
				if ($i == $selectedIndex)
					echo "<option value=\"$i\" selected>$display_time</option>";
				else
					echo "<option value=\"$i\">$display_time</option>";
			}
		}
	}

	class MinuteInput extends SelectInput {

		function create($selectedMinute = 0) 	{
			$this->printSelect();
		
			for ($i=0;$i<60;$i+=5) {
				echo "<option value=\"$i\"";
					if ($selectedMinute > $i - 2 && $selectedMinute < $i + 3)
						echo " selected";
				printf(">%02d</option>\n", $i);
			}

			$this->printEndSelect();
		}		
	}

	class MonthInput extends SelectInput {
		function create($selectedMonth = 1)	{
			$this->printSelect();
			
			for ($i=1; $i<=12; $i++) {	
				echo "<option value=\"$i\"";
				if ($i == $selectedMonth)
					echo " selected";
				echo ">" . date("F", mktime(0,0,0,$i,1,2000)) . "</option>\n";
			}

			$this->printEndSelect();
		}
	}

	class DayInput extends SelectInput {

		function create($selectedDay = 1) 	{
			$this->printSelect();
			
			for ($i=1; $i<=31; $i++) {
				echo "<option value=\"$i\"";
				if ($i == $selectedDay)
					echo " selected";
				echo ">$i</option>\n";
			}

			$this->printEndSelect();
		}
	}

}?>
