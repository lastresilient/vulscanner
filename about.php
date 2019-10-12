<?php

session_start();
$currentDir = './';
require_once($currentDir . 'scanner/functions/databaseFunctions.php');
?>
<!DOCTYPE html>
<head>
<title>Vul Scanner</title>
<meta charset="windows-1252">
<link rel="shortcut icon" href="images/favicon.gif" />
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript" src="jquery-1.6.4.js"></script>
</head>
<body>
<!--Header Begin-->
<div id="header">
  <div class="center">
    <div id="logo"><a href="#">Vul Scanner</a></div>
    <!--Menu Begin-->
	<div id="menu">
	<?php require_once($currentDir . 'session_control.php'); ?>
	</div>
    <div id="menu">
      <ul>
        <li><a href="index.php"><span>Home</span></a></li>
        <li><a class="active" href="about.php"><span>About</span></a></li>
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
    <h2>About</h2>
  </div>
</div>
<!--Toprow END-->
<!--SubPage MiddleRow Begin-->

<!--MiddleRow END-->

<!--Footer Begin-->
<div id="footer">
  <div class="foot"> <span>Developed</span> by Nitesh</div>
</div>
<!--Footer END-->
</body>
</html>
