<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

use function str_pad;
use function str_repeat;
use function strlen;
use function usleep;

/**
 * Countdown helper.
 */
class Countdown
{
	/**
	 * Sleep time in microseconds.
	 *
	 * @var int
	 */
	const SLEEP_TIME = 250000;

	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output instance
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * Delay execution by SLEEP_TIME microseconds.
	 */
	protected function sleep(): void
	{
		usleep(static::SLEEP_TIME);
	}

	/**
	 * Counts down from n.
	 *
	 * @param int $from Number of seconds to count down
	 */
	public function draw(int $from = 5): void
	{
		$dots = 0;

		$fromLength = strlen($from);

		$totalLength = $fromLength + 5;

		do
		{
			do
			{
				$numbers = str_pad($from, $fromLength, '0', STR_PAD_LEFT);

				$this->output->write("\r" . str_pad($numbers . ' ' . str_repeat('.', $dots) . ' ', $totalLength, ' '));

				$this->sleep();
			}
			while($dots++ < 3);

			$dots = 0;
		}
		while($from-- > 1);

		$this->output->write("\r" . str_repeat(' ', $totalLength) . "\r");
	}
}
