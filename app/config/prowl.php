<?php

//---------------------------------------------
// Prowl configuration
//---------------------------------------------

return array
(
	/**
	* Default configuration to use.
	*/

	'default' => 'my_iphone',

	/**
	* Application identifier.
	*/
	
	'identifier' => 'Mako Framework',

	/**
	* Provider key. Register at http://www.prowlapp.com/ to get one.
	*/

	'provider_key' => '',

	/**
	* You can define as many Prowl configurations as you want.
	*
	* api_key: Register at http://www.prowlapp.com/ to get one.
	*/

	'configurations' => array
	(
		'my_iphone' => array
		(
			'api_key' => '',
		),
	)
);

/** -------------------- End of file --------------------**/