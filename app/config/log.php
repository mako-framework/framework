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
	* The supported log types are: "file", "firephp", "growl", "prowl" and "syslog".
	*
	* type         : Log type you want to use.
	* path         : Location where you want to write the logs (only required when using "file" logs).
	* configuration: Growl/Prowl configuration to use for logging (only required when using "growl" or "prowl" logs).
	* identifier   : Application identifier (only required when using "syslog" logs).
	* facility     : Specify what type of program is logging the message (only required when using "syslog" logs).
	*/
	
	'configurations' => array
	(
		'file' => array
		(
			'type'  => 'file',
			'path'  => MAKO_APPLICATION . '/storage/logs',
		),
		
		'firephp' => array
		(
			'type' => 'firephp',
		),

		'growl' => array
		(
			'type'          => 'growl',
			'configuration' => 'logger',
		),

		'prowl' => array
		(
			'type'          => 'prowl',
			'configuration' => 'my_iphone',
		),

		'syslog' => array
		(
			'type'       => 'syslog',
			'identifier' => 'Mako Framework',
			'facility'   => LOG_USER,
		),
	),
);

/** -------------------- End of file --------------------**/