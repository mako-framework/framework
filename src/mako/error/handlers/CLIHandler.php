<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use \mako\error\handlers\Handler;

/**
 * CLI handler.
 * 
 * @author  Frederic G. Østby
 */

class CLIHandler extends Handler
{
	/**
	 * Returns a detailed error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getDetailedError()
	{
		fwrite(STDERR, $this->exception->getMessage() . PHP_EOL . $this->exception->getTraceAsString() . PHP_EOL);
	}

	/**
	 * Retruns a generic error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getGenericError()
	{
		fwrite(STDERR, 'An error has occurred while processing your task.' . PHP_EOL);
	}

	/**
	 * {@inheritdoc}
	 */

	public function handle($showDetails = true)
	{
		// Set the response body

		if($showDetails)
		{
			$this->getDetailedError();
		}
		else
		{
			$this->getGenericError();
		}

		// Return false to stop further error handling

		return false;
	}
}