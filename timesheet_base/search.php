<?php
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = 'Itr@n%55';
$dbName = 'timesheet';
//connect with the database
$db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);
//get search term
$searchTerm = $_GET['term'];
//get matched data from skills table
$query = $db->query("SELECT * FROM timesheet_project WHERE title LIKE '%".$searchTerm."%' ORDER BY title ASC");
while ($row = $query->fetch_assoc()) {
    $data[] = $row['title'];
}
//return json data
echo json_encode($data);
?>