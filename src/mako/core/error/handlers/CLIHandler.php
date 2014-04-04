<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\error\handlers;

use \Exception;
use \ErrorException;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\RequestException;

/**
 * Web handler.
 * 
 * @author  Frederic G. Østby
 */

class CLIHandler extends \mako\core\error\handlers\Handler implements \mako\core\error\handlers\HandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a detailed error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getDetailedError()
	{
		echo $this->exception->getMessage() . PHP_EOL;
	}

	/**
	 * Retruns a generic error.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getGenericError()
	{
		echo 'An error has occurred while processing your task.' . PHP_EOL;
	}

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 * @param   boolean  $showDetails  (optional) Show error details?
	 * @return  boolean
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