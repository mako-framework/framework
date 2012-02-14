<?php

//---------------------------------------------
// Core configuration
//---------------------------------------------

return array
(
	/**
	* Base url of your application (without trailing slash).
	*/
	
	'base_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/mako',
	
	/**
	* Set to true to hide "index.php" from your urls (this requires mod_rewrite).
	*/

	'clean_urls' => false,
	
	/**
	* Locale settings.
	*
	* locales   : Array of locales to try until success. You can also set the value to "NULL" to use the default locale.
	* lc_numeric: Set to true to set LC_NUMERIC to the locale you specified.
	*/
	
	'locale' => array
	(
		'locales'    => array('en_US.UTF-8', 'en_US.utf8', 'C'),
		'lc_numeric' => false,
	),
	
	/**
	* Set the default timezone used by various PHP date functions.
	*
	* @see http://php.net/manual/en/timezones.php
	*/
	
	'timezone' => 'UTC',

	/**
	* Class aliases used by the autoloader.
	* The key is the original class name and the value is the alias.
	*/

	'aliases' => array
	(
		'mako\URL' => 'URL',
	),

	/**
	* Packages to initialize by default.
	*/

	'packages' => array
	(
		
	),
	
	/**
	* Error handler settings.
	*
	* enable        : Set to true to enable the Mako error handler.
	* display_errors: Set to true to display errors caught by the mako error handlers.
	* log_errors    : Set to true if you want to log errors caught by the Mako errors handlers.
	*/
	
	'error_handler' => array
	(
		'enable'         => true,
		'display_errors' => true, // It is recommended to set this value to false when you are in production.
		'log_errors'     => true,
	),
);

/** -------------------- End of file --------------------**/