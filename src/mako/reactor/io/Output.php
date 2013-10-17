<?php

namespace mako\reactor\io;

use \mako\reactor\io\StdErr;
use \Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Reactor output.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Output extends \mako\reactor\io\StdOut
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * StdErr instance.
	 * 
	 * @var \mako\reactor\io\StdErr;
	 */

	protected $stderr;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 */

	public function __construct()
	{
		parent::__construct();

		$this->stderr = new StdErr();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
		if(MAKO_IS_WINDOWS)
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

		$progress->setRedrawFrequency($redrawRate ?: ceil(0.01 * $itemCount));

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

/** -------------------- End of file -------------------- **/