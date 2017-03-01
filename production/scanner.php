<?php
//	set_time_limit(60);
require('configure.php');
$report = "\r\nSuperScan v2 File Check for " . $acct . "\r\n\r\n";
$baseline = $current = array();
$added = $altered = $deleted = array();	
$last_scanned_records = mysqli_query($scandb, "SELECT `scanned` FROM scanned WHERE `acct` = '$acct' ORDER BY `scanned` DESC LIMIT 1");
if ($last_scanned_records && 0 < mysqli_num_rows($last_scanned_records))
{	while($last_datetime = mysqli_fetch_assoc($last_scanned_records))
	{	$last_scanned = $last_datetime['scanned']; $firstscan = false; }
} else {	$firstscan = true; $count_baseline = 0; }
$time = date('Y-m-d H:i:s');	
$start = microtime(true);
$baseline_sql = "SELECT `file_path`, `file_hash`, `file_last_mod` FROM baseline WHERE `acct` = '$acct' ORDER BY `file_path` ASC";
$baseline_results = mysqli_query($scandb,$baseline_sql);
handle_db_error( $baseline_sql, mysqli_error($scandb), __LINE__ );
if ($baseline_results)
{	while ($baseline_files = mysqli_fetch_assoc($baseline_results))
	{	$baseline[$baseline_files['file_path']] = array(
			'file_hash' => $baseline_files['file_hash'],
			'file_last_mod' => $baseline_files['file_last_mod']); }
	$count_baseline = count($baseline);
	if (0 == $count_baseline) 
	{	if (!$firstscan)	//	Check for database hack by checking $firstscan
			$report .= "Empty baseline table!\r\nPROBABLE HACK ATTACK\r\n(ALL files are missing/deleted)!\r\n\r\n"; }
	$report .= "$count_baseline baseline files extracted from database.\r\n"; }
$dir    = new RecursiveDirectoryIterator(SCAN_PATH);
$filter = new MyRecursiveFilterIterator($dir);
$iter   = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);
$iterations = 0;
//	$length_error = array();
foreach ($iter as $filePath => $fileInfo)
{	$iterations++;
	/*	if (255 < strlen($filePath)) {
		$length_error[] = $filePath;
			continue; }	*/
	$file_path = $filePath;
	$file_path = str_replace(chr(92),chr(47),$file_path);
	if (is_file($file_path)) {
		$ext = strtolower(substr($file_path,strrpos($file_path,'.')+1));
		if ((empty($ext_array) && !in_array($ext,$excl_array)) || in_array($ext,$ext_array)) {
			$current[$file_path] = array('file_hash' => hash_file("sha1", $file_path), 'file_last_mod' => date("Y-m-d H:i:s", filemtime($file_path)));
			if (!array_key_exists($file_path, $baseline)) {
				$added[$file_path] = array('file_hash' => $current[$file_path]['file_hash'], 'file_last_mod' => $current[$file_path]['file_last_mod']);
				$added_baseline_sql = "INSERT INTO baseline SET `file_path` = '$file_path', `file_hash` = '" . $added[$file_path]['file_hash'] . "', `file_last_mod` = '" . $added[$file_path]['file_last_mod'] . "', `acct` = '$acct'";
				mysqli_query($scandb, $added_baseline_sql);
				handle_db_error( $added_baseline_sql, mysqli_error($scandb), __LINE__ );
				if(!$firstscan) {
					$added_history_sql = "INSERT INTO history SET `stamp` = '$time', `status` = 'Added', `file_path` = '$file_path', `hash_org` = 'Not Applicable', `hash_new` = '" . $added[$file_path]['file_hash'] . "', `file_last_mod` = '" . $added[$file_path]['file_last_mod'] . "', `acct` = '$acct'";
					mysqli_query($scandb, $added_history_sql);
					handle_db_error( $added_history_sql, mysqli_error($scandb), __LINE__ );
				} else {
 					/*	$added_firstscan_history_sql = "INSERT INTO history SET `stamp` = '$time', `status` = 'FIRST SCAN', `file_path` = '$file_path', `hash_org` = 'Not Applicable', `hash_new` = '" . $added[$file_path]['file_hash'] . "', `file_last_mod` = '" . $added[$file_path]['file_last_mod'] . "', `acct` = '$acct'";
					mysqli_query($scandb, $added_firstscan_history_sql);
					handle_db_error( $added_firstscan_history_sql, mysqli_error($scandb), __LINE__ ); */
				}
			} else {	
				if ($baseline[$file_path]['file_hash'] <> $current[$file_path]['file_hash'] || $baseline[$file_path]['file_last_mod'] <> $current[$file_path]['file_last_mod']) {
					$altered[$file_path] = array('hash_org' => $baseline[$file_path]['file_hash'], 'hash_new' => $current[$file_path]['file_hash'], 'file_last_mod' => $current[$file_path]['file_last_mod']);
					$altered_baseline_sql = "UPDATE baseline SET `file_hash` = '" . $altered[$file_path]['hash_new'] . "', `file_last_mod` = '" . $altered[$file_path]['file_last_mod'] . "' WHERE `file_path` = '$file_path' AND `acct` = '$acct'";
					mysqli_query($scandb, $altered_baseline_sql);
					handle_db_error( $altered_baseline_sql, mysqli_error($scandb), __LINE__ );
					$altered_history_sql = "INSERT INTO history SET `stamp` = '$time', `status` = 'Altered', `file_path` = '$file_path', `hash_org` = '" . $altered[$file_path]['hash_org'] . "', `hash_new` = '" . $altered[$file_path]['hash_new'] . "', `file_last_mod` = '" . $altered[$file_path]['file_last_mod'] . "', `acct` = '$acct'";
					mysqli_query($scandb, $altered_history_sql);
					handle_db_error( $altered_history_sql, mysqli_error($scandb), __LINE__ ); }
			}
		}
	}
}
$deleted = array_diff_key($baseline, $current);
foreach($deleted as $key => $value) {
	$delete_baseline_sql = "DELETE FROM baseline WHERE `file_path` = '$key' LIMIT 1";
	mysqli_query($scandb, $delete_baseline_sql);
	handle_db_error( $delete_baseline_sql, mysqli_error($scandb), __LINE__ );
	$delete_history_sql = "INSERT INTO history SET `stamp` = '$time', `status` = 'Deleted', `file_path` = '$key', `hash_org` = '" . $deleted[$key]['file_hash'] . "', `hash_new` = 'Not Applicable', `file_last_mod` = '" . $deleted[$key]['file_last_mod'] . "', `acct` = '$acct'";
	mysqli_query($scandb, $delete_history_sql);
	handle_db_error( $delete_history_sql, mysqli_error($scandb), __LINE__ ); }

