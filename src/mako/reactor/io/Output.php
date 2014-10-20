<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\io;

use \mako\reactor\io\StdErr;
use \mako\reactor\io\StdOut;

use \Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Reactor output.
 *
 * @author  Frederic G. Østby
 */

class Output extends StdOut
{
	/**
	 * StdErr instance.
	 * 
	 * @var \mako\reactor\io\StdErr;
	 */

	protected $stderr;

	/**
	 * Are we running on windows?
	 * 
	 * @var boolean
	 */

	protected $isWindows;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 */

	public function __construct()
	{
		parent::__construct();

		$this->stderr = new StdErr();

		$this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Returns the stderr instance.
	 * 
	 * @access  public
	 * @return  \mako\reactor\io\StdErr
	 */

	public function stderr()
	{
		return $this->stderr;
	}

	/**
	 * Output an error message to stderr.
	 * 
	 * @access  public
	 * @param   string  $message  Error message
	 */

	public function error($message)
	{
		$this->stderr->writeln('<red>' . $message . '</red>');
	}

	/**
	 * Clears the screen.
	 * 
	 * @access  public
	 */

	public function clearScreen()
	{
		if($this->isWindows)
		{
			$this->nl(50);
		}
		else
		{
			$this->write("\033[H\033[2J");
		}
	}

	/**
	 * Sytem Beep.
	 *
	 * @access  public
	 * @param   int     $beeps  (optional) Number of system beeps
	 */

	public function beep($beeps = 1)
	{
		$this->write(str_repeat("\x07", $beeps));
	}

	/**
	 * Returns a progress helper.
	 * 
	 * @access  public
	 * @param   int                                               $itemCount   Number of items
	 * @param   int                                               $redrawRate  (optional) Redraw rate
	 * @return  \Symfony\Component\Console\Helper\ProgressHelper
	 */

	public function progress($itemCount, $redrawRate = null)
	{
		$progress = new ProgressHelper();

		// Set redraw frequency

		$progress->setRedrawFrequency(max($redrawRate ?: ceil(0.01 * $itemCount), 1));

		// Set stome styles

		$progress->setFormat(ProgressHelper::FORMAT_VERBOSE);

		$progress->setBarCharacter('<green>=</green>');

		$progress->setEmptyBarCharacter('<red>-</red>');

		$progress->setProgressCharacter('|');

		// Start, display and return progress helper instance

		$progress->start($this, $itemCount);

		$progress->display();

		return $progress;
	}
}