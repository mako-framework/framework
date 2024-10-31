<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\Output;

use function str_repeat;

/**
 * Bell component.
 */
class Bell
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * Rings the terminal bell n times.
	 */
	public function ring(int $times = 1): void
	{
		$this->output->write(str_repeat("\x07", $times));
	}
}
