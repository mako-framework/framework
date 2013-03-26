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
	 * Enable the query log?
	 */

	'log_queries' => false,
	
	/**
	 * You can define as many database configurations as you want.
	 *
	 * dsn       : PDO data source name
	 * username  : (optional) Username of the database server
	 * password  : (optional) Password of the database server
	 * persistent: (optional) Set to true to make the connection persistent
	 * queries   : (optional) Queries that will be executed right after a connection has been made
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
			'dsn'     => 'sqlite:' . MAKO_APPLICATION_PATH . '/storage/database/test.sqlite',
			'queries' => array
			(
				"PRAGMA encoding = 'UTF-8'",
			),
		),	
	),
);

/** -------------------- End of file --------------------**/