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
use Monolog\Logger as MonoLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

use function date;

/**
 * Logger service.
 */
class LoggerService extends Service
{
	/**
	 * Get information about the current user.
	 */
	protected function getUserContext(): ?array
	{
		if ($this->app->isCommandLine() === false) {
			try {
				$user = ['ip_address' => $this->container->get(Request::class)->getIp()];

				if ($this->container->hasInstanceOf(Gatekeeper::class) && ($gatekeeperUser = $this->container->get(Gatekeeper::class)->getUser()) !== null) {
					$user += ['id' => $gatekeeperUser->getId(), 'username' => $gatekeeperUser->getUsername()];
				}

				return $user;
			}
			catch (Throwable $e) {
				return null;
			}
		}

		return null;
	}
	/**
	 * Get global logger context.
	 */
	protected function getContext(): array
	{
		$context = [];

		if (($user = $this->getUserContext()) !== null) {
			$context['user'] = $user;
		}

		return $context;
	}

	/**
	 * Returns the storage path.
	 */
	protected function getStoragePath(): string
	{
		return "{$this->app->getStoragePath()}/logs/" . date('Y-m-d') . '.mako';
	}

	/**
	 * Returns a stream handler.
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
	 */
	protected function getSyslogHandler(): SyslogHandler
	{
		return new SyslogHandler($this->config->get('application.logger.syslog.identifier', 'Mako'), $this->config->get('application.logger.syslog.facility', LOG_USER));
	}

	/**
	 * Returns a error log handler.
	 */
	protected function getErrorLogHandler(): ErrorLogHandler
	{
		return new ErrorLogHandler;
	}

	/**
	 * Returns a log handler.
	 */
	protected function getHandler(string $handler): HandlerInterface
	{
		return $this->{"get{$handler}Handler"}();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function register(): void
	{
		$config = $this->config->get('application.logger');

		$this->container->registerSingleton([LoggerInterface::class, 'logger'], function () use ($config) {
			$processors = ($config['replace_placeholders'] ?? true) === false ? [] : [new PsrLogMessageProcessor];

			$monolog = new MonoLogger($config['channel'] ?? 'mako', processors: $processors);

			foreach ($config['handler'] as $handler) {
				$monolog->pushHandler($this->getHandler($handler));
			}

			return (new Logger($monolog))->setContext($this->getContext());
		});
	}
}
