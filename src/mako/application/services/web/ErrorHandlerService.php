<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services\web;

use mako\application\services\Service;
use mako\error\ErrorHandler;
use mako\error\handlers\web\DevelopmentHandler;
use mako\error\handlers\web\ProductionHandler;
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
		$config = $this->container->get('config')->get('application.error_handler');

		$errorHandler = new ErrorHandler($this->container);

		if($config['log_errors'])
		{
			$errorHandler->setLogger(function()
			{
				return 	$this->container->get('logger');
			});
		}

		$errorHandler->handle(Throwable::class, $config['display_errors'] ? DevelopmentHandler::class : ProductionHandler::class);

		$this->container->registerInstance([ErrorHandler::class, 'errorHandler'], $errorHandler);
	}
}
