<?php

/* Store this file outside your webspace if possible */

	define('SERVER','localhost');
	define('USER','User_Name');
	define('PASS','Password for User_Name');
	define('DATABASE','User_hashscan');

	$scandb = mysqli_connect(SERVER,USER,PASS,DATABASE);

	if (!$scandb)
	{
		$query_sql = "Connection parameters";
		$line = "scandb.php line 10.";
		$error = mysqli_connect_error($scandb);
		if ($testing) echo "Database connection error $error <br />";
		if ($email_out) mail($to,'SuperScan Database Connection ERROR',"SuperScan Database Connection ERROR $error using $query_sql at $line.", $headers);
		die();
	}
?>