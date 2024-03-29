<?php

$currentDir = './';
$loginMsg = '';

if(isset($_GET['action']))
{
	$action = $_GET['action'];
	if($action == 'logout')
	{
		if(isset($_SESSION['username']))
		{
			unset($_SESSION['username']);
			$loginMsg = 'You are now logged out';
		}
		else
			$loginMsg = 'You are currently not logged in';
	
	}
}

//Check if user has just made a login attempt
if(isset($_POST['email']) && isset($_POST['password']))
{
	$email = $_POST['email'];
	$password = $_POST['password'];
	
	$continueLogin = true;
	
	if(!filter_var($email, FILTER_VALIDATE_EMAIL) || !ctype_alnum($password))
	{
		$loginMsg = 'Invalid email or password. Please try again';
		$continueLogin = false;
	}
	
	if(connectToDb($db) && $continueLogin)
	{
		$query = "SELECT * FROM users WHERE email = '$email' AND password = SHA1('$password')";
		$result = $db->query($query);
		if($result)
		{
			$numRows = $result->num_rows;
			if($numRows == 0)
				$loginMsg = 'Invalid email or password. Please try again';
			else
			{
				$row = $result->fetch_object();
				$username = $row->username;
				$_SESSION['username'] = $username;
				$_SESSION['email'] = $email;
				$loginMsg = 'You have successfully logged in';
			}
		}
		else
		{
			$loginMsg = 'There was a problem checking your credentials. Please contact administrator if the problem persists';
		}
	}
}


//Check if user is logged
if(isset($_SESSION['username']))
{
	echo '<h5>Welcome ' . $_SESSION['username'] . ' | <a href="logout.php?action=logout">Logout</a></h5>';
	if(!isset($loginMsg))
		$loginMsg = 'You are currently logged in';
}
else
{
	require_once($currentDir . 'login_form.html');
}
