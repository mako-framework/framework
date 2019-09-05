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
			$context['user_id'] = $user->getId();
		}

		return $context;
	}

	/**
	 * Returns the default handler.
	 *
	 * @return \Monolog\Handler\HandlerInterface
	 */
	protected function getHandler(): HandlerInterface
	{
		$handler = new StreamHandler($this->app->getPath() . '/storage/logs/' . date('Y-m-d') . '.mako');

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
