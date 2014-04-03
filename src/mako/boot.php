<?php

use mako\core\Application;

//------------------------------------------------------------------------------------------
// Define some constants
//------------------------------------------------------------------------------------------

define('MAKO_VERSION', '4.0.0');
define('MAKO_START', microtime(true));
define('MAKO_APPLICATION_PARENT_PATH', dirname(MAKO_APPLICATION_PATH));

//------------------------------------------------------------------------------------------
// Convert all errors to ErrorExceptions and override default path for error logs
//------------------------------------------------------------------------------------------

set_error_handler(function($code, $message, $file, $line)
{
        if((error_reporting() & $code) !== 0)
        {
                throw new ErrorException($message, $code, 0, $file, $line);
        }

        return true;
});

ini_set('error_log', MAKO_APPLICATION_PATH . '/storage/logs/error_' . gmdate('Y_m_d') . '.log');

//------------------------------------------------------------------------------------------
// Include helpers and the composer autoloader
//------------------------------------------------------------------------------------------

include __DIR__ . '/helpers.php';

include realpath(__DIR__ . '/../../../../autoload.php');

//------------------------------------------------------------------------------------------
// Boot the application
//------------------------------------------------------------------------------------------

$app = Application::start(MAKO_APPLICATION_PATH);

