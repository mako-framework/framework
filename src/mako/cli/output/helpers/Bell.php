<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\Output;

/**
 * Terminal bell helper.
 *
 * @author  Frederic G. Østby
 */

class Bell
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
	 * Rings the terminal bell n times.
	 *
	 * @access  public
	 * @param   int     $times  Number of times to ring the bell
	 */

	public function ring($times = 1)
	{
		$this->output->write(str_repeat("\x07", $times));
	}
}