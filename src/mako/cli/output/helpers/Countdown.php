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
	protected const SLEEP_TIME = 250000;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	)
	{}

	/**
	 * Delay execution by SLEEP_TIME microseconds.
	 */
	protected function sleep(): void
	{
		usleep(static::SLEEP_TIME);
	}

	/**
	 * Counts down from n.
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
