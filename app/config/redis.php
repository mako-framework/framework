<?php

//---------------------------------------------
// Redis configuration
//---------------------------------------------

return array
(
	/**
	* Default configuration to use.
	*/

	'default' => 'localhost',

	/**
	* You can define as many Redis configurations as you want.
	*/

	'configurations' => array
	(
		'localhost' => array
		(
			'host'     => 'localhost',
			'port'     => 6379,
			'password' => '',
		),
	)
);

/** -------------------- End of file --------------------**/