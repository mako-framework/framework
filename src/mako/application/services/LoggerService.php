<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use \mako\application\services\Service;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Logger service.
 *
 * @author  Frederic G. Østby
 */

class LoggerService extends Service
{
	/**
	 * {@inheritdoc}
	 */

	public function register()
	{
		$this->container->registerSingleton(['Psr\Log\LoggerInterface', 'logger'], function($container)
		{
			$logger = new Logger('mako');

			$logger->pushHandler(new StreamHandler($container->get('app')->getPath() . '/storage/logs/' . date('Y-m-d') . '.mako', Logger::DEBUG));

			return $logger;
		});
	}
}