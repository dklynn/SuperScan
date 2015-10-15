<?php

//	INTRO
//
//	Introductory comments are provided in the ReadMe.txt file
//
//	End of Intro

//	CONFIGURE account for scan
require('configure_DK.php');

//	INITIALIZE
//	Initialization of scanner variables and tables

//	Connect to database - $scandb is the returned handle
require('scandb.php');

//	Clear and title the report variable before starting
$report = "SuperScan File Check for $acct\r\n\r\n";

//	Initialize the baseline array for the baseline table
$baseline = array();

//	Initialize the current array for the current file scan
$current = array();

//	Intitialize the differences arrays 
$added = array();
$altered = array();
$deleted = array();	

//	Limit first scan entries in history table

//	Get date and time of last scan for report
$last_scanned_records = @mysqli_query($scandb, "SELECT `scanned` FROM scanned WHERE `acct` = '$acct' ORDER BY `scanned` DESC LIMIT 1");
if ($last_scanned_records && 0 < mysqli_num_rows($last_scanned_records))
{
	//	Get last timestamp
	while($last_datetime = @mysqli_fetch_assoc($last_scanned_records))
	{
		$last_scanned = $last_datetime['scanned'];
		$firstscan = false;
	}
} else {
	$firstscan = true;
	$count_baseline = 0;
}

//	Start timer (scan duration)
$start = microtime(true);

//	END OF INITIALIZE


//	BASELINE
// 	Read from database to obtain file paths, hash values and 
//		last modified dates to compare against current files

$baseline_results = @mysqli_query($scandb,"SELECT `file_path`, `file_hash`, `file_last_mod` FROM baseline WHERE `acct` = '$acct' ORDER BY `file_path` ASC");

if ($baseline_results) 
{
	while ($baseline_files = @mysqli_fetch_assoc($baseline_results))
	{
		$baseline[$baseline_files['file_path']] = array(
			'file_hash' => $baseline_files['file_hash'],
			'file_last_mod' => $baseline_files['file_last_mod']);
	}

	//	Output baseline array for testing
	/* if ($testing)
	{
		echo "<h2>SuperScan Test</h2>";
		echo "<pre>Baseline array (database files):<br />";
		print_r($baseline);
		echo "</pre><br />";
	} */

	//	Get the count of baseline records
	$count_baseline = count($baseline);

	if (0 == $count_baseline) 
	//	Prior scanned results but empty baseline table
	{
		//	Check for database hack by checking $firstscan
		if (!$firstscan)
		{
			$report .= "Empty baseline table!\r\nPROBABLE HACK ATTACK\r\n(ALL files are missing/deleted)!\r\n\r\n";	
		}
	}
	
	$report .= "$count_baseline baseline files extracted from database.\r\n";
	
	//	Output number of baseline files for testing
	if ($testing) echo "<p>$count_baseline baseline files extracted from database.</p>";
}
//	Baseline files read into baseline array and baseline_count made


//	SCAN

