<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\signals;

use function count;
use function function_exists;
use function pcntl_async_signals;
use function pcntl_signal;

/**
 * Signal handler.
 */
class SignalHandler
{
	/**
	 * Can we handle signals?
	 */
	protected bool $canHandle = false;

	/**
	 * Signal handlers.
	 */
	protected array $handlers = [];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (function_exists('pcntl_async_signals')) {
			pcntl_async_signals(true);

			$this->canHandle = true;
		}
	}

	/**
	 * Can we handle signals?
	 */
	public function canHandleSignals(): bool
	{
		return $this->canHandle;
	}

	/**
	 * Registers a singal handler for the chosen signal.
	 */
	public function addHandler(array|int $signal, callable $handler): void
	{
		foreach ((array) $signal as $signalToHandle) {
			$this->handlers[$signalToHandle][] = $handler;

			if (count($this->handlers[$signalToHandle]) === 1) {
				pcntl_signal($signalToHandle, function ($signal): void {
					$count = count($this->handlers[$signal]);

					foreach ($this->handlers[$signal] as $i => $handler) {
						$isLast = $i === $count - 1;

						$handler($signal, $isLast);
					}
				});
			}
		}
	}
}
