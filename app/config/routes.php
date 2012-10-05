<?php

//---------------------------------------------
// Routing configuration
//---------------------------------------------

return array
(
	/**
	 * Locales.
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