//	Scan directories and generate hash values for current files
$dir = new RecursiveDirectoryIterator(SCAN_PATH);
$iter = new RecursiveIteratorIterator($dir);
while ($iter->valid())
{
	// 	Not in Dot AND not in $skip (prohibited) directories
	if (!$iter->isDot() && !(in_array($iter->getSubPath(), $skip)))
	{
		//	Get or set file extension ('' vs null)
		if (is_null(pathinfo($iter->key(), PATHINFO_EXTENSION)))
		{
			$ext = '';
		} else {
			$ext = strtolower(pathinfo($iter->key(), PATHINFO_EXTENSION));
		}

		//	Check for allowed file extension OR
		//	$ext empty AND not excluded ext OR
		//	is not $extensionless (if prohibited)
		//	if ((!empty($ext_array)) || (empty($ext_array) && !in_array($ext, $excl_array, true)))
		if (
			(in_array($ext, $ext_array, true)) ||	
			// in allowed extension array
			(empty($ext_array) && !in_array($ext, $excl_array, true)) ||	
			// OR NOT in excluded extension array
			(empty($ext) && $extensionless) )	
			// OR extensionless AND extensionless is allowed
		{
			$file_path = $iter->key();
			//	Ensure $file_path without \'s
			$file_path = str_replace(chr(92),chr(47),$file_path);
			
			//	Handle addition to $current array
			$current[$file_path] = array('file_hash' => hash_file("sha1", $file_path), 'file_last_mod' => date("Y-m-d H:i:s", filemtime($file_path)));

			//	IF file_path is not in baseline, file was ADDED
			if (!array_key_exists($file_path, $baseline))
			{
				$added[$file_path] = array('file_hash' => $current[$file_path]['file_hash'], 'file_last_mod' => $current[$file_path]['file_last_mod']);
			
				//	INSERT added record in baseline table
				@mysqli_query($scandb, "INSERT INTO baseline SET `file_path` = '$file_path', `file_hash` = '" . $added[$file_path]['file_hash'] . "', `file_last_mod` = '" . $added[$file_path]['file_last_mod'] . "', `acct` = '$acct'");
				if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);

				//	INSERT added file record in history table
				//		EXCEPT if $firstscan (to prevent unnecessary records)
				if(!$firstscan) 
				{
					@mysqli_query($scandb, "INSERT INTO history SET `stamp` = '" . date('Y-m-d h:i:s') . "', `status` = 'Added', `file_path` = '$file_path', `hash_org` = 'Not Applicable', `hash_new` = '" . $added[$file_path]['file_hash'] . "', `file_last_mod` = '" . $added[$file_path]['file_last_mod'] . "', `acct` = '$acct'");
					if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);
				}  else {
					//	First Scan entry into history table
 					@mysqli_query($scandb, "INSERT INTO history SET `stamp` = '" . date('Y-m-d h:i:s') . "', `status` = 'Added', `file_path` = 'FIRST SCAN (file listings inhibited)', `hash_org` = 'Not Applicable', `hash_new` = 'Not Applicable', `file_last_mod` = 'Not Applicable', `acct` = '$acct'");
					if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);
				}	//	End of handling $added array entry

			} else {

				//	IF file was ALTERED 
				if ($baseline[$file_path]['file_hash'] <> $current[$file_path]['file_hash'] || $baseline[$file_path]['file_last_mod'] <> $current[$file_path]['file_last_mod'])
				{
					$altered[$file_path] = array('hash_org' => $baseline[$file_path]['file_hash'], 'hash_new' => $current[$file_path]['file_hash'], 'file_last_mod' => $current[$file_path]['file_last_mod']);
				
					//	UPDATE altered record in baseline
					@mysqli_query($scandb,"UPDATE baseline SET `file_hash` = '" . $altered[$file_path]['hash_new'] . "', `file_last_mod` = '" . $altered[$file_path]['file_last_mod'] . "' WHERE `file_path` = '$file_path' AND `acct` = '$acct'");
					if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);

					//	INSERT altered file info in history table
					@mysqli_query($scandb,"INSERT INTO history SET `stamp` = '" . date('Y-m-d h:i:s') . "', `status` = 'Altered', `file_path` = '$file_path', `hash_org` = '" . $altered[$file_path]['hash_org'] . "', `hash_new` = '" . $altered[$file_path]['hash_new'] . "', `file_last_mod` = '" . $altered[$file_path]['file_last_mod'] . "', `acct` = '$acct'");
					if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);
				}
			}
		}	//	End of handling $altered array entry
	}	// End of handling $current record entry
	$iter->next();
}

//	DELETED
//	Compare and generate $deleted array
//	$deleted contains records where file_path 
//		in $baseline but not in $current
$deleted = array_diff_key($baseline, $current);
//	Next line necessary for Windows
$deleted = str_replace(chr(92),chr(47),$deleted);

foreach($deleted as $key => $value)
{
	//	Handle DELETEd file
	//	DELETE file from baseline table
	mysqli_query($scandb,"DELETE FROM baseline WHERE `file_path` = '$key' LIMIT 1");
	if ($testing && mysqli_error($scandb)) 
	{
		echo mysqli_error($scandb);
	} else {
		if ($testing) echo "<p>Deleted " . $key . "'s baseline record.</p>";
	}

	//	Record deletion in history table
	@mysqli_query($scandb, "INSERT INTO history SET `stamp` = '" . date('Y-m-d h:i:s') . "', `status` = 'Deleted', `file_path` = '$key', `hash_org` = '" . $deleted[$key]['file_hash'] . "', `hash_new` = 'Not Applicable', `file_last_mod` = '" . $deleted[$key]['file_last_mod'] . "', `acct` = '$acct'");
	if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);
}
//	End of Deleted file handling


//	PREPARE Report 
	
