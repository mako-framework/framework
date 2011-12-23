<?php

//---------------------------------------------
// Session configuration
//---------------------------------------------

return array
(
	/**
	* Default configuration to use.
	*/
	
	'default' => 'native',
	
	/**
	* You can define as many cache configurations as you want.
	*
	* The supported cache types are: "Native" and "Redis".
	*
	* type         : Cache type you want to use (case-sensitive).
	* configuration: Redis configuration to use for sessions (only required when using "redis" sessions).
	*/
	
	'configurations' => array
	(
		'native' => array
		(
			'type'       => 'Native',
		),

		'redis' => array
		(
			'type'          => 'Redis',
			'configuration' => 'session',
		),
	),
);

/** -------------------- End of file --------------------**/