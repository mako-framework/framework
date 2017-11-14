<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services\web;

use Throwable;

use mako\application\services\Service;
use mako\error\ErrorHandler;
use mako\error\handlers\web\DevelopmentHandler;
use mako\error\handlers\web\ProductionHandler;

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
		$errorHandler = new ErrorHandler($this->container);

		if($this->container->get('config')->get('application.error_handler.log_errors'))
		{
			$errorHandler->setLogger(function()
			{
				return 	$this->container->get('logger');
			});
		}

		if($this->container->get('config')->get('application.error_handler.display_errors'))
		{
				$errorHandler->handle(Throwable::class, DevelopmentHandler::class);
		}
		else
		{
				$errorHandler->handle(Throwable::class, ProductionHandler::class);
		}

		$this->container->registerInstance([ErrorHandler::class, 'errorHandler'], $errorHandler);
	}
}
