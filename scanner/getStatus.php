<?php

$currentDir = './';
require_once($currentDir . 'functions/databaseFunctions.php');
//require_once('classes/Logger.php');

isset($_POST['testId']) ? $testId = $_POST['testId'] : $testId = 0;

connectToDb($db);

$query = "SELECT * FROM tests WHERE id = $testId;"; 
$result = $db->query($query);
$row = $result->fetch_object();
$finished = $row->scan_finished;

//Update finish time to current time while scan is not finished
if($finished == 0)
{
	$now = time();
	$query = "UPDATE tests SET finish_timestamp = $now WHERE id = $testId;"; 
	$result = $db->query($query); 
}

$query = "SELECT * FROM tests WHERE id = $testId;"; 
$result = $db->query($query); 

$row = $result->fetch_object();
$status = $row->status;
$startTime = $row->start_timestamp;
$finTime = $row->finish_timestamp;
$count = $row->numUrlsFound;
$numRequests = $row->num_requests_sent;

$duration = $finTime - $startTime;
$mins = intval($duration/60);
$seconds = $duration % 60;
$secondsStr = strval($seconds);
$secondsFormatted = str_pad($secondsStr,2,"0",STR_PAD_LEFT);

$query = "SELECT * FROM test_results WHERE test_id = $testId;"; 
$result = $db->query($query); 
$numVulns = $result->num_rows;

//TODO: Put table here, looks bit messy
echo '<b>Scan Details:</b><br>';
echo 'Status: ' . $status;

echo "<br><br>No. URLs Found: $count";
echo "<br>Time Taken: $mins:$secondsFormatted";
echo "<br>No. HTTP Requests Sent: $numRequests";
echo "<br>No. Vulnerabilities Found: $numVulns";

$result->free();
$db->close();

?>