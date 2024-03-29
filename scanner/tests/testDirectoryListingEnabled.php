<?php


set_time_limit(0);

//This function check if directory listing is enabled

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

// Inculde the phpcrawl-mainclass
require_once("../../crawler/PHPCRAWL_071/classes/phpcrawler.class.php");
require_once("../../crawler/PHPCRAWL_071/classes/mycrawler.php");	
					
testDirectoryListingEnabled('http://localhost/testsitewithvulns/' ,500);//Just for testing
*/

function testDirectoryListingEnabled($urlToScan, $siteBeingTested, $testId, $crawlUrlFlag){

connectToDb($db);
updateStatus($db, "Testing for $urlToScan for Directory Listing enabled...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');
$log->lwrite("Testing for $urlToScan for Directory Listing enabled");

if($crawlUrlFlag)
{
	//Perform crawl again but allow images, etc. this time to capture every URL
	$crawlerNew = &new MyCrawler();
	$crawlerNew->setURL($urlToScan);
	$crawlerNew->setTestId($testId);
	$crawlerNew->addReceiveContentType("/text\/html/");
	$crawlerNew->setCookieHandling(true);
	$crawlerNew->setFollowMode(3);
	$log->lwrite("Crawling $urlToScan again for all links including images, css, etc, in order to identify directories");
	$crawlerNew->go();
	$urlsFound = $crawlerNew->urlsFound;

	$logStr = sizeof($urlsFound) . ' URLs found for test: ' . $testId;

	$log->lwrite("All URLs found during crawl for directory listing check:");
	foreach($urlsFound as $currentUrl)
	{
		$log->lwrite($currentUrl);
	}

	$relativePathUrls = array();

	foreach($urlsFound as $currentUrl)
	{
		$currentUrl = str_replace($urlToScan,'', $currentUrl);
		array_push($relativePathUrls,$currentUrl);
	}

	$directories = array();

	//Check if relative path contain a directory and if they do, add it to a list of directories
	foreach($relativePathUrls as $relativePathUrl)
	{
		if(dirname($relativePathUrl) != '.')
		{
			$dir = dirname($relativePathUrl);
										
			if(!in_array($dir, $directories) && !empty($dir) && (!strpos($dir,'?')))
			{
				array_push($directories, $dir);
				$log->lwrite("Found directory $dir");
			}
		}
	}
}
else
	$directories = array(1);//Just need to make an array of size one so the for loop below iterates once

$http = new http_class;
$http->timeout=0;
$http->data_timeout=0;
//$http->debug=1;
$http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
$http->follow_redirect=1;
$http->redirection_limit=5;
$http->setTestId($testId);

//Regular expressions that will indicate directory listing is enabled
$regexs = array("/Parent Directory/", //Microsoft IIS
				"/\\bDirectory Listing\\b.*(Tomcat|Apache)/", //Apache
				"/Parent directory/", //General 
				"/\\bDirectory\\b/", //General
				"/[\\s<]+IMG\\s*=/"); //General
				
foreach($directories as $directory)
{
	if($crawlUrlFlag)
		$testUrl = $urlToScan . $directory . '/';
	else
		$testUrl = $siteBeingTested;
	
	$error=$http->GetRequestArguments($testUrl,$arguments);
						
	$error=$http->Open($arguments);
	
	$log->lwrite("URL to be requested is: $testUrl");
	
	if($error=="")
	{
		$log->lwrite("Sending HTTP request to $testUrl");
		$error=$http->SendRequest($arguments);
		
		if($error=="")
		{
			$headers=array();
			$error=$http->ReadReplyHeaders($headers);
			if($error=="")
			{				
				$responseCode = $http->response_status;//This is a string
				$log->lwrite("Received response code: $responseCode");
				if(intval($responseCode) >= 200 && intval($responseCode) <300)//Directory listing enabled, would receive 403 if it was not
				{
					$vulnerabilityFound = false;
					$error = $http->ReadWholeReplyBody($body);
							
					if(strlen($error) == 0)
					{	
						$indicatorStr='';
						if(preg_match($regexs[0],$body))
						{
							$vulnerabilityFound = true;
							$indicatorStr = $regexs[0];
						}
						else if(preg_match($regexs[1],$body))
						{
							$vulnerabilityFound = true;
							$indicatorStr = $regexs[1];
						}
						else if(preg_match($regexs[2],$body))
						{
							$vulnerabilityFound = true;
							$indicatorStr = $regexs[2];
						}
						else if(preg_match($regexs[3],$body))
						{
							if(preg_match($regexs[4],$body))
							{
								$vulnerabilityFound = true;
								$indicatorStr = $regexs[3] . ' and ' . $regexs[4];
							}
						}
						if($vulnerabilityFound)
						{
							//The echo's are for testing function on its own
							echo '<br>Directory Listing Enabled!<br>Url: ' . $testUrl . '<br>';
							echo 'Method: GET <br>';
							echo 'Url Requested: ' . $testUrl . '<br>';
							echo "Error: Received response code: $responseCode after requesting a directory and regular expression: $indicatorStr<br>";
							$tableName = 'test' . $testId;	
							
							//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
							$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'dirlist' AND method = 'get' AND url = '$testUrl' AND attack_str = '$testUrl'";
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
									insertTestResult($db, $testId, 'dirlist', 'get', $testUrl, $testUrl);
								}
							}	
						}
					}
				}
			}
		}
		$http->Close();
	}
	if(strlen($error))
	{
		echo "<H2 align=\"center\">Error: ",$error,"</H2>\n";
		$log->lwrite("Error: $error");
	}
}
}
?>