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
<script type="text/javascript" src="js/swfobject/swfobject.js"></script>
<script type="text/javascript">
var flashvars = {};
flashvars.xml = "config.xml";
flashvars.font = "font.swf";
var attributes = {};
attributes.wmode = "transparent";
attributes.id = "slider";
swfobject.embedSWF("cu3er.swf", "cu3er-container", "960", "270", "9", "expressInstall.swf", flashvars, attributes);
</script>
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
        <li><a class="active" href="index.php"><span>Home</span></a></li>
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
<!--Toprow Begin-->
<div id="toprow">
  <div class="center">
    <div id="cubershadow">
      <div id="cu3er-container"> <a href="http://www.adobe.com/go/getflashplayer"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="" /> </a> </div>
    </div>
  </div>
</div>
<!--Toprow END-->

<!--BottomRow Begin-->
<div id="bottomrow">
  <div class="textbox">
    <h1>Vul Scanner - The Web Application Vulnerability Scanner</h1>
    <p>Vul Scanner firstly crawls the target website to identify all URLs belonging to the website. It tests each URL for a number of vulnerabilities and emails you a detailed PDF report once the scan is complete.</p></div>
</div>
<!--BottomRow END-->
<!--Footer Begin-->
<div id="footer">
  <div class="foot"> <span>Developed </span> by Nitesh </div>
</div>
<!--Footer END-->
</body>
</html>