//	Get scan duration
$elapsed = round(microtime(true) - $start, 5);
	
//	Add count summary to report
$count_current = count($current);
$report .= "$count_current files collected in scan.\r\n";
if (0 == $count_current)
{
	//	ALL files are gone!
	$report .= "\r\nThere are NO files in the specified location.\r\n";
	if (!$firstscan) $report .= "This indicates a possible HACK ATTACK\r\nOR an incorrect path to the account's files\r\n";
}

$count_added = count($added);
$report .= "$indent $count_added files ADDED to baseline.\r\n";
if (!$firstscan)
{
	foreach($added as $filename => $value) $report .= "$indent2 + " . substr($filename,$scan_path_length) . "\r\n";
}

$count_altered = count($altered);
$report .= "$indent $count_altered ALTERED files updated.\r\n";
foreach($altered as $filename => $value) $report .= "$indent2 " . chr(177) . " " . substr($filename,$scan_path_length) . "\r\n";

$count_deleted = count($deleted);
$report .= "$indent $count_deleted files DELETED from baseline.\r\n";
foreach($deleted as $filename => $value) $report .= "$indent2 - " . substr($filename,$scan_path_length) . "\r\n";

echo "\r\n";

$count_changes = $count_added + $count_altered + $count_deleted;
	
//	Completed update of history table for Unchanged

if (0 == $count_changes)
{  
    $path = "File structure is unchanged since last scan, script execution time $elapsed seconds.<br>The baseline contains $count_current files.\r\n";

	//	Update history table
	@mysqli_query($scandb,"INSERT INTO history SET `stamp` = '" . date('Y-m-d h:i:s') . "', `status` = 'Unchanged', `file_path` = '$path', `hash_org` = 'Not Applicable', `hash_new` = 'Not Applicable', `file_last_mod` = 'Not Applicable', `acct` = '$acct'");
	if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);

	// update scanned table
	@mysqli_query($scandb,"INSERT INTO scanned SET `scanned` = '" . date('Y-m-d h:i:s') . "', `changes` = '$count_changes', `acct` = '$acct'");  
	if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);

	$report .= "File structure is unchanged since last scan.\r\n\r\nThe baseline now contains $count_current files.\r\n\r\nScan executed in $elapsed seconds.";
	
} else {
	
	@mysqli_query($scandb,"INSERT INTO scanned SET `scanned` = '" . date('Y-m-d h:i:s') . "', `changes` = '$count_changes', `acct` = '$acct'");  
	if ($testing && mysqli_error($scandb)) echo mysqli_error($scandb);

	$report .= "\r\n\r\nSummary:\r\n
Baseline start: $count_baseline
Current Baseline: $count_current
Changes to baseline: $count_changes\r\n
$indent Added: $count_added
$indent Altered: $count_altered
$indent Deleted: $count_deleted.\r\n
Scan executed in $elapsed seconds.";
	if (0 < $count_changes) $report .= "\r\n\r\nIf you did not makes these changes, examine your files closely\r\nfor evidence of embedded hacker code or added hacker files.\r\n(WinMerge provides excellent comparisons)";
}

//	Clean-up history table and scanned table by deleting entries over 30 days old
@mysqli_query($scandb,"DELETE FROM history WHERE `stamp` < DATE_SUB(NOW(), INTERVAL 30 DAY)");
if ($testing && mysqli_error($scandb)) echo "History table clean-up problem: " . mysqli_error($scandb) . "<br />";

@mysqli_query($scandb,"DELETE FROM scanned WHERE `scanned` < DATE_SUB(NOW(), INTERVAL 30 DAY)");
if ($testing && mysqli_error($scandb)) echo "Scanned table clean-up problem: " . mysqli_error($scandb) . "<br />";

//	End of Report preparation and clean-up


//	OUTPUT Report
//	E-mail Report
if ($email_out && 0 < $count_changes)
{
	if (count($addresses)>1)
	{
		$to = implode(", ", $addresses); 
	} else {
		$to = $addresses[0];
	}
	mail($to, "SuperScan Report for $acct",str_replace('&nbsp;',' ',$report)); 
}

//	Output Report for testing
if ($testing || $report_out)
{
	echo str_replace(array("\r\n", "\r", "\n"), "<br />", $report); 
}

//	Destroy tables (release to memory)
$baseline = $current = $added = $altered = $deleted = array();

//	Close database
@mysqli_close($scandb);

?>
