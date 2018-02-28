<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\services\Service;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

use Psr\Log\LoggerInterface;

/**
 * Logger service.
 *
 * @author Frederic G. Østby
 */
class LoggerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->registerSingleton([LoggerInterface::class, 'logger'], function($container)
		{
			$logger = new Logger('mako');

			$handler = new StreamHandler($container->get('app')->getPath() . '/storage/logs/' . date('Y-m-d') . '.mako', Logger::DEBUG);

			$handler->setFormatter(new LineFormatter(null, null, true, true));

			$logger->pushHandler($handler);

			return $logger;
		});
	}
}
