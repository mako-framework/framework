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
	* You can define as many session configurations as you want.
	*
	* The supported session types are: "Database", "Native" and "Redis".
	*
	* type         : Session type you want to use (case-sensitive).
	* configuration: Database or redis configuration to use for sessions (only required when using "datbase" or "redis" sessions).
	*/
	
	'configurations' => array
	(
		'database' => array
		(
			'type'          => 'Database',
			'configuration' => 'test',
		),

		'native' => array
		(
			'type' => 'Native',
		),

		'redis' => array
		(
			'type'          => 'Redis',
			'configuration' => 'session',
		),
	),
);

/** -------------------- End of file --------------------**/