$elapsed_iterations = round(microtime(true) - $start, 4);
$elapsed = number_format($elapsed_iterations, 4, '.', '');
/*if (!empty($length_error)) {
	$report .= "\r\nWARNING: path-to-file string length exceeded (" . count($length_error) . " times).\r\nThe following files were NOT processed:\r\n";
	foreach($length_error as $path_error) {
		$report .= "$indent XXXX $path_error\r\n"; }
	$report .= "\r\n"; } */
$count_current = count($current);
$report .= "$iterations file & directory iterations examined.\r\n";
if (0 == $count_current) $report .= "\r\nThere are NO files in the specified location\r\n(" . SCAN_PATH . ").\r\n";
$count_added = count($added);
$report .= "$indent $count_added files ADDED to baseline.\r\n";
if (!$firstscan) foreach($added as $filename => $value) $report .= "$indent2 + " . substr($filename,$scan_path_length) . "\r\n";
$count_altered = count($altered);
$report .= "$indent $count_altered ALTERED files updated.\r\n";
foreach($altered as $filename => $value) $report .= "$indent2 " . chr(177) . " " . substr($filename,$scan_path_length) . "\r\n";
$count_deleted = count($deleted);
$report .= "$indent $count_deleted files DELETED from baseline.\r\n";
foreach($deleted as $filename => $value) $report .= "$indent2 - " . substr($filename,$scan_path_length) . "\r\n";
echo "\r\n";
$count_changes = $count_added + $count_altered + $count_deleted;
if (0 == $count_changes) {
	$path = "File structure is unchanged since last scan.";
	$update_history_table_sql = "INSERT INTO history SET `stamp` = '$time', `status` = 'Unchanged', `file_path` = '$path', `hash_org` = 'Not Applicable', `hash_new` = 'Not Applicable', `file_last_mod` = 'Not Applicable', `acct` = '$acct'";
	mysqli_query($scandb, $update_history_table_sql);
	handle_db_error( $update_history_table_sql, mysqli_error($scandb), __LINE__ );
	$update_scanned_table_sql = "INSERT INTO scanned SET `scanned` = '$time', `changes` = $count_changes, `iterations` = $iterations, `count_current` = $count_current, `elapsed` = '$elapsed', `acct` = '$acct'";
	mysqli_query($scandb, $update_scanned_table_sql);
	handle_db_error( $update_scanned_table_sql, mysqli_error($scandb), __LINE__ );
	$report .= "File structure is unchanged since last scan.";
} else {
	$update_scanned_table_sql = "INSERT INTO scanned SET `scanned` = '$time', `changes` = $count_changes, `iterations` = $iterations, `count_current` = $count_current, `elapsed` = '$elapsed', `acct` = '$acct'";
	mysqli_query($scandb, $update_scanned_table_sql);
	handle_db_error( $update_scanned_table_sql, mysqli_error($scandb), __LINE__ );

	$report .= "\r\nSummary:\r\n
Files & Directories (iterations) Examined: $iterations\r\n
Baseline start: $count_baseline
Current Baseline: $count_current
Changes to baseline: $count_changes\r\n
$indent Added: $count_added
$indent Altered: $count_altered
$indent Deleted: $count_deleted\r\n
Scan executed in $elapsed seconds\r\n\r\n
If you did not makes these changes, examine your files closely\r\n
for evidence of embedded hacker code or added hacker files.\r\n
(WinMerge provides excellent comparisons between your\r\n
master file and a downloaded file.)"; }

$clean_history_sql = "DELETE FROM history WHERE `stamp` < DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($scandb, $clean_history_sql);
handle_db_error( $clean_history_sql, mysqli_error($scandb), __LINE__ );
$clean_scanned_sql = "DELETE FROM scanned WHERE `scanned` < DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($scandb, $clean_scanned_sql);
handle_db_error( $clean_scanned_sql, mysqli_error($scandb), __LINE__ );
if ($email_out && 0 < $count_changes) mail($to, 'SuperScan Report for ' . $acct, $report, $headers);
if ($testing) echo str_replace(array("\r\n", "\r", "\n"), "<br />", $report);
$baseline = $current = $added = $altered = $deleted = array();
mysqli_close($scandb);
?>