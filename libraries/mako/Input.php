<?php

namespace mako;

use \mako\Arr;
use \mako\Request;

/**
 * Input filtering and helpers.
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
	 * Holds the input array.
	 *
	 * @var array
	 */

	protected $input;

	/**
	 * Holds the PUT method's data.
	 *
	 * @var array
	 */

	protected static $put;

	/**
	 * Holds the DELETE method's data.
	 *
	 * @var array
	 */

	protected static $delete;
	
	/**
	 * Holds all the callback filtering functions that need to be run.
	 *
	 * @var array
	 */
	
	protected $filters = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   array  $input  Array to validate
	 */

	public function __construct(array &$input)
	{
		$this->input =& $input;
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   array          $input  Array to validate
	 * @return  mako\Validate
	 */

	public static function factory(array &$input)
	{
		return new static($input);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Adds a filter to the list of callbacks.
	 *
	 * @access  public
	 * @param   mixed          $field     Field name
	 * @param   callback       $function  Filter function
	 * @return  mako\Validate
	 */
	
	public function filter($field, $function)
	{
		!is_array($function) && $function = array($function);

		$callback['function'] = $function[0];
		$callback['params']   = isset($function[1]) ? $function[1] : array();
		
		if($field === '*')
		{
			foreach(array_keys($this->input) as $field)
			{
				$this->filters[$field][] = $callback;
			}
		}
		else
		{
			$this->filters[$field][] = $callback;
		}
		
		return $this;
	}

	/**
	 * Runs all input filters.
	 *
	 * @access  public
	 * @return  array
	 */

	public function process()
	{
		foreach($this->filters as $field => $filters)
		{
			foreach($filters as $callback)
			{
				$params = array_merge(array($this->input[$field]), $callback['params']);
								
				$this->input[$field] = call_user_func_array($callback['function'], $params);
			}
		}
	}

	/**
	 * Fetch data from the $_GET array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function get($key = null, $default = null)
	{
		return $key === null ? $_GET : Arr::get($_GET, $key, $default);
	}

	/**
	 * Fetch data from the $_POST array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function post($key = null, $default = null)
	{
		return $key === null ? $_POST : Arr::get($_POST, $key, $default);
	}

	/**
	 * Fetch data from the $_COOKIE array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function cookie($key = null, $default = null)
	{
		return $key === null ? $_COOKIE : Arr::get($_COOKIE, $key, $default);
	}

	/**
	 * Fetch data from the $_FILES array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function files($key = null, $default = null)
	{
		return $key === null ? $_FILES : Arr::get($_FILES, $key, $default);
	}

	/**
	 * Fetch data from the $_SERVER array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function server($key = null, $default = null)
	{
		return $key === null ? $_SERVER : Arr::get($_SERVER, $key, $default);
	}

	/**
	 * Fetch data from the $_ENV array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function env($key = null, $default = null)
	{
		return $key === null ? $_ENV : Arr::get($_ENV, $key, $default);
	}

	/**
	 * Fetch data from the $_SESSION array.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function session($key = null, $default = null)
	{
		return $key === null ? $_SESSION : Arr::get($_SESSION, $key, $default);
	}
	
	/**
	 * Returns PUT data.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function put($key = null, $default = null)
	{
		if(static::$put === null)
		{
			static::$put = array();

			parse_str(file_get_contents('php://input'), static::$put);
		}

		return $key === null ? static::$put : Arr::get(static::$put, $key, $default);
	}

	/**
	 * Returns DELETE data.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function delete($key = null, $default = null)
	{
		if(static::$delete === null)
		{
			static::$delete = array();

			parse_str(file_get_contents('php://input'), static::$delete);
		}

		return $key === null ? static::$delete : Arr::get(static::$delete, $key, $default);
	}

	/**
	 * Returns the current request method's data.
	 *
	 * @access  public
	 * @param   string  $key      (optional) Array key
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public static function data($key = null, $default = null)
	{
		return call_user_func('static::' . strtolower(Request::method()), $key, $default);
	}

	/**
	 * Checks if the keys exist in the request method data
	 *
	 * @access  public
	 * @param   mixed    $key     Key or array of keys
	 * @param   string   $method  (optional) Request method
	 * @return  boolean
	 */

	public static function has($key, $method = null)
	{
		$keys = (array) $key;

		$method = strtolower($method ?: Request::method());

		$data = static::$method();

		foreach($keys as $key)
		{
			if(!isset($data[$key]))
			{
				return false;
			}
		}

		return true;
	}
}

/** -------------------- End of file --------------------**/