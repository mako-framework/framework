<?php

//---------------------------------------------
// Routing configuration
//---------------------------------------------

return array
(
	/**
	 * Use default routing?
	 */

	'default_route' => true,

	/**
	 * Index route.
	 */

	'index_route' => 'index/index',

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