<?php
// 	CONFIGURE data for SuperScan's scanner script

//	Account to access
//		Account name does not need to match the real account name
//			because the account is managed by the PATH definition
$acct = 'MyAccount';

//	Set SCAN_PATH (where the scanner starts)
//		AND SCANNER_PATH (dir for the scanner scripts)
//	IP address 127.0.0.1 allows for local testing before upload
if ($_SERVER['REMOTE_ADDR']=='127.0.0.1')
{
//	***	define("SCAN_PATH", '{local Virtual Host}');
	define("SCAN_PATH", 'X:/path/to/account/');
//	*** define("SCANNER_PATH", '{local path to}'.'/scandb.php}');
	define("SCANNER_PATH", 'X:/path/to/superscan/');
} else {
	//	For security, set SCAN_PATH inside your webspace
	define("SCAN_PATH", "/home/account/public_html/");
	// 	and SCANNER_PATH outside (NOT in public_html)	
	define("SCANNER_PATH", "/home/account/superscan/");
}


//	Used for testing only!!! Leave false for production use!!!
//		$testing = true produces prolific troubleshooting output
$testing = false;


//	SET Report Output

//	Output as e-mail (true or false)
//		Recommend false for testing and true for CRON
$email_out = false;

//	E-mail address(es) to send reports of change
$addresses = array("user1@domain1.com", "user2@domain2.com");

//	Output to monitor (true or false)
//		Recommend true for testing and FALSE for CRON
$report_out = false;

//	Extensions to fetch
//  	Example: $ext = array("php", "html", "htm", "js");   
//	Recommended: An empty array will return ALL extensions 
//		which is best for real security
$ext = array();

// 	extensions to omit
//		An empty array will return all extensions
//      *** The $excl_ext array can only contain elements *** 
//		  *** if $ext array above is empty *** 
$excl_ext = array('ftpquota','txt');

// 	directories to ignore
//		An empty array will check all directories
$skip = array("protected", "private");


//	END OF CONFIGURE
?>
