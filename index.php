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
 * Define the path to the libraries directory (without trailing slash).
 */

define('MAKO_LIBRARIES_PATH', __DIR__ . '/libraries');

/**
 * Define the path to the app directory (without trailing slash).
 */

define('MAKO_APPLICATION_PATH', __DIR__ . '/app');

//------------------------------------------------------------------------------------------
// END OF USER CONFIGURABLE SECTION
//------------------------------------------------------------------------------------------

require MAKO_LIBRARIES_PATH . '/mako/_init.php';

mako\Mako::factory()->run();

/** -------------------- End of file --------------------**/