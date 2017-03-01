<?php
require('configure.php');
$report = "$acct SuperScan v2 Daily Report " . date('Y-m-d') . "\r\n\r\n";
$yesterday = date("Y-m-d H:i:s", mktime(date('H')-25, date('i'), date('s'), date('n'), date('j'),date('Y')));
$report .= "SuperScan log report for $acct file changes since ".$yesterday.":\r\n\r\n";
$scanner_sql = "SELECT `scanned`, `changes`, `iterations`, `count_current`, `elapsed` FROM scanned WHERE `acct` = '$acct' AND `scanned` > '$yesterday' ORDER BY `scanned` DESC";
$scans = mysqli_query($scandb, $scanner_sql);
handle_db_error( $scanner_sql, mysqli_error($scandb), __LINE__ );
if (!$scans) {
	$report .="No scanner log entries available!\r\n ";
} else {
	while ($scan = mysqli_fetch_assoc($scans)) {
		$scan_timestamp = $scan['scanned'];
		$changes = $scan['changes'];
		$iterations = $scan['iterations'];
		$count_current = $scan['count_current'];
		$elapsed = $scan['elapsed'];
		$history_sql = "SELECT `stamp`, `status`, `file_path` FROM history WHERE `acct` = '$acct' AND `stamp` = '$scan_timestamp' ORDER BY `status`, `file_path`";
		$scan_histories = mysqli_query($scandb, $history_sql);
		handle_db_error( $history_sql, mysqli_error($scandb), __LINE__ );
		if (0 < mysqli_num_rows($scan_histories)) {
			while($scan_history = mysqli_fetch_array($scan_histories)) {
				$hist_stamp = $scan_history['stamp'];
				$hist_status= $scan_history['status'];
				$hist_file_path = $scan_history['file_path'];
				$report .= "$hist_stamp => ".strtoupper($scan_history['status'])." => ".$scan_history['file_path']."\r\n"; }
		} else { $report .= "No entry in the history table.\r\n"; }
		$report .= "$changes changes detected in $count_current files ($iterations iterations) in $elapsed seconds.\r\n\r\n"; } }
if ($email_out) mail($to, $acct . ' Integrity Monitor Log Summary Report for' . date('Y-m-d'), $report, $headers);
if ($testing) echo nl2br($report);
mysqli_close($scandb);
?>