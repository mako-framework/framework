<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers\traits;

use function substr_count;

/**
 * Interactive input trait.
 *
 * @property \mako\cli\output\Output $output
 */
trait InteractiveInputTrait
{
	/**
	 * Number of newlines in the last render.
	 */
	protected int $newlinesInLastRender = 0;

	/**
	 * Reset the number of newlines in the last render.
	 */
	protected function resetNewlinesInLastRender(): void
	{
		$this->newlinesInLastRender = 0;
	}

	/**
	 * Clear the previous render and render the provided string.
	 */
	protected function render(string $string): void
	{
		if ($this->newlinesInLastRender > 0) {
			$this->output->cursor->up($this->newlinesInLastRender);
		}

		$this->output->cursor->clearScreenFromCursor();

		$this->output->write($string);

		$this->newlinesInLastRender = substr_count($string, PHP_EOL);
	}
}
