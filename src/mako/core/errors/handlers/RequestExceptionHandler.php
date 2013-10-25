<?php

namespace mako\core\errors\handlers;

use \Exception;
use \mako\view\View;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\MethodNotAllowedException;

/**
 * RequestException handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class RequestExceptionHandler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Exception.
	 * 
	 * @var Exception
	 */

	protected $exception;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   Exception  $exception  Exception to handle
	 */

	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 */

	public function handle()
	{
		$response = new Response(Request::main(), new View('_mako_.errors.' . $this->exception->getCode()));

		if($this->exception instanceof MethodNotAllowedException)
		{
			$response->header('Allow', implode(', ', $this->exception->getAllowedMethods()));
		}

		$response->status($this->exception->getCode())->send();
	}
}

/** -------------------- End of file -------------------- **/