<?php

namespace mako\http\responses;

use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Routes;
use \mako\http\routing\URL;

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

	public function __construct($location, array $routeParams = array(), array $queryParams = array())
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
	 * @param   \mako\http\Request
	 * @param   \mako\http\Response
	 */

	public function send(Request $request, Response $response)
	{
		$response->status($this->status);

		$response->header('Location', $this->location);

		$response->sendHeaders();
	}
}

/** -------------------- End of file -------------------- **/