<?php

//---------------------------------------------
// Database configuration
//---------------------------------------------

return array
(
	/**
	* Default configuration to use.
	*/
	
	'default' => 'test',
	
	/**
	* You can define as many database configurations as you want.
	*/
	
	'configurations' => array
	(
		'test' => array
		(
			'dsn'        => 'mysql:dbname=test;host=localhost;port=3306',
			'username'   => 'username',
			'password'   => 'password',
			'persistent' => false,
			'queries'    => array
			(
				"SET NAMES UTF8",
			),
		),

		'sqlite' => array
		(
			'dsn'     => 'sqlite:/' . MAKO_APPLICATION . '/storage/test.sqlite',
			'queries' => array
			(
				"PRAGMA encoding = 'UTF-8'",
			),
		),	
	),
);

/** -------------------- End of file --------------------**/