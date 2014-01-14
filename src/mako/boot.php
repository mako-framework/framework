<?php

use mako\core\Application;
use mako\core\Config;

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

// Register dependencies

$app->registerInstance(['mako\core\Config', 'mako.config'], new Config(MAKO_APPLICATION_PATH));

//------------------------------------------------------------------------------------------
// Configure stuff
//------------------------------------------------------------------------------------------

$config = $app->get('mako.config')->get('application');

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

//------------------------------------------------------------------------------------------
// Include 
//------------------------------------------------------------------------------------------

if(file_exists(MAKO_APPLICATION_PATH . '/bootstrap.php'))
{
	include MAKO_APPLICATION_PATH . '/bootstrap.php';
}