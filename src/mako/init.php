<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

//------------------------------------------------------------------------------------------
// Define some constants
//------------------------------------------------------------------------------------------

define('MAKO_START', microtime(true));
define('MAKO_VERSION', '4.2.3');
define('MAKO_IS_WINDOWS', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));

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

include realpath(__DIR__ . '/../../../../autoload.php');