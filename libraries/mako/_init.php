<?php

// Define some constants

define('MAKO_START', microtime(true));
define('MAKO_MAGIC_QUOTES', get_magic_quotes_gpc());
define('MAKO_IS_WINDOWS', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));
define('MAKO_APPLICATION', MAKO_APPLICATION_PATH . '/' . MAKO_APPLICATION_NAME);
define('MAKO_APPLICATION_ID', md5(MAKO_APPLICATION));
define('MAKO_BUNDLES', MAKO_APPLICATION . '/bundles');

// Set path for error logs

ini_set('error_log', MAKO_APPLICATION . '/storage/logs/error_' . gmdate('Y_m_d') . '.log');

// Map all core classes

include MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php';

mako\ClassLoader::addClasses(array
(
	'mako\ArrayTo'                  => MAKO_LIBRARIES_PATH . '/mako/ArrayTo.php',
	'mako\Benchmark'                => MAKO_LIBRARIES_PATH . '/mako/Benchmark.php',
	'mako\CLI'                      => MAKO_LIBRARIES_PATH . '/mako/CLI.php',
	'mako\Cache'                    => MAKO_LIBRARIES_PATH . '/mako/Cache.php',
	'mako\cache\APC'                => MAKO_LIBRARIES_PATH . '/mako/cache/APC.php',
	'mako\cache\Adapter'            => MAKO_LIBRARIES_PATH . '/mako/cache/Adapter.php',
	'mako\cache\File'               => MAKO_LIBRARIES_PATH . '/mako/cache/File.php',
	'mako\cache\Memcache'           => MAKO_LIBRARIES_PATH . '/mako/cache/Memcache.php',
	'mako\cache\Memcached'          => MAKO_LIBRARIES_PATH . '/mako/cache/Memcached.php',
	'mako\cache\Memory'             => MAKO_LIBRARIES_PATH . '/mako/cache/Memory.php',
	'mako\cache\Redis'              => MAKO_LIBRARIES_PATH . '/mako/cache/Redis.php',
	'mako\cache\SQLite'             => MAKO_LIBRARIES_PATH . '/mako/cache/SQLite.php',
	'mako\cache\WinCache'           => MAKO_LIBRARIES_PATH . '/mako/cache/WinCache.php',
	'mako\cache\XCache'             => MAKO_LIBRARIES_PATH . '/mako/cache/XCache.php',
	'mako\cache\ZendDisk'           => MAKO_LIBRARIES_PATH . '/mako/cache/ZendDisk.php',
	'mako\cache\ZendMemory'         => MAKO_LIBRARIES_PATH . '/mako/cache/ZendMemory.php',
	//'mako\ClassLoader'              => MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php',
	'mako\Controller'               => MAKO_LIBRARIES_PATH . '/mako/Controller.php',
	'mako\Cookie'                   => MAKO_LIBRARIES_PATH . '/mako/Cookie.php',
	'mako\Crypto'                   => MAKO_LIBRARIES_PATH . '/mako/Crypto.php',
	'mako\crypto\Adapter'           => MAKO_LIBRARIES_PATH . '/mako/crypto/Adapter.php',
	'mako\crypto\Mcrypt'            => MAKO_LIBRARIES_PATH . '/mako/crypto/Mcrypt.php',
	'mako\crypto\OpenSSL'           => MAKO_LIBRARIES_PATH . '/mako/crypto/OpenSSL.php',
	'mako\Curl'                     => MAKO_LIBRARIES_PATH . '/mako/Curl.php',
	'mako\Database'                 => MAKO_LIBRARIES_PATH . '/mako/Database.php',
	'mako\DateTime'                 => MAKO_LIBRARIES_PATH . '/mako/DateTime.php',
	'mako\HTML'                     => MAKO_LIBRARIES_PATH . '/mako/HTML.php',
	'mako\I18n'                     => MAKO_LIBRARIES_PATH . '/mako/I18n.php',
	'mako\Image'                    => MAKO_LIBRARIES_PATH . '/mako/Image.php',
	'mako\image\Adapter'            => MAKO_LIBRARIES_PATH . '/mako/image/Adapter.php',
	'mako\image\GD'                 => MAKO_LIBRARIES_PATH . '/mako/image/GD.php',
	'mako\image\ImageMagick'        => MAKO_LIBRARIES_PATH . '/mako/image/ImageMagick.php',
	'mako\image\Imagick'            => MAKO_LIBRARIES_PATH . '/mako/image/Imagick.php',
	'mako\Input'                    => MAKO_LIBRARIES_PATH . '/mako/Input.php',
	'mako\Log'                      => MAKO_LIBRARIES_PATH . '/mako/Log.php',
	'mako\log\Adapter'              => MAKO_LIBRARIES_PATH . '/mako/log/Adapter.php',
	'mako\log\File'                 => MAKO_LIBRARIES_PATH . '/mako/log/File.php',
	'mako\log\FirePHP'              => MAKO_LIBRARIES_PATH . '/mako/log/FirePHP.php',
	'mako\log\Syslog'               => MAKO_LIBRARIES_PATH . '/mako/log/Syslog.php',
	'mako\Mako'                     => MAKO_LIBRARIES_PATH . '/mako/Mako.php',
	'mako\Model'                    => MAKO_LIBRARIES_PATH . '/mako/Model.php',
	'mako\Num'                      => MAKO_LIBRARIES_PATH . '/mako/Num.php',
	'mako\Pagination'               => MAKO_LIBRARIES_PATH . '/mako/Pagination.php',
	'mako\reactor\handlers\Bundles' => MAKO_LIBRARIES_PATH . '/mako/reactor/handlers/Bundles.php',
	'mako\reactor\Reactor'          => MAKO_LIBRARIES_PATH . '/mako/reactor/Reactor.php',
	'mako\reactor\Task'             => MAKO_LIBRARIES_PATH . '/mako/reactor/Task.php',
	'mako\reactor\handlers\Tasks'   => MAKO_LIBRARIES_PATH . '/mako/reactor/handlers/Tasks.php',
	'mako\Redis'                    => MAKO_LIBRARIES_PATH . '/mako/Redis.php',
	'mako\Request'                  => MAKO_LIBRARIES_PATH . '/mako/Request.php',
	'mako\Response'                 => MAKO_LIBRARIES_PATH . '/mako/Response.php',
	'mako\Security'                 => MAKO_LIBRARIES_PATH . '/mako/Security.php',
	'mako\Session'                  => MAKO_LIBRARIES_PATH . '/mako/Session.php',
	'mako\session\Adapter'          => MAKO_LIBRARIES_PATH . '/mako/session/Adapter.php',
	'mako\session\Database'         => MAKO_LIBRARIES_PATH . '/mako/session/Database.php',
	'mako\session\Redis'            => MAKO_LIBRARIES_PATH . '/mako/session/Redis.php',
	'mako\String'                   => MAKO_LIBRARIES_PATH . '/mako/String.php',
	'mako\UTF8'                     => MAKO_LIBRARIES_PATH . '/mako/UTF8.php',
	'mako\UUID'                     => MAKO_LIBRARIES_PATH . '/mako/UUID.php',
	'mako\UserAgent'                => MAKO_LIBRARIES_PATH . '/mako/UserAgent.php',
	'mako\View'                     => MAKO_LIBRARIES_PATH . '/mako/View.php',
));

// Set up autoloader

spl_autoload_register('mako\ClassLoader::load');