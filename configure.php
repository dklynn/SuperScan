<?php
//	Introductory comments are provided in the README.md file

//	Account is only used for reports and SELECT for the baseline table
$acct = 'Account Name';

$localtesting = false;	//	Set TRUE for testing

//	Set SCAN_PATH and SCANNER_PATH - use a trailing / for both!

if ($localtesting)
{
	define('SCAN_PATH', 'X:/path/to/directory/');
	define('SCANNER_PATH', 'X:/path/to/SuperScan/');
} else {
	define('SCAN_PATH', '/home/account/public_html/');
	define('SCANNER_PATH', '/home/account/SuperScan/');
}

$scan_path_length = strlen(SCAN_PATH);	//	Used for reports

$testing = false;	//	Leave FALSE for production use

//	SET Report Output

$email_out = true;	//	Set false for testing

//	Set e-mail and headers for scanner & reporter
$addresses = array('user1@example.com', 'user2@example.com');
$to = implode(', ',$addresses);

$headers_array = array();
//	Uncomment and edit as required
//	$headers_array[] = 'From: SuperScan CRON <superscan.cron@example.com>';
//	$headers_array[] = 'Reply-To: SuperScan NoReply <SuperScan_NoReply@example.com>';
//	$headers_array[] = 'Cc: User3 <user3@example.com>, User4 <user4@example.com>';
//	$headers_array[] = 'Bcc: Webmaster <webmaster@example.com>, Client <Client@example.com>';
$headers = implode("\r\n",$headers_array);
	
//	Extensions to examine - An empty array will return ALL extensions
//		Do NOT include the dot character!
$ext_array = array('php', 'html', 'htm', 'js');
foreach($ext_array as &$ext) $ext = strtolower($ext);	//	lower case required by scanner

// 	Extensions to exclude *** IF *** $ext_array is empty
//		Do NOT include the dot character
$excl_array = array('pdf','zip','xml','mp3');
foreach($excl_array as &$excl) $excl = strtolower($excl);	//	lower case required by scanner

//	Directories to ignore
//	RecursiveFilterIterator adapted from code by lemats at
//		http://nz2.php.net/manual/en/class.recursivefilteriterator.php
class MyRecursiveFilterIterator extends RecursiveFilterIterator
{
	public static $FILTERS = array(
		'private','personal'	//	<= Edit THIS line
	);
	public function accept() 
	{
		return !in_array(
			$this->current()->getFilename(),
			self::$FILTERS,
			true);
	}
}

$indent = '    '; $indent2 = $indent . $indent;	//	$indent for reports

//	HANDLE database errors
$die = true;	//	Terminate scanner after reporting db error
function handle_db_error( $query_sql, $error, $line )
{
	global $scandb, $testing, $email_out, $die, $to, $headers, $acct;
	if (!empty($error)) {
		$line = $line - 1;	//	db error on the previous line
		if ($testing) echo "Database error $error <br />"; 
		if ($email_out) mail($to,"SuperScan Database ERROR for $acct", "SuperScan Database ERROR\r\n$error\r\nusing $query_sql\r\nat line $line.", $headers);
		if ($die) die();
	}
}

//	Connect to database - $scandb is the returned handle
require_once('scandb.php');

//	END OF CONFIGURE
?>