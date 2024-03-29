<?php


require_once('functions/databaseFunctions.php');

global $user;
	
if(isset($_SESSION['username']))
{
	//Get the user's username and email address
	$username = $_SESSION['username'];
		
	if(!connectToDb($db))
	{
		echo 'There was a problem connecting to the database';
		return;
	}
	
	$query = "SELECT * FROM tests WHERE type = 'scan' AND username = '$username'";
	//echo $query;
	$result = $db->query($query);
	if($result)
	{
		$numRows = $result->num_rows;
		if($numRows == 0)
			echo 'You have not performed any previous scans';
		else
		{
			echo '<table border="3" width="900"><tr><th>ID</th><th>Start Time</th><th>URL</th><th>No. Vulnerabilities</th><th>Report</th></tr>';
			for($i=0; $i<$numRows; $i++)
			{
				$row = $result->fetch_object();
				$id = $row->id;
				$startTime = $row->start_timestamp;
				$startTimeFormatted = date('l jS F Y h:i:s A', $startTime);
				$url = $row->url;
				
				$numVulns = 'Unknown';
				$query = "SELECT * FROM test_results WHERE test_id = $id";
				$resultTwo = $db->query($query);
				if($resultTwo)
					$numVulns = $resultTwo->num_rows;
			
				$report = '<a href="scanner/reports/Test_' . $id . '.pdf" target="_blank">View</a>';
				
				echo '<tr>';
				echo "<td align='center'>$id</td>";
				echo "<td align='left'>$startTimeFormatted</td>";
				echo "<td align='left'>$url</td>";
				echo "<td align='center'>$numVulns</td>";
				echo "<td align='center'>$report</td>";
				echo '</tr>';
			
			}
			echo '</table>';

		}
	
	}
	else
		echo 'There was a problem retrieving your data from the database';
}
else
	echo 'You are not logged in. Please log in to use this feature.';





?>