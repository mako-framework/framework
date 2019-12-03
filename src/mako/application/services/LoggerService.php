<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\gatekeeper\Gatekeeper;
use mako\http\Request;
use mako\logger\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
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
	 * Get information about the current user.
	 *
	 * @return array|null
	 */
	protected function getUserContext(): ?array
	{
		if($this->app->isCommandLine() === false)
		{
			try
			{
				$user = ['ip_address' => $this->container->get(Request::class)->getIp()];

				if($this->container->has(Gatekeeper::class) && ($gatekeeperUser = $this->container->get(Gatekeeper::class)->getUser()) !== null)
				{
					$user += ['id' => $gatekeeperUser->getId(), 'username' => $gatekeeperUser->getUsername()];
				}

				return $user;
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

		if(($user = $this->getUserContext()) !== null)
		{
			$context['user'] = $user;
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
	 * Returns a syslog handler.
	 *
	 * @return \Monolog\Handler\SyslogHandler
	 */
	protected function getSyslogHandler(): SyslogHandler
	{
		return new SyslogHandler($this->config->get('application.identifier', 'Mako'), $this->config->get('application.syslog.facility', LOG_USER));
	}

	/**
	 * Returns a error log handler.
	 *
	 * @return \Monolog\Handler\ErrorLogHandler
	 */
	protected function getErrorLogHandler(): ErrorLogHandler
	{
		return new ErrorLogHandler;
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
