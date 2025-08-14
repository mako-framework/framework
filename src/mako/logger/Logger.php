<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\logger;

use Override;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Logger.
 */
class Logger implements LoggerInterface
{
	protected array $context = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected LoggerInterface $logger
	) {
	}

	/**
	 * Returns the underlying logger instance.
	 */
	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	/**
	 * Sets the global logger context.
	 *
	 * @return $this
	 */
	public function setContext(array $context): static
	{
		$this->context = $context;

		return $this;
	}

	/**
	 * Returns the global logger context.
	 */
	public function getContext(): array
	{
		return $this->context;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function emergency(string|Stringable $message, array $context = []): void
	{
		$this->logger->emergency($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function alert(string|Stringable $message, array $context = []): void
	{
		$this->logger->alert($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function critical(string|Stringable $message, array $context = []): void
	{
		$this->logger->critical($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function error(string|Stringable $message, array $context = []): void
	{
		$this->logger->error($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function warning(string|Stringable $message, array $context = []): void
	{
		$this->logger->warning($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function notice(string|Stringable $message, array $context = []): void
	{
		$this->logger->notice($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function info(string|Stringable $message, array $context = []): void
	{
		$this->logger->info($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function debug(string|Stringable $message, array $context = []): void
	{
		$this->logger->debug($message, $context + $this->context);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function log(mixed $level, string|Stringable $message, array $context = []): void
	{
		$this->logger->log($level, $message, $context + $this->context);
	}
}
