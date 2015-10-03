<?php

/* Store this file outside your webspace if possible */

	define('SERVER','localhost');
	define('USER','User_Name');
	define('PASS','Password for User_Name');
	define('DATABASE','User_hashscan');
	
	$scandb = mysqli_connect(SERVER,USER,PASS,DATABASE);
?>
