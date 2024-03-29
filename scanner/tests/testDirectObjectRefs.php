<?php


set_time_limit(0);

//This function checks each of the URLs for direct object references exposed as parameters. e.g. files, directories.

//For testing:
/*
//Include parsing class and http library
require_once('../classes/simplehtmldom/simple_html_dom.php');
//require_once('HTTPclasses/HTTPclient/HTTPClient.class.php');
require_once('../classes/httpclient-2011-08-21/http.php');

//Include Entity Classes
require_once('../classes/Form.php');
require_once('../classes/InputField.php');
require_once('../classes/Logger.php');
require_once('../classes/PostOrGetObject.php');

//Include Function Scripts
require_once('../functions/commonFunctions.php');
require_once('../functions/databaseFunctions.php');
				
testDirectObjectRefs($testUrls,500);//Just for testing
*/

function testDirectObjectRefs($arrayOfURLs, $testId){

connectToDb($db);
updateStatus($db, "Testing all URLs for Insecure Direct Object References...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');

$log->lwrite("Identifying which URLs have parameters");

$log->lwrite("All URLs found during crawl:");

$urlsWithParameters = array();

foreach($arrayOfURLs as $currentUrl)
{
	$log->lwrite($currentUrl);
	if(strpos($currentUrl,"?"))
		array_push($urlsWithParameters, $currentUrl);
}

$log->lwrite("URLs with parameters:");
foreach($urlsWithParameters as $currentUrl)
	$log->lwrite($currentUrl);

$log->lwrite("Testing each URL that has parameters");
foreach($urlsWithParameters as $currentUrl)
{
	$parsedUrl = parse_url($currentUrl);
	if($parsedUrl)
	{
		$query = $parsedUrl['query'];
		$parameters = array();
		parse_str($query,$parameters);
		foreach($parameters as $para)
		{
			if(preg_match('/\.([^\.]+)$/',$para))
			{
				//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
				$tableName = 'test' . $testId;
				$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'idor' AND method = 'get' AND url = '$currentUrl' AND attack_str = '$para'";
				$result = $db->query($query);
				if(!$result)
					$log->lwrite("Could not execute query $query");
				else
				{
					$log->lwrite("Successfully executed query $query");
					$numRows = $result->num_rows;
					if($numRows == 0)
					{	
						$log->lwrite("Number of rows is $numRows for query: $query");
						insertTestResult($db, $testId, 'idor', 'get', $currentUrl, $para);
					}
				}
			}
		}
	}
	else
		$log->lwrite("Could not parse malformed URL: $currentUrl");
}
		
}

?>