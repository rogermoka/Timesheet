<?php

/**
* A function to perform enumeration defines easily. Use it as follows:
*
*	enum("None", "Bob", "Joe", "John");
*/
function enum() {
   $ArgC = func_num_args();
   $ArgV = func_get_args();

   for($Int = 0; $Int < $ArgC; $Int++) define($ArgV[$Int], $Int);
}

?>
