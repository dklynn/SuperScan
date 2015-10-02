# SuperScan
Detect changed files using PHP/MySQL/CRON

INTRODUCTION

This set of scripts is an evolution from what I offered as a SitePoint article and has evolved primarily through the suggestion of Han Wechgelaer who suggested that I extend the original "hashscan" to compare and record the changes in new database tables and offered a daily report of changes.
	
Kudos and a big THANK YOU to Han for sharing his coding start and vastly improving the usefulness of the original hashscan code.

I have further extended Han's code by altering the code logic in the Compare block and I added the last modifiedlogic and time (datetime; to facilitate forensic analysis, if required) as well as adding the account name (for the scan) in the database tables (because I run multiple scans over several accounts and use the same database). I have also changed my earlier recommendations regarding file extensions, directory exclusions and CRON settings.
	
Database accesses are silent but I have included mysqli_error statements should they be required. They are controlled by the $testing switch (in the configure.php script) to turn off the reporting during production (CRON) runs.
	
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
		
The auto-purge number of days to retain values are only found in the clean-up just before the report output at the end of the scanner script and I've set both to 30 days although 10 days should normally be more than adequate).
	
The coding assumes that scans will run under CRON every hour and the reporter script will be run under CRON on a daily basis (recommended to be at the start of the webmaster's "business day" - change the CRON statement's hour and minute settings to the GMT equivalent of the start of your "business day").
	
The reporter script will report changes in the history table over the prior 24 hours. It is recommended that CRON NOT start the reporter script at the same minute as the scanner script (the offered CRON statements suggest a 10 minute delay on the last of the hourly executions).
	
WORKING ARRAYS
• baseline is the array of path/filenames, file hashes, last modified date and time and the scan's account name
• current is the array of CURRENT path/filenames as determined by the directory scan, file hashes and last modified date and time
• the differences between baseline and current are sorted into arrays (added, altered and deleted) to facilitate baseline and history table updates and report generation.
• ext is the array of file extensions to capture (empty is ALL and is recommended)
• excl_ext is the array of file extensions to exclude (ext must be empty to exclude any file extensions)
• skip is the array of directories to exclude
	
I STRONGLY recommend that your SuperScan files be located outside your webspace (at the same or higher level than public_html) to prevent access via a browser. Of course, I expect testing on your test server OR from within your webspace so minor alterations (e.g., SCAN_PATH's define statements) after testing will be necessary.
	
There is a problem when testing on a Windows server in that the RecursiveDirectoryIterator() and RecursiveIteratorIterator() functions will use BACKslashes which MUST be converted immediately to slashes using str_replace(chr(92), chr(47), $file_path);.
	
There are many configurable items (in the configure.php script) and this file is well commented so it should be easy to modify to suit your own requirements. You should not have to modify the scanner.php script. 

SuperScan is NOT copyright but I would ask that these introductory comments remain for posterity.
	
David K. "DK" Lynn
