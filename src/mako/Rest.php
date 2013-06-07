<?php

namespace mako;

/**
 * REST client.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Rest
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * URL.
	 *
	 * @var string
	 */

	protected $url;

	/**
	 * cURL options.
	 *
	 * @var array
	 */

	protected $options = array
	(
		CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Mako Framework; +http://makoframework.com)',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true
	);

	/**
	 * Array holding information about the last transfer.
	 *
	 * @var array
	 */

	protected $info;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $url      URL
	 * @param   array   $options  (optional) cURL options
	 */

	public function __construct($url, array $options = array())
	{
		$this->url     = $url;
		$this->options = $options + $this->options;
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string     $url      URL
	 * @param   array      $options  (optional) cURL options
	 * @return  mako\Rest
	 */

	public static function factory($url, array $options = array())
	{
		return new static($url, $options);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Executes cURL request and returns the response.
	 *
	 * @access  protected
	 * @param   array      $info  $info is filled with info about the request
	 * @return  string
	 */

	protected function execute(&$info)
	{
		// Limit the number of redirects if option is not set

		if(!isset($this->options[CURLOPT_MAXREDIRS]))
		{
			$this->options[CURLOPT_MAXREDIRS] = 5;
		}

		// Execute request

		$handle = curl_init($this->url);

		curl_setopt_array($handle, $this->options);

		$response = curl_exec($handle);

		$info = $this->info = curl_getinfo($handle);

		curl_close($handle);

		return $response;
	}

	/**
	 * Parses HTTP headers and returns a nice array.
	 *
	 * http://php.net/manual/en/function.http-parse-headers.php
	 *
	 * @access  protected
	 * @param   string      $headers  HTTP headers
	 * @return  array
	 */

	protected function parseHeaders($headers)
	{
		if(function_exists('http_parse_headers'))
		{
			return http_parse_headers($headers);
		}

		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));

		$headers = array();

		foreach($fields as $field)
		{
			if(preg_match('/([^:]+): (.+)/m', $field, $match))
			{
				$match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function($matches){ return strtoupper($matches[0]); }, strtolower(trim($match[1])));

				if(isset($headers[$match[1]]))
				{
					$headers[$match[1]] = array($headers[$match[1]], $match[2]);
				}
				else
				{
					$headers[$match[1]] = trim($match[2]);
				}
			}
		}

		return $headers;
	}

	/**
	 * Returns info about the last cURL request.
	 *
	 * @access  public
	 * @param   string  $key  (optional) Array key
	 * @return  mixed
	 */

	public function info($key = null)
	{
		if(empty($this->info))
		{
			return false;
		}

		return ($key === null) ? $this->info : $this->info[$key];
	}

	/**
	 * Sets username and password for HTTP authentication.
	 *
	 * @access  public
	 * @param   string      $username  Username
	 * @param   string      $password  Password
	 * @param   int         $method    (optional) Authenication method
	 * @return  \mako\Rest
	 */

	public function authenticate($username, $password, $method = CURLAUTH_ANY)
	{
		$this->options[CURLOPT_HTTPAUTH] = $method;
		$this->options[CURLOPT_USERPWD]  = $username . ':' . $password;

		return $this;
	}

	/**
	 * Performs a GET request and returns the response.
	 *
	 * @access  public
	 * @param   array   $info  (optional) If $info is provided, then it is filled with info about the request
	 * @return  string
	 */

	public function get(&$info = null)
	{
		return $this->execute($info);
	}

	/**
	 * Performs a HEAD request and returns an array containing the response headers.
	 *
	 * @access  public
	 * @param   array   $info  (optional) If $info is provided, then it is filled with info about the request
	 * @return  array
	 */

	public function head(&$info = null)
	{
		$this->options[CURLOPT_HEADER] = true;
		$this->options[CURLOPT_NOBODY] = true;

		return $this->parseHeaders($this->execute($info));
	}

	/**
	 * Performs a POST request and returns the response.
	 *
	 * @access  public
	 * @param   array    $data       (optional) Post data
	 * @param   boolean  $multipart  (optional) True to send data as multipart/form-data and false to send as application/x-www-form-urlencoded
	 * @param   array    $info       (optional) If $info is provided, then it is filled with info about the request
	 * @return  string
	 */

	public function post(array $data = array(), $multipart = false, &$info = null)
	{
		$this->options[CURLOPT_POST]       = true;
		$this->options[CURLOPT_POSTFIELDS] = ($multipart === true) ? $data : http_build_query($data);

		return $this->execute($info);
	}

	/**
	 * Performs a PUT request and returns the response.
	 *
	 * @access  public
	 * @param   mixed   $data  Put data
	 * @param   array   $info  (optional) If $info is provided, then it is filled with info about the request
	 * @return  string
	 */

	public function put($data, &$info = null)
	{
		$this->options[CURLOPT_CUSTOMREQUEST] = 'PUT';

		if(is_array($data))
		{
			$data = http_build_query($data);
		}

		!isset($this->options[CURLOPT_HTTPHEADER]) && $this->options[CURLOPT_HTTPHEADER] = array();

		$this->options[CURLOPT_HTTPHEADER] = array_merge($this->options[CURLOPT_HTTPHEADER], array('Content-Length: ' . strlen($data)));

		$this->options[CURLOPT_POSTFIELDS] = $data;

		return $this->execute($info);
	}

	/**
	 * Performs a DELETE request and returns the response.
	 *
	 * @access  public
	 * @param   array   $info  (optional) If $info is provided, then it is filled with info about the request
	 * @return  string
	 */

	public function delete(&$info = null)
	{
		$this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';

		return $this->execute($info);
	}
}

/** -------------------- End of file -------------------- **/