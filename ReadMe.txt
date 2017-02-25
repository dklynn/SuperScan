	INTRODUCTION

	SuperScan v2.0 is the third iteration of a set of PHP 
	scripts to provide a warning of changed (edited, added 
	or deleted) files to detect a hacker's nefarious work 
	on your website(s). It began with HashScan which simply 
	created a hash of every file within a directory and 
	compared it to the pervious hash values for those files 
	- not very sophisticated.

	SuperScan was the result of comments received and code 
	offered by Han Wechgelaer to extend the usability to 
	include more file comparisons (e.g., last modified) and 
	better summary reporting. Unfortunately, my interpretation 
	of PHP's RecursiveDirectoryIterator and RecursiveIterator-
	Iterator failed to account for my attempt to filter 
	directories (eliminate them from a scan).

	SuperScan v2 is a major update for which I consider 
	Jan Bakke of Norway responsible. Jan found and tested 
	my "obvious" error as well as suggested and coded the 
	additional headers for e-mail and performed many tests to 
	validate  and optimize the code. His suggestions, testing, 
	coding and comments were invaluable - MANY THANKS to 
	Jan for his efforts which made v2 possible.

 	SuperScan v2 corrects the errors of SuperScan by inserting 
	lemats' MyRecursiveFilterIterator to handle directory 
	protection and provided a lot of clean-up of the code 
	as well as the output (HTML for testing and text for 
	e-mail; production use). 
	
	WARNINGS
	
	Although SuperScan v2 has been "optimized" for the average 
	webmaster, there are several things to be aware of:
	
	1.	The SuperScan files are well commented throughout. 
		READ THE COMMENTS before using and set the variables
		in the configure.php script to suit your situation.
	2.	Although SuperScan v2 was designed to be run on an 
		hourly basis via CRON (with a daily summary report), 
		there is code to accommodate testing. The output is 
		controlled by the $testing and $email_out switches 
		set in the configure.php script.
	3.	Be aware that the `file_path` field is the index for 
		the baseline table. That means that it must be UNIQUE 
		(NOT shared across accounts). If not unique, an error 
		will be thrown and the $die variable (set in the 
		configure.php script) will stop the scan after reporting 
		the error.
	4.	Because the `file_path` field type is VARCHAR, its length 
		is limited to 255 characters. Once again, $die will 
		report the error and can stop the scan. Code is available 
		in scanner.php which will report and skip excessive 
		`file_path` lenths but it is commented out because it is a 
		surprising performance hit (~9%).
	5.	The scans require a few seconds for a "normal" website or
		account structure but larger structures and large files may 
		exceed PHP's default execution time of 30 seconds (Jan's
		scan of over 114,504 iterations - files and directories - 
		required just over 2 minutes aand my hashiing of ~9.5Gb of 
		mp3 files among 3500 directories and files required just 
		under one minute). To accomodate large file sets, I have 
		set the max_execution_time to 120 seconds at the top 
		of the scanner.php script. Adjust this if necessary to 
		suit your file structure.
		
	SuperScan v2 UPDATES:
	
	1.	Incorporated the lemats' MyRecursiveFilterIterator to 
		correctly prevent scanning of specified directories
		(at php.net/manual/en/class.recursivefilteriterator.php).
	2.	Added set_time_limit(120); to prevent early script 
		termination for large file sets (scanning a 3600+ 
		file account in under 10½ seconds and over 100,000 
		"files" scanned in about 1½ minutes). Change the 
		120 second limit only if you are scanning a massive 
		number of files or comment it out for less than 
		3,000 files).
	3.	Jan Bakke discovered that the baseline table's 
		`file_path` set to 200 caused a problem with 
		artificially long path-to-file strings. I increased 
		the fields' limits to 255 characters (VARCHAR's max
		length) but, if you exceed this limit, UNcomment 
		lines 90, 102-105 and 202-208 which will count and 
		display those path-to-file strings exceeding 255 
		characters (then ignore them because they will cause 
		database errors). If you need longer string lengths, 
		you may want to try changing the `file_path` field 
		type to TEXT (NOT recommended because Jan's 
		preliminary testing showed "it is a performance 
		killer!!").
	4.	Changed the length of the `file_path` to 255 
		characters in the database fields to prevent 
		database error for exceeding file path length. 
		This can be updated using PHPMyAdmin's Import 
		function with UpdateTablesFor2.0.sql. READ the 
		WARNINGs at the top of the configure script!
	5.	Deleted the $report_out switch because it only 
		duplicated the $testing switch.
	6.	Deleted the $extensionless switch (because you 
		SHOULD scan extensionless files for changes 
		as they are often executed as PHP scripts).
	7.	Removed the ambiguity of localhost testing using 
		127.0.0.1 with the use of a $localtesting switch.
	8.	Allowed the option to add FROM and REPLY-TO headers 
		to e-mail sent by both the scanner and reporter 
		scripts.
	9.	Replaced 'h' (12 hour format) with 'H' (24 hour 
		format) in 8 locations in the scanner script.
	10.	Added reporting of database errors.
	11.	Created the $die switch in configure.php to tell the 
		scanner to abort the scan if a database error is found 
		(it will generally be repeated MANY times); database 
		connection errors WILL terminate the scripts.
	12.	Used the PHP variable, __LINE__ , to identify the line
		number of the mysqli_errors and corrected it to 
		identify the previous line (with the mysqli_query).
	13.	Counted and reported the number of Recursive-
		IteratorIterator (`file_path` iterations) made 
		during the scan as well as the microtime for 
		execution of the scan and file difference handling.
	14.	Replaced "if (not directory and not '.' and not '..')" 
		with "if(is_file())".
	15.	Because all files are liberally commented so you can 
		identify the variables and follow the logic, I have 
		added scanner and reporter scripts without comments or 
		testing output in a production subdirectory of the ZIP 
		file. Simply upload these two files to replace the 
		commented scanner and reporter scripts (all 
		configuration is done in the configure script).
	16.	Added the scan.php script in `online` to run SuperScan 
		from a browser when the files are NOT located in 
		public_html (they should NEVER be located within 
		public_html but, if you don't have access to a higher 
		level directory, be sure to password protect their 
		directory!).
	17.	Moved the database connection for the scanner and 
		reporter scripts to the end of the configure script.
	18.	Other than extending the time limit for execution (for 
		very large scans) and eliminating 'negative reports' 
		(which are meant to provide a "warm, fuzzy feeling that 
		all is okay") at line 287 (" && 0 < $count_changes"), 
		I have made included all the configuration necessary 
		in the configure script. It should NOT be necessary to 
		change either the scanner or reporter scripts except 
		for future updates.
	19.	I nearly deleted the `acct` fields from the database
		records (and database queries) but left them in case
		someone is using the same database with multiple 
		accounts (but NOT scanning the same files - which 
		would violate the UNIQUE key of the baseline table). 
		Feel free to make this modification on your own.
	20.	I was unhappy with the format of the reporter script 
		(and so impressed by Jan Bakke's testing) that I added 
		fields to the scanned table to record the iterations, 
		files captured (by the scan) and elapsed time; these are 
		provided for each scan in the daily report.
	21.	As part of the database clean-up, I also specified the 
		`hash_org` and `hash_new` fields as CHAR(40) for a 
		marginal speed improvement (because they use a fixed 
		hash length).

	DATABASE

	There are three database tables (CreateTables.sql will 
	create them for you if you import using PHPMyAdmin or UPDATE 
	using the two lines of code in UpdateTablesFor2.0.sql): 
	• baseline: To record the file's path and name along 
		with its hash value, last modified date and time 
		and the scan's account name. The file's path and name 
		are limited to 255 characters (VARCHAR fields) and MUST 
		be unique because they are used as the table's index)
	• history: to record every change in the baseline table 
		(except during the first run) and 
	• scanned: to record the scan timestamps, number of changes 
		detected and the account for which the scan was made
	
	Each of these tables are used by the scanner script:
	• baseline to compare with the current `file_path` and 
		hash value to determine whether the file was changed 
		(added, altered or deleted)
	• history is updated to record any (or no) changes to the 
		baseline table. I have auto-purged the history table 
		of data over 30 days old to prevent growing the table 
		from without bound
	• scanned is simply updated with a summary of the changes 
		and is also auto-purged after 30 days
		
	The auto-purge number of days to retain values are found 
	in the clean-up just before the output at the end of the 
	scanner script and I have set both to 30 days although 
	10 days should normally be more than adequate - change the 
	number of days to save on the scanner.php script's lines 275 
	and 279).
	
	If queries result in database errors, they are now presented
	during testing (via HTML) or production (run by CRON which 
	sends e-mail with the error statements). Output is controlled 
	by the $testing and $email_out switches (in the configure 
	script) to turn off the reporting during production (CRON) 
	scans.
	
	SCANS

	Create your database and add your username and password 
	with cPanel (update scandb.php with that information) 
	then Import your database tables using PHPMyAdmin using 
	CreateTables.sql and create your CRON jobs using the code 
	in CRON.txt. Set the variables in configure.php to suit 
	your file structure and testing status then upload the 
	four scripts: configure.php, reporter.php, scandb.php and 
	scanner.php to your directory of choice (I recommend 
	SuperScan at the same level as public_html).
	
	The first scan will require more time than any other scan
	because it writes every file path record to the database.
	Subsequent scans will be MUCH faster!
	
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
	  last modified date and time
	• current is the array of CURRENT path/filenames as 
	  determined by the directory scan, file hashes and 
	  last modified date and time
	• the differences between baseline and current are sorted 
	  into arrays (added, altered and deleted) to facilitate 
	  baseline and history table updates and report generation.
	• the ext_array is the array of file extensions to capture 
	  (empty is ALL and is recommended; do NOT include the dot 
	  character)
	• the excl_array is the array of file extensions to exclude 
	  (ext_array must be empty to exclude any file extensions; 
	  do NOT include the dot character)
	• directories to be excluded by the scan are set in a 
	  comma-separated list near the end of the configure script 
	  in the public static $FILTERS array declaration
	  
	SCAN FLOW
	
	After initialization, the baseline is read from the database 
	so it can be compared with the current file set during the 
	scan's iteration through the directories. Added or altered 
	files are added to the added or altered arrays while the 
	deleted files are determined by matching the current and 
	baseline arrays. The report to be output is built during 
	the scan and sorting of files to the appropriate arrays.
	
	The reporter script simply reads the last 24 hours of records 
	INSERTed into the scanned table and reports them via HTML 
	(during testing) or e-mail (when run by CRON).
	
	COMMENTS
	
	I STRONGLY recommend that your SuperScan files be located 
	outside your webspace (at the same or higher level than 
	public_html) to prevent access via a browser. Of course, 
	I expect testing on your test server OR from within your 
	webspace so minor alterations (e.g., SCAN_PATH's define 
	statements) after testing may be necessary. I use a 
	directory at the level of public_html and upload all three 
	script files there as well as scandb (the database connection 
	script) which effects the connection to the MySQL database. 
	For testing, I use the scan script in my webspace which is 
	also included in case you need an example.
	
	There is a problem when testing on a Windows OS in that the 
	RecursiveDirectoryIterator() and RecursiveIteratorIterator() 
	functions will create BACKslashes which MUST be converted 
	immediately to slashes ( str_replace(chr(92), chr(47), 
	$file_path); ).
	
	There are many configurable items (all of which are in the 
	configure.php script). You should not have to modify the 
	scanner.php script (unless there is a PHP max_executible_time 
	ERROR) OR reporter.php script. 
	
	Finally SuperScan (including SuperScan v2) is NOT copyright 
	but I would ask that these introductory explanations be retained.
		
	Should you have any suggestions for further improvement, 
	please e-mail them to me.

			
	David K. "DK" Lynn
	Data Koncepts
	DK@DataKoncepts.com