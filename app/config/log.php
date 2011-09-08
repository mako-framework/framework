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
	* The supported log types are: "file", "firephp", "growl" and "syslog".
	*
	* type      : Log type you want to use.
	* path      : Location where you want to write the logs (only required when using "file" logs).
	* host      : IP address or hostname (only required when using "growl" logs).
	* password  : Growl server password (only required when using "growl" logs).
	* identifier: Application identifier (only required when using "syslog" logs).
	* facility  : Specify what type of program is logging the message (only required when using "syslog" logs).
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
			'type'       => 'growl',
			'host'       => '127.0.0.1',
			'password'   => '',
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