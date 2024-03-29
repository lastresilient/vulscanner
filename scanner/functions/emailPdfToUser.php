<?php


//This script emails a PDF report to the user
/*
//For testing:

require_once('databaseFunctions.php');
require_once('../classes/Logger.php');

emailPdfToUser('/../reports/Test_101.pdf', 'Dermot', 'youremail@hotmail.com',101);
*/

function emailPdfToUser($fileName, $username, $email, $testId)
{

connectToDb($db);
updateStatus($db, "Emailing PDF report to $email...", $testId);

$log = new Logger();
$log->lfile('logs/eventlogs');
$log->lwrite("Starting email PDF function for test: $testId");

if(file_exists($fileName))
{
	$log->lwrite("File: $fileName exists");
	
	$fileatt = $fileName; // Path to the file 
	$fileatt_type = "application/pdf"; // File Type 
	$fileatt_name = 'Test_' . $testId . '.pdf'; // Filename that will be used for the file as the attachment 

	$email_from = "aashishgupta0409@gmail.com"; // Who the email is from, don't think this does anything
	$email_subject = "Vulnerability Scan Detailed Report"; // The Subject of the email 
	$email_message = "Hello $username,<br><br>";
	$email_message .= 'Thank you for scanning with VulScanner. Please find the scan results attached in the PDF report.<br><br>';
	$email_message .= 'Please reply to this email if you have any questions.<br><br>';
	$email_message .= 'Kind Regards,<br><br>';
	$email_message .= 'Team VulScanner<br>';

	$email_to = $email; // Who the email is to 

	$headers = "From: ".$email_from; 

	$file = fopen($fileatt,'rb'); 
	$data = fread($file,filesize($fileatt)); 
	fclose($file); 

	$semi_rand = md5(time()); 
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

	$headers .= "\nMIME-Version: 1.0\n" . 
	"Content-Type: multipart/mixed;\n" . 
	" boundary=\"{$mime_boundary}\""; 

	$email_message .= "This is a multi-part message in MIME format.\n\n" . 
	"--{$mime_boundary}\n" . 
	"Content-Type:text/html; charset=\"iso-8859-1\"\n" . 
	"Content-Transfer-Encoding: 7bit\n\n" . 
	$email_message .= "\n\n"; 

	$data = chunk_split(base64_encode($data)); 

	$email_message .= "--{$mime_boundary}\n" . 
	"Content-Type: {$fileatt_type};\n" . 
	" name=\"{$fileatt_name}\"\n" . 
	//"Content-Disposition: attachment;\n" . 
	//" filename=\"{$fileatt_name}\"\n" . 
	"Content-Transfer-Encoding: base64\n\n" . 
	$data .= "\n\n" . 
	"--{$mime_boundary}--\n"; 

	$mailSent = mail($email_to, $email_subject, $email_message, $headers); 

	if($mailSent) 
		$log->lwrite("$fileName successfully sent to $email");
	else 
		$log->lwrite("There was a problem sending $fileName to $email");	

}
else
	$log->lwrite("File: $fileName does not exist");

}





?>