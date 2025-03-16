<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\traits;

use function exec;
use function shell_exec;

/**
 * Stty trait.
 */
trait SttyTrait
{
	/**
	 * Do we have stty support?
	 */
	protected ?bool $hasStty = null;

	/**
	 * Do we have stty support?
	 */
	protected function hasStty(): bool
	{
		if ($this->hasStty === null) {
			exec('stty 2>&1', result_code: $status);

			$this->hasStty = $status === 0;
		}

		return $this->hasStty;
	}

	/**
	 * Returns the current stty settings.
	 */
	protected function getSttySettings(): string
	{
		return shell_exec('stty -g');
	}

	/**
	 * Sets the stty settings.
	 */
	protected function setSttySettings(string $settings): void
	{
		exec("stty {$settings}");
	}

	/**
	 * Executes a callable in a stty sandbox.
	 */
	protected function sttySandbox(callable $callable): mixed
	{
		$settings = $this->getSttySettings();

		try {
			return $callable();
		}
		finally {
			$this->setSttySettings($settings);
		}
	}
}
