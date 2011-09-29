<?php

//---------------------------------------------
// Request configuration
//---------------------------------------------

return array
(
	/**
	* Set the default route.
	*/

	'default_route' => 'index/_index',

	/**
	* You can add your own custom routes here.
	*/

	'custom_routes' => array
	(
		'hello_world' => function()
		{
			echo 'Hello World!';
		},
	),
);

/** -------------------- End of file --------------------**/