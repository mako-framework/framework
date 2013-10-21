<?php

namespace mako\http;

use \mako\utility\Arr;
use \mako\http\Request;

/**
 * HTTP input.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Input
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Request.
	 * 
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Get parameters.
	 * 
	 * @var array
	 */

	protected $get;

	/**
	 * Post parameters.
	 * 
	 * @var array
	 */

	protected $post;

	/**
	 * Cookies.
	 * 
	 * @var array
	 */

	protected $cookies;

	/**
	 * Files.
	 * 
	 * @var array
	 */

	protected $files;

	/**
	 * Server info.
	 * 
	 * @var array
	 */

	protected $server;

	/**
	 * Request body.
	 * 
	 * @var array
	 */

	protected $body;

	/**
	 * Parsed body.
	 * 
	 * @var array
	 */

	protected $parsedBody;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access public
	 * @param  \mako\http\Request  $request  Request object
	 * @param  array               $get      (optional) GET parameters
	 * @param  array               $post     (optional) POST parameters
	 * @param  array               $cookies  (optional) Cookies
	 * @param  array               $files    (optional) Files
	 * @param  array               $server   (optional) Server info
	 * @param  string              $body     (optional) Request body
	 */

	public function __construct(Request $request, array $get = array(), array $post = array(), array $cookies = array(), array $files = array(), array $server = array(), $body = null)
	{
		$this->request = $request;

		$this->get = $get ?: $_GET;

		$this->post = $post ?: $_POST;

		$this->cookies = $cookies ?: $_COOKIE;

		$this->files = $files ?: $_FILES;

		$this->server = $server ?: $_SERVER;

		$this->body = $body;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Parses the request body and returns the chosen value.
	 * 
	 * @access  protected
	 * @param   string  $key      Array key
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	protected function getParsed($key, $default)
	{
		if($this->parsedBody === null)
		{
			switch($this->request->header('content-type'))
			{
				case 'application/x-www-form-urlencoded':
					parse_str($this->body(), $this->parsedBody);
				break;
				case 'text/json':
				case 'application/json':
					$this->parsedBody = json_decode($this->body(), true);
				break;
				default:
					$this->parsedBody = array();
			}
			
		}

		return ($key === null) ? $this->parsedBody : Arr::get($this->parsedBody, $key, $default);
	}

	/**
	 * Returns the raw request body.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function body()
	{
		if($this->body === null)
		{
			$this->body = file_get_contents('php://input');
		}

		return $this->body;
	}

	/**
	 * Fetch data from the GET parameters.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function get($key = null, $default = null)
	{
		return ($key === null) ? $this->get : Arr::get($this->get, $key, $default);
	}

	/**
	 * Fetch data from the POST parameters.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function post($key = null, $default = null)
	{
		return ($key === null) ? $this->post : Arr::get($this->post, $key, $default);
	}

	/**
	 * Fetch data from the PUT parameters.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function put($key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch data from the PATCH parameters.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function patch($key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch data from the DELETE parameters.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function delete($key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch cookie data.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function cookie($key = null, $default = null)
	{
		return ($key === null) ? $this->cookies : Arr::get($this->cookies, $key, $default);
	}

	/**
	 * Fetch file data.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function file($key = null, $default = null)
	{
		return ($key === null) ? $this->files : Arr::get($this->files, $key, $default);
	}

	/**
	 * Fetch server info.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function server($key = null, $default = null)
	{
		return ($key === null) ? $this->server : Arr::get($this->server, $key, $default);
	}

	/**
	 * Fetch data the current request method.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function data($key = null, $default = null)
	{
		$method = strtolower($this->request->realMethod());

		return $this->$method($key, $default);
	}

	/**
	 * Checks if the keys exist in the data of the current request method.
	 *
	 * @access  public
	 * @param   string   $key     Array key
	 * @param   string   $method  (optional) Request method
	 * @return  boolean
	 */

	public function has($key, $method = null)
	{
		$method = strtolower($this->request->realMethod());

		return Arr::has($this->$method(), $key);
	}

	/**
	 * Returns request data where keys not in the whitelist have been removed.
	 * 
	 * @access  public
	 * @param   array  $keys      Keys to whitelist
	 * @param   array  $defaults  (optional) Default values
	 * @return  array
	 */

	public function whitelist(array $keys, array $defaults = array())
	{
		return array_intersect_key($this->data(), array_flip($keys)) + $defaults;
	}

	/**
	 * Returns request data where keys in the blacklist have been removed.
	 * 
	 * @access  public
	 * @param   array  $keys      Keys to whitelist
	 * @param   array  $defaults  (optional) Default values
	 * @return  array
	 */

	public function blacklist(array $keys, array $defaults = array())
	{
		return array_diff_key($this->data(), array_flip($keys)) + $defaults;
	}
}

/** -------------------- End of file -------------------- **/