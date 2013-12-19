<?php

namespace mako\http\responses;

use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Routes;
use \mako\http\routing\URL;
use \mako\session\Session;

/**
 * Redirect response.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

Class Redirect implements \mako\http\responses\ResponseContainerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Location.
	 * 
	 * @var string
	 */

	protected $location;

	/**
	 * Status code.
	 * 
	 * @var int
	 */

	protected $status = 302;

	/**
	 * Flash the request data?
	 * 
	 * @var boolean
	 */

	protected $flashRequestData = false;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $location     Location
	 * @param   array   $routeParams  (optional) Route parameters
	 * @param   array   $queryParams  (optional) Query parameters
	 */

	public function __construct($location, array $routeParams = [], array $queryParams = [])
	{
		if(strpos($location, '://') === false)
		{
			if(Routes::hasNamedRoute($location))
			{
				$location = URL::toRoute($location, $routeParams, $queryParams, '&');
			}
			else
			{
				$location = URL::to($location, $queryParams, '&');
			}
		}

		$this->location = $location;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets flash data that will be available on the next request.
	 * 
	 * @access  public
	 * @param   string                         $key   Session flash key
	 * @param   mixed                          $data  Flash data
	 * @return  \mako\http\responses\Redirect
	 */

	public function flash($key, $data)
	{
		Session::flash($key, $data);

		return $this;
	}

	/**
	 * Will flash the request data if called.
	 * 
	 * @access  public
	 * @return  \mako\http\responses\Redirect
	 */

	public function flashRequestData()
	{
		$this->flashRequestData = true;

		return $this;
	}

	/**
	 * Sets the status code.
	 * 
	 * @access  public
	 * @param   int                            $status  Status code
	 * @return  \mako\http\responses\Redirect
	 */

	public function status($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Sends the response.
	 * 
	 * @access  public
	 * @param   \mako\http\Request   $request  Request instance
	 * @param   \mako\http\Response  $response  Response instance
	 */

	public function send(Request $request, Response $response)
	{
		// Flash request data?

		if($this->flashRequestData)
		{
			$this->flash('mako:request-data', $request->data());
		}

		// Set status and location header

		$response->status($this->status);

		$response->header('Location', $this->location);

		// Send headers

		$response->sendHeaders();
	}
}

/** -------------------- End of file -------------------- **/