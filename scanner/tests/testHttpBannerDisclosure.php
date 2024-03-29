<?php


set_time_limit(0);

//This function checks if any sensitive details are disclosed by the web server in the HTTP resonse headers. 
//e.g. versions of server, programming language, operating system, etc.

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

testHttpBannerDisclosure('http://127.0.0.1/testsitewithvulns/',500);//Just for testing
*/

function testHttpBannerDisclosure($urlToCheck, $testId){

connectToDb($db);
updateStatus($db, "Testing $urlToCheck for HTTP Banner Disclosure...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');

$log->lwrite("Starting HTTP Banner Disclosure test function on $urlToCheck");

$http = new http_class;
$http->timeout=0;
$http->data_timeout=0;
//$http->debug=1;
$http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
$http->follow_redirect=1;
$http->redirection_limit=5;
$http->setTestId($testId);

$error=$http->GetRequestArguments($urlToCheck,$arguments);
						
$error=$http->Open($arguments);

$log->lwrite("URL to be requested is: $urlToCheck");

//TODO: add more to these arrays
$serverHeaders = array('Apache',
					   'Win32',
					   'mod_ssl',
					   'OpenSSL',
					   'PHP',
					   'mod_perl',
					   'Perl',
					   'Ubuntu',
					   'Python',
					   'mod_python',
					   'Microsoft',
					   'IIS',
					   'Unix',
					   'Linux');
					   
$xPowByHeaders = array('PHP',
					   'ASP',
					   'NET',
					   'JSP',
					   'JBoss',
					   'Perl',
					   'Python');

if($error=="")
{
	$log->lwrite("Sending HTTP request to $urlToCheck");
	$error=$http->SendRequest($arguments);
	
	if($error=="")
	{
		$headers=array();
		$error=$http->ReadReplyHeaders($headers);
		if($error=="")
		{			
			
			
			if(isset($headers['server']))
			{
				$serverHeader = $headers['server'];
				foreach($serverHeaders as $currentHeader)
				{
					if(stripos($serverHeader, $currentHeader) !== false)
					{
						echo "<br>Found $currentHeader in $serverHeader";
						echo '<br>HTTP Banner Disclosure Present!<br>Url: ' . $urlToCheck . '<br>';
						echo 'Method: GET <br>';
						echo 'Url Requested: ' . $urlToCheck . '<br>';
						echo 'Info Disclosed: Server: ' . $serverHeader . '<br>';
						$tableName = 'test' . $testId;
						
						//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
						$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'bannerdis' AND method = 'get' AND url = '$urlToCheck' AND attack_str = '$serverHeader'";
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
								insertTestResult($db, $testId, 'bannerdis', 'get', $urlToCheck, $serverHeader);
							}
						}	
						break;
					}
				}
			}
			else
			{
				$log->lwrite("Server header for $urlToCheck is empty");
				echo "Server header for $urlToCheck is empty<br>";
			}
			if(isset($headers['x-powered-by']))
			{
				$xPowByHeader = $headers['x-powered-by'];
				foreach($xPowByHeaders as $currentHeader)
				{
					if(stripos($xPowByHeader, $currentHeader) !== false)
					{
						//The echo's here are for testing/debugging the function on its own
						echo "<br>Found $currentHeader in $xPowByHeader ";
						echo '<br>HTTP Banner Disclosure Present!<br>Url: ' . $urlToCheck . '<br>';
						echo 'Method: GET <br>';
						echo 'Url Requested: ' . $urlToCheck . '<br>';
						echo 'Info Disclosed: X-Powered-by: ' . $xPowByHeader . '<br>';
						$tableName = 'test' . $testId;
						
						//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
						$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'bannerdis' AND method = 'get' AND url = '$urlToCheck' AND attack_str = '$xPowByHeader'";
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
								insertTestResult($db, $testId, 'bannerdis', 'get', $urlToCheck, $xPowByHeader);
							}
						}	
						break;
					}
				}
			}
			else
			{
				$log->lwrite("X-Powered-by header for $urlToCheck is empty");
				echo "X-Powered-by header for $urlToCheck is empty<br>";
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
?>