<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\Gatekeeper;
use mako\logger\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

use function date;
use function ucfirst;

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
		if($this->app->isCommandLine() === false && $this->container->has(Gatekeeper::class))
		{
			$user = $this->container->get(Gatekeeper::class)->getUser();

			return ['user_id' => $user !== null ? $user->getId() : null];
		}

		return [];
	}

	/**
	 * Returns the storage path.
	 *
	 * @return string
	 */
	protected function getStoragePath(): string
	{
		$base = $this->config->get('application.storage_path') ?? "{$this->app->getPath()}/storage";

		return "{$base}/logs/" . date('Y-m-d') . '.mako';
	}

	/**
	 * Returns a stream handler.
	 *
	 * @return \Monolog\Handler\HandlerInterface
	 */
	protected function getStreamHandler(): HandlerInterface
	{
		$handler = new StreamHandler($this->getStoragePath());

		$formatter = new LineFormatter(null, null, true, true);

		$formatter->includeStacktraces();

		$handler->setFormatter($formatter);

		return $handler;
	}

	/**
	 * Returns a log handler.
	 *
	 * @param  string                            $handler Handler name
	 * @return \Monolog\Handler\HandlerInterface
	 */
	protected function getHandler(string $handler): HandlerInterface
	{
		$method = 'get' . ucfirst($handler) . 'Handler';

		return $this->{$method}();
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

			foreach((array) $this->config->get('application.log_handler', ['stream']) as $handler)
			{
				$logger->pushHandler($this->getHandler($handler));
			}

			return $logger;
		});
	}
}
