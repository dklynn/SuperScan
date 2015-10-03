<?php

// 	CONFIGURE

//	SET Report Output
//	Output to e-mail addresses and/or to STDOUT (monitor)

//	Output as e-mail (true or false)
//		Recommend false for testing and true for CRON
$email_out = true;

//	E-mail addresses to send report of change
$addresses = array("address1@domain.com","address2@domain.com");

//	Output to monitor (true or false)
//		Recommend true for testing and false for CRON
$report_out = false;

//	Account to access
//		Account name does not need to match the real account 
//			name but is managed by the PATH definition
$acct = 'MyAccount';

//	Set PATH
if ($_SERVER['REMOTE_ADDR']=='127.0.0.1')
{
//	*** define("PATH_DB", '{local path to}'.'/db.php}');
	define("PATH_DB", 'X:/path/to/superscan/scandb.php}');
} else {
	define("PATH_DB", "/home/account/scandb.php");
}
//	END OF CONFIGURE

$report = "SuperScan Daily Report\r\n\r\n";

//	Prepare scan report
$yesterday = date("Y-m-d H:i:s", mktime(date('H'), date('i'), date('s'), date('n'), date('j')-1,date('Y')));

$report .= "SuperScan log report for $acct file changes since ".$yesterday.":\r\n\r\n";

// 	use define statements or enter values directly in the mysqli_connect
include('scandb.php');

//	$scandb = mysqli_connect(SERVER,USER,PASS,DATABASE);

$results = mysqli_query($scandb,"SELECT stamp, status, file_path, file_last_mod FROM history WHERE acct = '$acct' AND stamp > '$yesterday'");
if (!$results)
{
	$report .="No log entries available!\r\n ";
} else {
	while($result=mysqli_fetch_array($results))
	{
		$report .= $result['stamp']." =>  ".strtoupper($result['status'])." =>  ".$result['file_path']."\r\n";
	}
}

// OUTPUT Report
if ($email_out)
{
	$to = (count($addresses)>1) ? implode(", ", $addresses): $addresses[0];
	$mailed = mail($to, $acct . ' Integrity Monitor Log Report',$report); 
}

// To TEST this script, activate the following line on
if ($report_out) echo nl2br($report);

mysqli_close($scandb);
?>
