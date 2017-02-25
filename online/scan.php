<!DOCTYPE HTML>
<html>
<head>
<title>Manual Scan</title>
</head>

<body>
<h1>Manual Scan</h1>
<?php 
//	Lets you know that the script is running

//	Assuming that this file is located in the 
//		domain's DOCUMENT_ROOT (public_html) and 
//		SuperScan is located at the same level 
//		as public_html ...
include('../SuperScan/scanner.php'); 

//	Lets you know that the script has completed 
//		its scan, reporting and clean-up
?>
<p>Scan Completed</p>
</body>
</html>