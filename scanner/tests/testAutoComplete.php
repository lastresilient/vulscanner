<?php


set_time_limit(0);

//This function checks the URL for password input fields and if any are found,
//checks if they have autocomplete enabled

/*
//These are only for testing script on its own
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

testAutoComplete('http://localhost/testsitewithvulns/login.php',500);//Just for testing
*/

function testAutoComplete($urlToCheck, $testId){

connectToDb($db);
updateStatus($db, "Testing $urlToCheck for autocomplete enabled ...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');

$log->lwrite("Starting autocomplete test function on $urlToCheck");

//Array containing all input fields
$arrayOfInputFields = array();

$log->lwrite("Searching $urlToCheck for input fields");

//Check URL is not responding with 5xx codes
$log->lwrite("Checking what response code is received from $urlToCheck");
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
			$responseCode = $http->response_status;//This is a string
			$log->lwrite("Received response code: $responseCode");
			if(intval($responseCode) >= 500 && intval($responseCode) <600)
			{
				$log->lwrite("Response code: $responseCode received from: $urlToCheck");
				return;
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

$html = file_get_html($urlToCheck, $testId);

if(empty($html))//Checks if null or false, etc.
{
	//This can happen due to file_get_contents returning a 500 code. Then the parser won't parse it
	updateStatus($db, "Problem getting contents from $urlToCheck...", $testId);
	$log->lwrite("Problem getting contents from $urlToCheck");
	return;
}

foreach($html->find('input') as $input) 
{
	$vulnerabilityFound = false;
	if(isset($input->attr['type']))
	{
		$inputType = $input->attr['type'];
		if($inputType == 'password')
		{
			if(isset($input->attr['autocomplete']))
			{
				$inputAutoComplete = $input->attr['autocomplete'];
				if(strcasecmp($inputAutoComplete, 'off') != 0)
					$vulnerabilityFound = true;
			}
			else
				$vulnerabilityFound = true;
			
			if($vulnerabilityFound)
			{	
				$inputName = $input->attr['name'];
				
				echo 'Autocomplete enabled!<br>';
				echo 'Method: get <br>';
				echo 'Url: $urlToCheck<br>';
				echo "Error: Input field with name: $inputName is of type: password and does not have autocomplete disabled";
				$tableName = 'test' . $testId;
			
				//Check if this vulnerability has already been found and added to DB. If it hasn't, add it to DB.
				$query = "SELECT * FROM test_results WHERE test_id = $testId AND type = 'autoc' AND method = 'get' AND url = '$urlToCheck' AND attack_str = '$inputName'";
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
						insertTestResult($db, $testId, 'autoc', 'get', $urlToCheck, $inputName);
					}
				}	
			}
		}
	}
}	

}

?>