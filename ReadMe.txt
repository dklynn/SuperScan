	INTRODUCTION

	This set of scripts is an evolution from what I offered 
	as a SitePoint article and has evolved primarily through 
	the effort of Han Wechgelaer who extended the original 
	"hashscan" to compare and record the changes in new 
	database tables and offered a daily report of changes. 
	
	Kudos and a big THANK YOU to Han for sharing his code 
	and vastly improving the usefulness of the original 
	hashscan code.

	I have further extended Han's code by altering the code 
	logic in the SCAN block and I added the last modified
	logic and time (datetime; to facilitate forensic analysis, 
	if required) as well as adding the account name (for 
	the scan) in the database tables (because I run multiple 
	scans over several accounts and use the same database). 
	I have also changed my earlier recommendations regarding
	file extensions, directory exclusions and CRON settings.
	
	Database accesses are silent but I have included 
	mysqli_error statements should they be required during
	testing. They are controlled by the $testing switch (in 
	the configure.php script) to turn off the reporting 
	during production (CRON) runs.
	
	There are now three database tables: 
	• baseline: To record the file's path and name along 
		with its hash value, last modified date and time 
		and the scan's account name
	• history: to record every change in the baseline table 
		for forensic purposes (except during the first run) and 
	• scanned: to record the scan timestamps, number of changes 
		detected and the account for which the scan was made
	
	Each of these tables are used by the scanner script:
	• baseline to compare with the current file_path and 
		hash value to determine whether the file was changed 
		(added, altered or deleted)
	• history is updated to record any (or no) changes 
		to the baseline table. I have auto-purged the history 
		table of data over 30 days old to prevent growing the 
		table from without bound
	• scanned is simply updated with a summary of the changes 
		and is also auto-purged after 30 days; 
		
	The auto-purge number of days to retain values are only 
	found in the clean-up just before the report output at 
	the end of the scanner script and I've set both to 30 days 
	although 10 days should normally be more than adequate).
	
	The coding assumes that scans will run under CRON every 
	hour and the reporter script will be run under CRON on a 
	daily basis (recommended to be at the start of the 
	webmaster's "business day" - change the CRON statement's 
	hour and minute settings to the GMT equivalent of the 
	start of your "business day").
	
	The reporter script will report changes in the history 
	table over the prior 24 hours. It is recommended that 
	CRON NOT start the reporter script at the same minute 
	as the scanner script (the offered CRON statements 
	suggest a 10 minute delay on the last of the hourly 
	executions).
	
	WORKING ARRAYS
	• baseline is the array of path/filenames, file hashes, 
		last modified date and time and the scan's account name
	• current is the array of CURRENT path/filenames as 
		determined by the directory scan, file hashes and 
		last modified date and time
	• the differences between baseline and current are sorted 
		into arrays (added, altered and deleted) to facilitate 
		baseline and history table updates and report generation.
	• ext_array is the array of file extensions to capture (empty 
		is ALL and is recommended)
	• excl_array is the array of file extensions to exclude 
		(ext must be empty to exclude any file extensions)
	• skip is the array of directories to exclude
	
	I STRONGLY recommend that your SuperScan files be located 
	outside your webspace (at the same or higher level than 
	public_html) to prevent access via a browser. Of course, 
	I expect testing on your test server OR from within your 
	webspace so minor alterations (e.g., SCAN_PATH's define 
	statements) after testing will be necessary.
	
	There is a problem when testing on a Windows server in that 
	the RecursiveDirectoryIterator() and RecursiveIteratorIterator() 
	functions will use BACKslashes which MUST be converted 
	immediately to slashes using str_replace(chr(92), chr(47), $file_path);.
	
	There are many configurable items (in the configure.php 
	script) and this file is well commented so it should be 
	easy to modify to suit your own requirements. You should 
	not have to modify the scanner.php script. SuperScan is 
	NOT copyright but I would ask that these introductory 
	comments remain for posterity.
	
	UPDATE: A reader (Guindillas ... and Micro Update) had 
	identified a problem with 	his $skip array not actually 
	skipping the identified directories. At that time, my 
	understanding of the Iterator was that it could be made 
	to skip. I WAS ***WRONG***!!! SuperScan v 1.3 corrects 
	this by eliminating the $skip array and replacing it with 
	code by lemats at http://nz2.php.net/manual/en/~
	class.recursivefilteriterator.php which extended the 
	RecursiveFilterIterator. My most sincere apologies to 
	Guindillas and Micro Update for pointing out my error 
	as well as to the code suggested by lemats.
	
	To prevent having to reconfigure your script:
	
	configure.php
	Replace lines 30-32
		// 	directories to ignore
		//		An empty array will check all directories
		$skip = array("protected", "private");
	with
		//	Enter comma sepatated quoted directory names to ignore in array 
		//	RecursiveFilterIterator
		//	Based on code by lemats at http://nz2.php.net/manual/en/class.recursivefilteriterator.php
		class MyRecursiveFilterIterator extends RecursiveFilterIterator {

		    public static $FILTERS = array(
		        'private','personal'
		    );

		    public function accept() {
		        return !in_array(
		            $this->current()->getFilename(),
		            self::$FILTERS,
		            true
		        );
		    }
		}
	
	and scanner.php
	Replace lines 78-83
		//	Scan directories and generate hash values for current files
		$dir = new RecursiveDirectoryIterator(SCAN_PATH);
		$iter = new RecursiveIteratorIterator($dir);
		while ($iter->valid())
		{
			// 	Not in Dot AND avoid banned directories
			if (!$iter->isDot() && !(in_array($iter->getSubPath(), $skip)))
	with
		$dir    = new RecursiveDirectoryIterator(SCAN_PATH);
		$filter = new MyRecursiveFilterIterator($dir);
		$iter   = new RecursiveIteratorIterator($filter, 
			RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iter as $filePath => $fileInfo) 
		{
		//	Get or set file extension ('' vs null)
			if (!$iter->isDot() && !is_dir($filePath))

			
	David K. "DK" Lynn
	Data Koncepts
	dk@datakoncepts.com