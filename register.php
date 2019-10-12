<?php

session_start();
$currentDir = './';
require_once($currentDir . 'scanner/functions/databaseFunctions.php');
?>
<!DOCTYPE html>
<head>
<title>VULSCANNER</title>
<meta charset="windows-1252">
<link rel="shortcut icon" href="images/favicon.gif" />
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript" src="jquery-1.6.4.js"></script>
</head>
<body>
<!--Header Begin-->
<div id="header">
  <div class="center">
    <div id="logo"><a href="#">VULSCANNER</a></div>
    <!--Menu Begin-->
	<div id="menu">
	<?php 
	require_once($currentDir . 'session_control.php'); 
	?>
	</div>
    <div id="menu">
      <ul>
        <li><a href="index.php"><span>Home</span></a></li>
        <li><a href="about.php"><span>About</span></a></li>
		<li><a href="crawler.php"><span>Crawler</span></a></li>
		<li><a href="scanner.php"><span>Scanner</span></a></li>
		<li><a href="history.php"><span>Scan History</span></a></li>
      </ul>
    </div>
    <!--Menu END-->
  </div>
</div>
<!--Header END-->
<!--SubPage Toprow Begin-->
<div id="toprowsub">
  <div class="center">
    <h2>About Us</h2>
  </div>
</div>
<!--Toprow END-->
<!--SubPage MiddleRow Begin-->
<div id="midrow">
  <div class="center">
    <div class="textbox2">
      <p><?php require_once('display_register_form.php'); ?></p>
    </div>
  </div>
</div>
<!--MiddleRow END-->

<!--Footer Begin-->
<div id="footer">
  <div class="foot"> Developed by Bharat </div>
</div>
<!--Footer END-->
</body>
</html>
