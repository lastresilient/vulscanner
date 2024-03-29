<?php


set_time_limit(0);

//This function checks for unvalidated redirects. 

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
					
testUnvalidatedRedirects($urlsToTest ,500);//Just for testing
*/

function testUnvalidatedRedirects($arrayOfUrls, $testId){

connectToDb($db);
updateStatus($db, "Testing all URLs for Unvalidated Redirects...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');

$log->lwrite("Starting Unvalidated Redirects test function on all URLs");

$http = new http_class;
$http->timeout=0;
$http->data_timeout=0;
//$http->debug=1;
$http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
$http->follow_redirect=0;
$http->setTestId($testId);

//Identify which URLs, if any, cause redirects
$log->lwrite("Identifying which URLs, if any, cause redirects");
updateStatus($db, "Identifying which URLs, if any, cause redirects...", $testId);

$potentiallyVulnUrls = array();

foreach($arrayOfUrls as $currentUrl)
{
	$error=$http->GetRequestArguments($currentUrl,$arguments);
						
	$error=$http->Open($arguments);
	
	$log->lwrite("URL to be requested is: $currentUrl");
	
	if($error=="")
	{
		$log->lwrite("Sending HTTP request to $currentUrl");
		$error=$http->SendRequest($arguments);
		
		if($error=="")
		{
			$headers=array();
			$error=$http->ReadReplyHeaders($headers);
			if($error=="")
			{				
				$responseCode = $http->response_status;//This is a string
				$log->lwrite("Received response code: $responseCode");
				if(intval($responseCode) >= 300 && intval($responseCode) <400)
				{
					array_push($potentiallyVulnUrls, $currentUrl);
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

$log->lwrite("Potentially Vulnerable URLs:");
foreach($potentiallyVulnUrls as $currentUrl)
	$log->lwrite("$currentUrl");

updateStatus($db, "Beginning testing each potentially vulnerable URL for unvalidated redirects ...", $testId);

$redirectDomain = 'www.whatismyip.com';

foreach($potentiallyVulnUrls as $currentUrl)
{
	updateStatus($db, "Testing $currentUrl for Unvalidated Redirects...", $testId);
	$log->lwrite("Testing $currentUrl for unvalidated redirects");
	echo "<br>Testing: $currentUrl <br>";
	$parsedUrl = parse_url($currentUrl);
	$query = $parsedUrl['query'];
	$parameters = array();
	parse_str($query,$parameters);
	$newQuery = '';
	$query = urldecode($query);
	$originalQuery = $query;
	if($parsedUrl)
	{
		foreach($parameters as $para)
		{
			$query = $originalQuery;
			if(stripos($para,'http') || stripos($para,'www'))
			{
				if(stripos($para,'http')===0)
				{	
					$newRedirectDomain = 'http://' . $redirectDomain;
					$newQuery = str_replace($para, $newRedirectDomain, $query);
					$query = $newQuery;
					$newRedirectDomain = '';
				}
				else if(stripos($para,'www')===0 && !strpos($para,'http')===0)
				{
					$newQuery = str_replace($para, $redirectDomain, $query);
					$query = $newQuery;
				}
			}
			else//There is no parameter that looks like a URL but a redirect is still caused. Just replace all parameters with http://www.whatsmyip.com
			{
				$newRedirectDomain = 'http://' . $redirectDomain;
				$newQuery = str_replace($para, $newRedirectDomain, $query);
				$query = $newQuery;
				$newRedirectDomain = '';
			}

			$scheme = $parsedUrl['scheme'];
			$host = $parsedUrl['host'];
			$path = $parsedUrl['path'];
			
			$testUrl = $scheme . '://' . $host . $path . '?' . $newQuery;
			$log->lwrite("URL to be requested is: $testUrl");
			
			$error=$http->GetRequestArguments($testUrl,$arguments);
							
			$error=$http->Open($arguments);
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
						$error = $http->ReadWholeReplyBody($body);
						
						if(strlen($error) == 0)
						{	
							//Check if the location in the HTTP response is the URL added as a parameter
							//If it is this would cause the browser to redirect to the parameter, therefore the vulnerability is present
							echo 'Location header is ' . $headers['location'] . '<br>';
							$redirectTarget = $headers['location'];
							if(strpos($redirectTarget, $redirectDomain) || $redirectTarget == $redirectDomain)
							{
								//The echo's here are for testing/debugging the function on its own
								echo '<br>Unvalidated Redirects Present!<br>Url: ' . $currentUrl . '<br>';
								echo 'Method: GET <br>';
								echo 'Url Requested: ' . $testUrl . '<br>';
								echo 'Error: Successfully Redirected to www.whatsmyip.com<br>';
								$tableName = 'test' . $testId;

								//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
								$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'unredir' AND method = 'get' AND url = '$currentUrl' AND attack_str = '$testUrl'";
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
										insertTestResult($db, $testId, 'unredir', 'get', $currentUrl, $testUrl);
									}
								}	
								$http->Close();
								break;	
							}
						}
					}
				}
				$http->Close();
			}
			if(strlen($error))
				echo "<H2 align=\"center\">Error: ",$error,"</H2>\n";
			}	
	}
	else
		$log->lwrite("Could not parse malformed URL: $currentUrl");
		
	}
}
?>