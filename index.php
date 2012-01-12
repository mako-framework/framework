<?php

//------------------------------------------------------------------------------------------
// START OF USER CONFIGURABLE SECTION
//------------------------------------------------------------------------------------------

/**
* Set which PHP errors are reported.
* @see http://php.net/manual/en/function.error-reporting.php
*/

error_reporting(E_ALL | E_STRICT);

/**
* Choose if errors that are NOT caught by the Mako error and exception handlers should be 
* printed to the screen as part of the output or if they should be hidden from the user. 
* It is recommended to set this value to false when you are in production.
*/

ini_set('display_errors', true);

/**
* Setting this value to true can speed up execution by reducing the 
* number of config and language files to include.
*/

define('MAKO_INTERNAL_CACHE', false);

/**
* Define the path to the libraries directory (without trailing slash).
*/

define('MAKO_LIBRARIES_PATH', __DIR__ . '/libraries');

/**
* Define the path to the parent directory of the app directory (without trailing slash).
*/

define('MAKO_APPLICATION_PATH', __DIR__);

/**
* Define the name of your application. The name must match the name of the app directory.
*/

define('MAKO_APPLICATION_NAME', 'app');

//------------------------------------------------------------------------------------------
// END OF USER CONFIGURABLE SECTION
//------------------------------------------------------------------------------------------

require MAKO_LIBRARIES_PATH . '/mako/Mako.php';

Mako::run();

/** -------------------- End of file --------------------**/