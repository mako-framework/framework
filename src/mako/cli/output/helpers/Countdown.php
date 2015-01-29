<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

/**
 * Countdown helper.
 *
 * @author  Yamada Taro
 */

class Countdown
{
	/**
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */

	protected $output;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\output\Output  $output  Output instance
	 */

	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * Counts down from n.
	 *
	 * @access  public
	 * @param   int     $from  Number of seconds to count down
	 */

	public function draw($from = 5)
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

				usleep(250000);
			}
			while($dots++ < 3);

			$dots = 0;
		}
		while($from-- > 1);

		$this->output->write("\r" . str_repeat(' ', $totalLength) . "\r");
	}
}