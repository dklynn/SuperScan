<?php
//	INTRO
//
//	Introductory comments are provided in the ReadMe.txt file
//
//	End of Intro

//	CONFIGURE data for SuperScan's scanner and reporter scripts

//	Account to access
//		Account name does not need to match the real account name
//		Account name is only used for reports and SELECT for the baseline table
$acct = 'Account Name';

//	For testing, set TRUE else FALSE for production use
$localtesting = false;

//	Set SCAN_PATH (where the scanner starts)
//		AND SCANNER_PATH (dir for the SuperScan scripts)
//		and be sure to use a trailing / for both!
//
//	WARNING!!!
//
//		The SCAN_PATH must not cause a file to be scanned within another scan.
//		To do so will result in a database error because the path to the file
//			is the UNIQUE key in the (shared) baseline table.
//
//		Additionally, filenames must NOT have single quote (') in them as that 
//			will interfere with database queries.
//
//	WARNING!!! 
//
//	File paths exceeding field length for file_path will result in database errors!

if ($localtesting)
{
//	define('SCAN_PATH', '{local Virtual Host}');
	define('SCAN_PATH', 'X:/path/to/directory/');
//	define('SCANNER_PATH', '{local path to}/SuperScan/');
	define('SCANNER_PATH', 'X:/path/to/SuperScan/');
} else {
	//	Example:	For security, set SCAN_PATH inside your webspace
	define('SCAN_PATH', '/home/account/public_html/');
	//	Example:	SCANNER_PATH outside webspace (NOT in public_html)
	define('SCANNER_PATH', '/home/account/SuperScan/');
}

$scan_path_length = strlen(SCAN_PATH);

//	$testing is used for testing only!!! Leave FALSE for production use!!!
//		$testing = TRUE produces prolific troubleshooting output
$testing = false;

//	SET Report Output

//	Output as e-mail (true or false)
//		Recommend FALSE for testing and TRUE for CRON
$email_out = true;

//	Set e-mail

//	E-mail address(es) to send reports
//		Example: $addresses = array('user1@domain1.com', 'user2@domain2.com');
$addresses = array('webmaster@mydomain.com');
$to = implode(', ',$addresses);

//	Set e-mail headers for scanner & reporter
$headers_array = array();

//		Example: $cc = array('CarbonCopy1@domain1.com', 'CarbonCopy2@domain2.com');
$cc_array = array('');
$cc = implode(', ',$cc_array);
if (isset($cc) && !empty($cc)) $headers_array[] = 'CC: ' . $cc;

//		Example: $bcc = array('user1@domain1.com', 'user2@domain2.com');
$bcc_array = array('');
$bcc = implode(', ',$bcc_array);
if (isset($bcc) && !empty($bcc)) $headers_array[] = 'BCC: ' . $bcc;

//	FROM for e-mail example: $from = 'SuperScan_CRON@mydomain.com';
//		- NOT required
$from = 'SuperScan_CRON@mydomain.com';
if (isset($from) && !empty($from)) $headers_array[] = 'FROM: ' . $from;

//	REPLY-TO for e-mail example: $from = 'SuperScan_NoReply@mydomain.com'; - NOT required
$reply = 'SuperScan_NoReply@mydomain.com';
if (isset($reply) && !empty($reply)) $headers_array[] = 'REPLY-TO: ' . $reply;
$headers = (0 < count($headers_array)) ? implode("\r\n",$headers_array) : '';
	
//	Extensions to examine
//	Recommended: An empty array will return ALL extensions; best for real security
//  	Example: $ext_array = array('php', 'html', 'htm', 'js');
//		Do NOT include the dot character!
$ext_array = array();
//	Make extensions lower case for scanner comparison
foreach($ext_array as &$ext) $ext = strtolower($ext);

// 	Extensions to exclude	*** ONLY used if $ext_array is empty ***
//	Example:	$excl_array = array('pdf','zip','doc','docx','xls','xlsx');
//		Do NOT include the dot character
$excl_array = array('pdf','zip','xml','mp3');
//	Make extensions lower case for scanner comparison
foreach($excl_array as &$excl) $excl = strtolower($excl);

//	Directories to ignore

//	Enter comma sepatated quoted directory names to ignore in array
//	RecursiveFilterIterator adapted from code by lemats at
//		http://nz2.php.net/manual/en/class.recursivefilteriterator.php
//	Change 'private','personal' to name the directories to exclude from the scan
class MyRecursiveFilterIterator extends RecursiveFilterIterator
{
	public static $FILTERS = array(
		'private','personal'			//	<= Edit THIS line
	);

	public function accept() 
	{
		return !in_array(
			$this->current()->getFilename(),
			self::$FILTERS,
			true
			);
	}
}

//	$indent for report
$indent = '    ';
$indent2 = $indent . $indent;

//	HANDLE database errors

//	Because the handle_db_error function outputs the database error and
//		these errors tend to be repeated many times, it is recommended
//		that $die = true;
$die = true;

//	Direct scanner to terminate upon database error (TRUE) else continue (FALSE)

if (!function_exists('handle_db_error'))
{
	function handle_db_error( $query_sql, $error, $line )
	{
		//	$handle is the db handle, $scandb
		//	$query is the query which generated the error
		//	$error is the generated error
		//	$line is where the error was generated - for information only
		//	$testing is the same as $testing above, 
		//	$email_out will enable e-mail if TRUE, prevent if FALSE
		//	$die will terminate the script if TRUE or continue if FALSE
		//	__LINE__ is used in scanner and reporter for $line input - 

		global $scandb, $testing, $email_out, $die, $to, $headers;
		if (!empty($error))
		{
			$line = $line - 1;	//	any database error would be on the previous line
			if ($testing) echo 'Database error $error <br />'; 
			if ($email_out) mail($to,'SuperScan Database ERROR', "SuperScan Database ERROR $error using $query_sql at line $line.", $headers);
			if ($die) die();
		}
	}
}

//	Connect to database - $scandb is the returned handle
require_once('scandb.php');

//	END OF CONFIGURE
?>