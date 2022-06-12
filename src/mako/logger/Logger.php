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
	protected $logger;
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
	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

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
	public function emergency($message, array $context = []): void
	{
		$this->logger->emergency($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function alert($message, array $context = []): void
	{
		$this->logger->alert($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function critical($message, array $context = []): void
	{
		$this->logger->critical($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function error($message, array $context = []): void
	{
		$this->logger->error($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function warning($message, array $context = []): void
	{
		$this->logger->warning($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function notice($message, array $context = []): void
	{
		$this->logger->notice($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function info($message, array $context = []): void
	{
		$this->logger->info($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function debug($message, array $context = []): void
	{
		$this->logger->debug($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function log($level, $message, array $context = []): void
	{
		$this->logger->log($level, $message, $context + $this->context);
	}
}
