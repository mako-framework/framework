<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\Application;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

use function date;

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
	public function register(): void
	{
		$this->container->registerSingleton([LoggerInterface::class, 'logger'], function($container)
		{
			$logger = new Logger('mako');

			$handler = new StreamHandler($container->get(Application::class)->getPath() . '/storage/logs/' . date('Y-m-d') . '.mako', Logger::DEBUG);

			$handler->setFormatter(new LineFormatter(null, null, true, true));

			$logger->pushHandler($handler);

			return $logger;
		});
	}
}
