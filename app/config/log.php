<?php

//---------------------------------------------
// Logging configuration
//---------------------------------------------

return array
(
	/**
	 * Default configuration to use.
	 */

	'default' => 'file',
	
	/**
	 * You can define as many logging configurations as you want.
	 *
	 * The supported log types are: "DebugToolbar", "File", "FirePHP", "Growl", "Prowl" and "Syslog".
	 *
	 * Growl and Prowl logging requires the official Growl and Prowl packages.
	 *
	 * type         : Log type you want to use (case-sensitive).
	 * path         : Location where you want to write the logs (only required when using "file" logs).
	 * configuration: Growl/Prowl configuration to use for logging (only required when using "growl" or "prowl" logs).
	 * identifier   : Application identifier (only required when using "syslog" logs).
	 * facility     : Specify what type of program is logging the message (only required when using "syslog" logs).
	 */
	
	'configurations' => array
	(
		'toolbar' => array
		(
			'type' => 'DebugToolbar',
		),
		'file' => array
		(
			'type'  => 'File',
			'path'  => MAKO_APPLICATION_PATH . '/storage/logs',
		),
		
		'firephp' => array
		(
			'type' => 'FirePHP',
		),

		/*'growl' => array
		(
			'type'          => 'Growl',
			'configuration' => 'logger',
		),

		'prowl' => array
		(
			'type'          => 'Prowl',
			'configuration' => 'my_iphone',
		),*/

		'syslog' => array
		(
			'type'       => 'Syslog',
			'identifier' => 'Mako Framework',
			'facility'   => LOG_USER,
		),
	),
);

/** -------------------- End of file --------------------**/