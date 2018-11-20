<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services\cli;

use mako\application\services\Service;
use mako\error\ErrorHandler;
use mako\error\handlers\cli\DevelopmentHandler;
use mako\error\handlers\cli\ProductionHandler;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Error handler service.
 *
 * @author Frederic G. Østby
 */
class ErrorHandlerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$config = $this->config->get('application.error_handler');

		$errorHandler = new ErrorHandler($this->container);

		if($config['log_errors'])
		{
			$errorHandler->setLogger(function()
			{
				return 	$this->container->get(LoggerInterface::class);
			});
		}

		$errorHandler->handle(Throwable::class, $config['display_errors'] ? DevelopmentHandler::class : ProductionHandler::class);

		$this->container->registerInstance([ErrorHandler::class, 'errorHandler'], $errorHandler);
	}
}
