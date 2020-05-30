<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\logger;

use Monolog\Logger as MonoLogger;

/**
 * Logger.
 *
 * @author Frederic G. Østby
 */
class Logger extends MonoLogger
{
	/**
	 * Global logger context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Sets the global logger context.
	 *
	 * @param array $context Context
	 */
	public function setContext(array $context): void
	{
		$this->context = $context;
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
	 * {@inheritdoc}
	 */
	public function addRecord(int $level, string $message, array $context = []): bool
	{
		return parent::addRecord($level, $message, $context + $this->context);
	}
}
