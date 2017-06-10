<?php
{
  //$Header: /cvsroot/tsheet/timesheet.php/mysql.db.inc,v 1.3 2005/04/17 12:19:31 vexil Exp $
  //To port to another database, simply keep this interface and duplicate the functionality.
  //Should have plug in compatibility.
  
  function dbConnect() {  		
    $GLOBALS["CONNECTED_MYSQL_DB_INC"] = 1;
    global $DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB;
    $dbh = mysql_pconnect("$DATABASE_HOST","$DATABASE_USER","$DATABASE_PASS") or die("Unable to connect to SQL server: ". mysql_error());
    mysql_select_db("$DATABASE_DB",$dbh) or die("Unable to select database: ".mysql_error());
    return $dbh;
  }
  
  function MakeInsertString ( $data ) {
    // Takes an associative array of $data and makes a string of fields and values like
    // (field1, field2, field3) VALUES ('value1', 'value2', 'value3')
    while ( list( $key, $val ) = each( $data ) ) {
      if ($key && $val) {
	$string["fields"] .= "$key, ";
	$string["values"] .= "'" . addslashes($val) . "', ";
      }
    }
    // Remove the last comma...
    $string["fields"]=ereg_replace(", $","",$string["fields"]);
    $string["values"]=ereg_replace(", $","",$string["values"]);
    
    // Return the completed string.
    return " ( $string[fields] ) VALUES $string[values] ";
  }
  
  function MakeUpdateString ($data) {
    // Similar to above, but instead makes a string of 
    while ( list( $key, $val ) = each( $data ) ) {
      if ($data[$key]) {
	$strings .= $key . " = '" . addslashes($val) . "', ";
      }
    }
    $strings=ereg_replace(", $", " ", $strings);
    return $strings;
  }
  
  function dbQuery ($statement) {
    if (!isset($GLOBALS["CONNECTED_MYSQL_DB_INC"]))
      dbConnect();
    //$result=@mysql_query($statement) or die("<pre>\n\nCan't perform query: " . mysql_error() . " \n\n$statement\n\n</pre>");
    $result=mysql_query($statement) or die("<pre>\n\nCan't perform query: " . mysql_error() . " \n\n$statement\n\n</pre>");
    $num_rows = dbNumRows($result);
    return array($result, $num_rows);
  }
  
  function tryDbQuery ($statement) {
    if (!isset($GLOBALS["CONNECTED_MYSQL_DB_INC"]))
      dbConnect();
    //$result=@mysql_query($statement) or die("<pre>\n\nCan't perform query: " . mysql_error() . " \n\n$statement\n\n</pre>");
    $result=mysql_query($statement);
    $num_rows = dbNumRows($result);
    return array($result, $num_rows);  
  }
  
  function dbNumRows ($result) 
  {
    return @mysql_numrows($result);
  }
  
  function dbResult ($result,$i=-1) {
    if ($i >= 0) {
      @mysql_data_seek($result,$i);
    }
    return mysql_fetch_array($result);
  }
  
  function dbLastID($result) {
    return mysql_insert_id($result);
  }

}?>
