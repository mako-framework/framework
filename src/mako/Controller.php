<?php

namespace mako;

use \mako\Request;
use \mako\Response;

/**
 * Base controller that all application controllers must extend.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Controller
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Is this a restful controller?
	 * 
	 * @var boolean
	 */

	const RESTFUL = false;

	/**
	 * Holds the request object that loaded the controller.
	 *
	 * @var mako\Request
	 */

	protected $request;
	
	/**
	 * Holds request response object.
	 *
	 * @var mako\Response
	 */
	
	protected $response;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\Request   $request   A request object
	 * @param   \mako\Response  $response  A response object
	 */

	public function __construct(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * This method runs before the action.
	 *
	 * @access  public
	 */

	public function before()
	{

	}

	/**
	 * This method runs after the action.
	 *
	 * @access  public
	 */

	public function after()
	{

	}
}

/** -------------------- End of file -------------------- **/