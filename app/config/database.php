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
			'dsn' => 'mysql:dbname=test;host=localhost;port=3306',
			'username' => 'username',
			'password' => 'password',
			'persistent' => false,
			'charset' => 'utf8',
			'table_prefix' => 'mako_',
		),

		'sqlite' => array
		(
			'dsn' => 'sqlite:/' . MAKO_APPLICATION . '/storage/test.sqlite',
			'charset' => 'UTF-8',
		),	
	),
);

/** -------------------- End of file --------------------**/