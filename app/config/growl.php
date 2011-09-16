<?php

//---------------------------------------------
// Growl configuration
//---------------------------------------------

return array
(
	/**
	* Application identifier.
	*/
	
	'identifier' => 'Mako Framework',
	
	/**
	* Default configuration to use.
	*/

	'default' => 'localhost',
	
	/**
	* You can define as many Growl configurations as you want.
	*
	* host         : IP address or hostname of the Growl host.
	* password     : Password of the Growl host.
	* notifications: Notification types to register (key = notification name, value = enabled/disabled).
	*/
	
	'configurations' => array
	(
		'localhost' => array
		(
			'host'          => '127.0.0.1',
			'password'      => '',
			'notifications' => array
			(
				'Info'  => true,
			),
		),

		'logger' => array
		(
			'host'          => '127.0.0.1',
			'password'      => '',
			'notifications' => array
			(
				'Emergency' => true,
				'Alert'     => true,
				'Critical'  => true,
				'Error'     => true,
				'Warning'   => true,
				'Notice'    => true,
				'Info'      => true,
				'Debug'     => true,
			),
		),
	),
);

/** -------------------- End of file --------------------**/