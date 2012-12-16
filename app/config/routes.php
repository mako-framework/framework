<?php

//---------------------------------------------
// Routing configuration
//---------------------------------------------

return array
(

	/**
	* Automatically map routes to controllers.
	* If set to false then only routes matching one of the custom routes will be considered valid.
	*/

	'automap' => true,

	/**
	 * Default route.
	 */

	'default_route' => 'index/index',

	/**
	 * Custom routes.
	 */

	'custom_routes' => array
	(
		
	),

	/**
	 * Languages.
	 * 
	 * If the first segment of the route matches the language 
	 * then the corresponding language pack will be loaded.
	 */

	'languages' => array
	(
		//'no' => 'nb_NO',
		//'fr' => 'fr_FR',
	),

	/**
	 * Package base routes.
	 * 
	 * The array key is the base route that you want the package 
	 * to respond to and the value is the package name.
	 */

	'packages' => array
	(

	),
);

/** -------------------- End of file --------------------**/