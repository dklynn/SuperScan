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
	//	define("SCANNER_PATH", 'X:/path/to/superscan/');
} else {
	//	For security, set SCAN_PATH inside your webspace
	define("SCAN_PATH", "/home/account/public_html/");
	// 	and SCANNER_PATH outside (NOT in public_html)	
	//	define("SCANNER_PATH", "/home/account/superscan/");
}
$scan_path_length = strlen(SCAN_PATH);

//	Used for testing only!!! Leave false for production use!!!
//		$testing = true produces prolific troubleshooting output
$testing = false;


//	SET Report Output

//	Output to monitor (true or false)
//		Recommend true for testing and FALSE for CRON
$report_out = false;

//	Output as e-mail (true or false)
//		Recommend false for testing and true for CRON
$email_out = false;

//	E-mail address(es) to send reports of change
$addresses = array("user1@domain1.com", "user2@domain2.com");


//	Extensions to fetch
//  	Example: $ext = array("php", "html", "htm", "js");   
//	Recommended: An empty array will return ALL extensions 
//		which is best for real security
$ext_array = array();
//	Make extensions lower case for scanner comparison
$ext_array = array_map('strtolower',$ext_array);

// 	extensions to omit
//		An empty array will return all extensions
//      *** The $excl_ext array can only contain elements *** 
//		*** if $ext array above is empty *** 
$excl_array = array('ftpquota','txt','swf','fla');
//	Make extensions lower case for scanner comparison
$excl_array = array_map('strtolower',$excl_array);

//	Scan extensionless files?
$extensionless = false;

// 	directories to ignore
//		An empty array will check all directories in the SCAN_PATH tree
$skip = array("protected", "private");

//	$indent for report indent
$indent = " &nbsp; &nbsp;";
$indent2 = $indent . $indent;

//	END OF CONFIGURE
?>
