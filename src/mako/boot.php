<?php

use mako\core\Application;

//------------------------------------------------------------------------------------------
// Define some constants
//------------------------------------------------------------------------------------------

define('MAKO_VERSION', '4.0.0');
define('MAKO_START', microtime(true));
define('MAKO_APPLICATION_PARENT_PATH', dirname(MAKO_APPLICATION_PATH));

//------------------------------------------------------------------------------------------
// Include helpers and the composer autoloader
//------------------------------------------------------------------------------------------

include __DIR__ . '/helpers.php';

include realpath(__DIR__ . '/../../../../autoload.php');

//------------------------------------------------------------------------------------------
// Boot the application
//------------------------------------------------------------------------------------------

$app = Application::instance(MAKO_APPLICATION_PATH);

//------------------------------------------------------------------------------------------
// Configure stuff
//------------------------------------------------------------------------------------------

$config = $app->get('config')->get('application');

// Set internal charset

define('MAKO_CHARSET', $config['charset']);

mb_language('uni');
mb_regex_encoding(MAKO_CHARSET);
mb_internal_encoding(MAKO_CHARSET);

// Set default timezone

date_default_timezone_set($config['timezone']);

// Set locale information

$app->setLanguage($config['default_language']);

setlocale(LC_ALL, $config['locale']['locales']);

if($config['locale']['lc_numeric'] === false)
{
	setlocale(LC_NUMERIC, 'C');
}

// Clean up

unset($config);

/** -------------------- End of file -------------------- **/