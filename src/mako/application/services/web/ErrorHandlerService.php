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
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Error handler service.
 */
class ErrorHandlerService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config->get('application.error_handler');

		$errorHandler = new ErrorHandler($this->container, register: $config['register'] ?? true);

		if ($config['log_errors']) {
			$errorHandler->setLogger(fn () => $this->container->get(LoggerInterface::class));

			if (!empty($config['dont_log'])) {
				$errorHandler->dontLog($config['dont_log']);
			}
		}

		$errorHandler->addHandler(Throwable::class, $config['display_errors'] ? DevelopmentHandler::class : ProductionHandler::class, ['keep' => $config['keep'] ?? []]);

		$this->container->registerInstance([ErrorHandler::class, 'errorHandler'], $errorHandler);
	}
}
