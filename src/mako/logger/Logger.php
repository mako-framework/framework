<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\logger;

use Psr\Log\LoggerInterface;

/**
 * Logger.
 */
class Logger implements LoggerInterface
{
	/**
	 * Global logger context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Constructor.
	 *
	 * @param \Psr\Log\LoggerInterface $logger Logger instance
	 */
	public function __construct(
		protected LoggerInterface $logger
	)
	{}

	/**
	 * Returns the underlying logger instance.
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	/**
	 * Sets the global logger context.
	 *
	 * @param  array $context Context
	 * @return $this
	 */
	public function setContext(array $context)
	{
		$this->context = $context;

		return $this;
	}

	/**
	 * Returns the global logger context.
	 *
	 * @return array
	 */
	public function getContext(): array
	{
		return $this->context;
	}

	/**
	 * {@inheritDoc}
	 */
	public function emergency(string|\Stringable $message, array $context = []): void
	{
		$this->logger->emergency($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function alert(string|\Stringable $message, array $context = []): void
	{
		$this->logger->alert($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function critical(string|\Stringable $message, array $context = []): void
	{
		$this->logger->critical($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function error(string|\Stringable $message, array $context = []): void
	{
		$this->logger->error($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function warning(string|\Stringable $message, array $context = []): void
	{
		$this->logger->warning($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function notice(string|\Stringable $message, array $context = []): void
	{
		$this->logger->notice($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function info(string|\Stringable $message, array $context = []): void
	{
		$this->logger->info($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function debug(string|\Stringable $message, array $context = []): void
	{
		$this->logger->debug($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function log(mixed $level, string|\Stringable $message, array $context = []): void
	{
		$this->logger->log($level, $message, $context + $this->context);
	}
}
