<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\Application;
use mako\gatekeeper\Gatekeeper;
use mako\logger\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
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
	 * Get global logger context.
	 *
	 * @return array
	 */
	protected function getContext(): array
	{
		if($this->container->has(Gatekeeper::class))
		{
			$user = $this->container->get(Gatekeeper::class)->getUser();

			return ['user_id' => $user !== null ? $user->getId() : null];
		}

		return [];
	}

	/**
	 * Returns the default handler.
	 *
	 * @return \Monolog\Handler\HandlerInterface
	 */
	protected function getHandler(): HandlerInterface
	{
		$handler = new StreamHandler($this->container->get(Application::class)->getPath() . '/storage/logs/' . date('Y-m-d') . '.mako');

		$formatter = new LineFormatter(null, null, true, true);

		$formatter->includeStacktraces();

		$handler->setFormatter($formatter);

		return $handler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->registerSingleton([LoggerInterface::class, 'logger'], function()
		{
			$logger = new Logger('mako');

			$logger->setContext($this->getContext());

			$logger->pushHandler($this->getHandler());

			return $logger;
		});
	}
}
