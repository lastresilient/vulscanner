<?php

$currentDir = './';
$displayForm = true;

if(isset($_SESSION['username']))
{
	echo 'You are currently logged in. You must be logged out to create an account';
	$displayForm = false;
}
else
{
	if(isset($_POST['regusername']) || isset($_POST['regpassword']) || isset($_POST['regpassword2']) ||
			 isset($_POST['email']))
	{
		if(empty($_POST['regusername']) || empty($_POST['regpassword']) || empty($_POST['regpassword2']) ||
			 empty($_POST['email']))
		{
			echo 'One or more input fields in the form were empty. You must fill in all input fields';	
		}
		else if($_POST['regpassword'] != $_POST['regpassword2'])
		{
			echo 'The confirmation of the password does not match the first password entered';
		}
		else if(!ctype_alnum($_POST['regusername']) || !ctype_alnum($_POST['regpassword']))//only hav to check the first password as the second password entered is equal to this (checked above)
		{
			echo 'Username and passwords must be alphanumeric. Please try again';
		}
		else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
		{
			echo 'The email address entered does not appear to be a valid email. If it is a valid email address, please contact our administrator';
		}
		else//everything should be ok if we make it to here
		{
			$username = $_POST['regusername'];
			$password = $_POST['regpassword'];
			$email = $_POST['email'];

			if(connectToDb($db))
			{
				$query = "SELECT * FROM users WHERE username = '$username'";
				$result = $db->query($query);
				if($result)
				{
					$numRows = $result->num_rows;
					if($numRows > 0)
						echo 'Sorry, this username already exists. Please try again';
					else
					{
						$query = "SELECT * FROM users WHERE email = '$email'";
						$result = $db->query($query);
						if($result)
						{
							$numRows = $result->num_rows;
							if($numRows > 0)
								echo 'Sorry, an account already exits with this email address. Please try again';
							else
							{
								$query = "INSERT INTO users VALUE('$username',SHA1('$password'),'$email')";
								$result = $db->query($query);
								if($result)
								{
									echo 'Congradulations! You have successfully registered, please login to enjoy our features';
									$displayForm = false;
								}
								else
									echo 'There was a problem connecting to the database. Please contact the administrator if problem persists';
							}
						}
						else
							echo 'There was a problem connecting to the database. Please contact the administrator if problem persists';
					}
				}
				else
					echo 'There was a problem connecting to the database. Please contact the administrator if problem persists';
			
			}
			else
				echo 'There was a problem connecting to the database. Please contact the administrator if problem persists';
	
		}
	}
}
if($displayForm)
	require_once($currentDir . 'register_form.html');
?>
