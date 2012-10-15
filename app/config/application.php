<?php

//---------------------------------------------
// Application configuration
//---------------------------------------------

return array
(
	/**
	 * Base url of your application (without trailing slash).
	 * The framework will try to autodetect the url if the value is left empty.
	 */
	
	'base_url' => '',
	
	/**
	 * Set to true to hide "index.php" from your urls (this requires mod_rewrite).
	 */

	'clean_urls' => false,

	/**
	 * URL or path to your asset directory (without trailing slash).
	 */

	'asset_location' => '/mako/assets',

	/**
	 * Secret used to provide cryptographic signing, and should be set to a unique, unpredictable value.
	 * You should NOT use the secret included with the framwork in a production environment!
	 */
	
	'secret'   => 'oib[H7:Jqn2QcMv77>qMpc<gTLFndNLd',

	/**
	 * Set the default timezone used by various PHP date functions.
	 *
	 * @see http://php.net/manual/en/timezones.php
	 */
	
	'timezone' => 'UTC',

	/**
	 * Default character set used internally in the framework.
	 */

	'charset' => 'UTF-8',

	/**
	 * Default language.
	 * 
	 * Default language pack loaded by the i18n class.
	 */

	'default_language' => 'en_US',
	
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
	 * Class aliases used by the autoloader.
	 */

	'aliases' => array
	(
		'URL'    => 'mako\URL',
		'Assets' => 'mako\Assets',
	),

	/**
	 * Packages to initialize by default.
	 */

	'packages' => array
	(
		
	),

	/**
	 * Enable the debug toolbar?
	 * Note that response cache using ETags might not work as expected when the debug toolbar is enabled.
	 */

	'debug_toolbar' => false, // It is recommended to set this value to false when you are in production.

	/**
	 * Cache language files?
	 * Setting this value to true can speed up execution by reducing the number of language files to include.
	 */

	'language_cache' => false,

	/**
	 * Compress output?
	 * Setting this to true will reduce bandwidth usage while slightly increasing the CPU usage.
	 */

	'compress_output' => false,
	
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