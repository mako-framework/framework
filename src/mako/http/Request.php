<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http;

use \RuntimeException;

use \mako\security\Signer;
use \mako\utility\Arr;

/**
 * Executes requets.
 *
 * @author  Frederic G. Østby
 */

class Request
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Get data
	 * 
	 * @var array
	 */

	protected $get;

	/**
	 * Post data
	 * 
	 * @var array
	 */

	protected $post;

	/**
	 * Cookie data.
	 * 
	 * @var array
	 */

	protected $cookies;

	/**
	 * File data.
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
	 * Raw request body.
	 * 
	 * @var string
	 */

	protected $body;

	/**
	 * Signer instance.
	 * 
	 * @var \mako\security\Signer
	 */

	protected $signer;

	/**
	 * Parsed request body.
	 * 
	 * @var array
	 */

	protected $parsedBody;

	/**
	 * Request headers.
	 * 
	 * @var array
	 */

	protected $headers = [];
	
	/**
	 * Ip address of the client that made the request.
	 *
	 * @var string
	 */
	
	protected $ip = '127.0.0.1';

	/**
	 * Is this an Ajax request?
	 *
	 * @var boolean
	 */

	protected $isAjax;

	/**
	 * Was the request made using HTTPS?
	 *
	 * @var boolean
	 */

	protected $isSecure;

	/**
	 * Base URL of the request.
	 * 
	 * @var string
	 */

	protected $baseURL;
	
	/**
	 * Holds the request path.
	 *
	 * @var string
	 */

	protected $path;

	/**
	 * Request language.
	 * 
	 * @var string
	 */

	protected $language;

	/**
	 * Request language prefix.
	 * 
	 * @var string
	 */

	protected $languagePrefix;

	/**
	 * Which request method was used?
	 *
	 * @var string
	 */

	protected $method;

	/**
	 * The actual request method that was used.
	 * 
	 * @var string
	 */

	protected $realMethod;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array                  $request  Request data and options
	 * @param   \mako\security\Signer  $signer   (optional) Signer instance used to validate signed cookies
	 */

	public function __construct(array $request = [], Signer $signer = null)
	{
		// Collect request data

		$this->get     = isset($request['GET']) ? $request['GET'] : $_GET;
		$this->post    = isset($request['POST']) ? $request['POST'] : $_POST;
		$this->cookies = isset($request['COOKIE']) ? $request['COOKIE'] : $_COOKIE;
		$this->files   = isset($request['FILES']) ? $request['FILES'] : $_FILES;
		$this->server  = isset($request['SERVER']) ? $request['SERVER'] : $_SERVER;
		$this->body    = isset($request['body']) ? $request['body'] : null;

		// Set the Signer instance

		$this->signer = $signer;

		// Collect the request headers

		$this->headers = $this->collectHeaders();

		// Collect request info

		$this->collectRequestInfo();

		// Set the request path and method

		$languages = isset($request['languages']) ? $request['languages'] : [];

		$this->path = isset($request['path']) ? $request['path'] : $this->determinePath($languages);

		$this->method = isset($request['method']) ? $request['method'] : $this->determineMethod();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Determines the request path.
	 *
	 * @access  protected
	 * @return  string
	 */

	protected function determinePath($languages)
	{
		$path = '/';

		if(isset($this->server['PATH_INFO']))
		{
			$path = $this->server['PATH_INFO'];
		}
		elseif(isset($this->server['REQUEST_URI']))
		{
			if($path = parse_url($this->server['REQUEST_URI'], PHP_URL_PATH))
			{
				// Remove base path from request path

				$basePath = pathinfo($this->server['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if(stripos($path, $basePath) === 0)
				{
					$path = mb_substr($path, mb_strlen($basePath));
				}

				// Remove "/index.php" from path

				if(stripos($path, '/index.php') === 0)
				{
					$path = mb_substr($path, 10);
				}

				$path = rawurldecode($path);
			}
		}

		// Remove the locale segment from the path
			
		foreach($languages as $key => $language)
		{
			if($path === '/' . $key || strpos($path, '/' . $key . '/') === 0)
			{
				$this->language = $language;

				$this->languagePrefix = $key;

				$path = '/' . ltrim(mb_substr($path, (mb_strlen($key) + 1)), '/');

				break;
			}
		}

		return $path;
	}

	/**
	 * Determines the request method.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function determineMethod()
	{
		$method = 'GET';

		if(isset($this->server['REQUEST_METHOD']))
		{
			$method = strtoupper($this->server['REQUEST_METHOD']);
		}

		if($method === 'POST')
		{
			if(isset($this->post['REQUEST_METHOD_OVERRIDE']))
			{
				$method = $this->post['REQUEST_METHOD_OVERRIDE'];
			}
			elseif(isset($this->server['HTTP_X_HTTP_METHOD_OVERRIDE']))
			{
				$method = $this->server['HTTP_X_HTTP_METHOD_OVERRIDE'];
			}
		}

		return strtoupper($method);
	}

	/**
	 * Returns all the request headers.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function collectHeaders()
	{
		$headers = [];

		foreach($this->server as $key => $value)
		{
			if(strpos($key, 'HTTP_') === 0)
			{
				$headers[substr($key, 5)] = $value;
			}
			elseif(in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE']))
			{
				$headers[$key] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Collects information about the request.
	 * 
	 * @access protected
	 */

	protected function collectRequestInfo()
	{
		// Get the IP address of the client that made the request
		
		if(!empty($this->server['HTTP_X_FORWARDED_FOR']))
		{
			$ip = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
			
			$ip = array_pop($ip);
		}
		elseif(!empty($this->server['HTTP_CLIENT_IP']))
		{
			$ip = $this->server['HTTP_CLIENT_IP'];
		}
		elseif(!empty($this->server['HTTP_X_CLUSTER_CLIENT_IP']))
		{
			$ip = $this->server['HTTP_X_CLUSTER_CLIENT_IP'];
		}
		elseif(!empty($this->server['REMOTE_ADDR']))
		{
			$ip = $this->server['REMOTE_ADDR'];
		}
		
		if(isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
		{
			$this->ip = $ip;
		}
		
		// Is this an Ajax request?

		$this->isAjax = (isset($this->server['HTTP_X_REQUESTED_WITH']) && ($this->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		$this->isSecure = (!empty($this->server['HTTPS']) && filter_var($this->server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;

		// Get the real request method that was used

		$this->realMethod = isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']) : 'GET';
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
			switch($this->header('content-type'))
			{
				case 'application/x-www-form-urlencoded':
					parse_str($this->body(), $this->parsedBody);
					break;
				case 'text/json':
				case 'application/json':
					$this->parsedBody = json_decode($this->body(), true);
					break;
				default:
					$this->parsedBody = [];
			}
			
		}

		return ($key === null) ? $this->parsedBody : Arr::get($this->parsedBody, $key, $default);
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
	 * Fetch signed cookie data.
	 *
	 * @access  public
	 * @param   string  $name     (optional) Cookie name
	 * @param   mixed   $default  (optional) Default value
	 * @return  string
	 */

	public function signedCookie($name = null, $default = null)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to read signed cookies.", [__METHOD__]));
		}

		if(isset($this->cookies[$name]) && ($value = $this->signer->validate($this->cookies[$name])) !== false)
		{
			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Fetch unsigned cookie data.
	 *
	 * @access  public
	 * @param   string  $name     (optional) Cookie name
	 * @param   mixed   $default  (optional) Default value
	 * @return  string
	 */

	public function cookie($name = null, $default = null)
	{
		return isset($this->cookies[$name]) ? $this->cookies[$name] : $default;
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
	 * Checks if the keys exist in the data of the current request method.
	 *
	 * @access  public
	 * @param   string   $key  Array key
	 * @return  boolean
	 */

	public function has($key)
	{
		$method = strtolower($this->realMethod);

		return Arr::has($this->$method(), $key);
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
		$method = strtolower($this->realMethod);

		return $this->$method($key, $default);
	}

	/**
	 * Returns request data where keys not in the whitelist have been removed.
	 * 
	 * @access  public
	 * @param   array  $keys      Keys to whitelist
	 * @param   array  $defaults  (optional) Default values
	 * @return  array
	 */

	public function whitelisted(array $keys, array $defaults = [])
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

	public function blacklisted(array $keys, array $defaults = [])
	{
		return array_diff_key($this->data(), array_flip($keys)) + $defaults;
	}

	/**
	 * Returns a request header.
	 * 
	 * @access  public
	 * @param   string  $name     Header name
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public function header($name, $default = null)
	{
		$name = strtoupper(str_replace('-', '_', $name));

		return isset($this->headers[$name]) ? $this->headers[$name] : $default;
	}

	/**
	 * Returns the ip of the client that made the request.
	 *
	 * @access  public
	 * @return  string
	 */
	
	public function ip()
	{
		return $this->ip;
	}

	/**
	 * Returns TRUE if the request was made using Ajax and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isAjax()
	{
		return $this->isAjax;
	}

	/**
	 * Returns TRUE if the request was made using HTTPS and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isSecure()
	{
		return $this->isSecure;
	}

	/**
	 * Returns the base url of the request.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function baseURL()
	{
		if(empty($this->baseURL))
		{
			// Get the protocol

			$protocol = $this->isSecure ? 'https://' : 'http://';

			// Get the server name and port

			if(($host = $this->header('host')) !== null)
			{
				$host = $this->server('SERVER_NAME');

				$port = $this->server('SERVER_PORT');

				if($port !== null && $port != 80)
				{
					$host = $host . ':' . $port;
				}
			}

			// Get the base path

			$path = $this->server('SCRIPT_NAME');

			$path = str_replace(basename($path), '', $path);

			// Put them all together

			$this->baseURL = rtrim($protocol . $host . $path, '/');
		}

		return $this->baseURL;
	}

	/**
	 * Returns the request path.
	 *
	 * @access  public
	 * @return  string
	 */

	public function path()
	{
		return $this->path;
	}

	/**
	 * Returns the request language.
	 *
	 * @access  public
	 * @return  string
	 */

	public function language()
	{
		return $this->language;
	}

	/**
	 * Returns the request language prefix.
	 *
	 * @access  public
	 * @return  string
	 */

	public function languagePrefix()
	{
		return $this->languagePrefix;
	}

	/**
	 * Returns the request method that was used.
	 *
	 * @access  public
	 * @return  string
	 */

	public function method()
	{
		return $this->method;
	}

	/**
	 * Returns the real request method that was used.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function realMethod()
	{
		return $this->realMethod;
	}

	/**
	 * Returns TRUE if the request method has been faked and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function isFaked()
	{
		return $this->realMethod !== $this->method;
	}

	/**
	 * Returns the basic HTTP authentication username or NULL.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function username()
	{
		return $this->server('PHP_AUTH_USER');
	}

	/**
	 * Returns the basic HTTP authentication password or NULL.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function password()
	{
		return $this->server('PHP_AUTH_PW');
	}

	/**
	 * Returns the referer.
	 *
	 * @access  public
	 * @param   string  $default  (optional) Value to return if no referer is set
	 * @return  string
	 */

	public function referer($default = '')
	{
		return $this->header('referer', $default);
	}
}