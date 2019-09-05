<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\Gatekeeper;
use mako\logger\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Throwable;

use function date;

/**
 * Logger service.
 *
 * @author Frederic G. Østby
 */
class LoggerService extends Service
{
	/**
	 * Get the current user.
	 *
	 * @return \mako\gatekeeper\entities\user\UserEntityInterface|null
	 */
	protected function getUser(): ?UserEntityInterface
	{
		if($this->app->isCommandLine() === false && $this->container->has(Gatekeeper::class))
		{
			try
			{
				return $this->container->get(Gatekeeper::class)->getUser();
			}
			catch(Throwable $e)
			{
				return null;
			}
		}

		return null;
	}
	/**
	 * Get global logger context.
	 *
	 * @return array
	 */
	protected function getContext(): array
	{
		$context = [];

		if(($user = $this->getUser()) !== null)
		{
			$context['user'] = ['id' => $user->getId(), 'username' => $user->getUsername()];
		}

		return $context;
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
	 * @return \Monolog\Handler\StreamHandler
	 */
	protected function getStreamHandler(): StreamHandler
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
		return $this->{"get{$handler}Handler"}();
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
