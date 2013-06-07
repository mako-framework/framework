<?php

namespace mako;

use \Closure;
use \mako\Config;
use \mako\String;
use \RuntimeException;

/**
 * Redis client based on protocol specification at http://redis.io/topics/protocol.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Redis
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Command terminator.
	 *
	 * @var string
	 */

	const CRLF = "\r\n";

	/**
	 * Holds the configuration.
	 *
	 * @var array
	 */

	protected $config;

	/**
	 * Is pipelining enabled?
	 *
	 * @var boolean
	 */

	protected $pipelined = false;

	/**
	 * Pipelined commands.
	 *
	 * @var array
	 */

	protected $commands = array();

	/**
	 * Socket connection.
	 *
	 * @var resource
	 */

	protected $connection;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $name  (optional) Redis configuration name
	 */

	public function __construct($name = null)
	{
		$config = Config::get('redis');

		$name = ($name === null) ? $config['default'] : $name;

		if(isset($config['configurations'][$name]) === false)
		{
			throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the redis configuration.", array(__METHOD__, $name)));
		}

		$this->config = $config['configurations'][$name];

		$this->connect();
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string       $name  (optional) Redis configuration name
	 * @return  \mako\Redis
	 */

	public static function factory($name = null)
	{
		return new static($name);
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		$this->disconnect();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Connects to the Redis server.
	 *
	 * @access  protected
	 */

	protected function connect()
	{
		$this->connection = @fsockopen('tcp://' . $this->config['host'], $this->config['port'], $errNo, $errStr);

		if(!$this->connection)
		{
			throw new RuntimeException(vsprintf("%s(): %s", array(__METHOD__, $errStr)));
		}

		if(!empty($this->config['password']))
		{
			$this->auth($this->config['password']);
		}

		if(!empty($this->config['database']) && $this->config['database'] !== 0)
		{
			$this->select($this->config['database']);
		}
	}

	/**
	 * Closes connection to the Redis server.
	 *
	 * @access  protected
	 */

	protected function disconnect()
	{
		if(is_resource($this->connection))
		{
			fclose($this->connection);	
		}
	}

	/**
	 * Returns response from redis server.
	 *
	 * @access  protected
	 * @return  mixed
	 */

	protected function response()
	{
		$response = trim(fgets($this->connection));

		switch(substr($response, 0, 1))
		{
			case '-': // error reply
				throw new RuntimeException(vsprintf("%s(): %s.", array(__METHOD__, substr($response, 5))));
			break;
			case '+': // status reply
				return trim(substr($response, 1));
			break;
			case ':': // integer reply
				return (int) trim(substr($response, 1));
			break;
			case '$': // bulk reply
				if($response === '$-1')
				{
					return null;
				}

				$length = (int) substr($response, 1);

				return substr(fread($this->connection, $length + strlen(static::CRLF)), 0, - strlen(static::CRLF));
			break;
			case '*': // multi-bulk reply
				if($response === '*-1')
				{
					return null;
				}

				$data = array();

				$count = substr($response, 1);

				for($i = 0; $i < $count; $i++)
				{
					$data[] = $this->response();
				}

				return $data;
			break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to handle server response.", array(__METHOD__)));
		}
	}

	/**
	 * Pipeline commands.
	 *
	 * @access  public
	 * @param   Closure  $pipeline  Pipelined commands
	 * @return  array
	 */

	public function pipeline(Closure $pipeline)
	{
		// Enable pipelining

		$this->pipelined = true;

		// Build commands

		$pipeline($this);

		// Send all commands to server and fetch responses

		$responses = array();

		$commands = count($this->commands);

		fwrite($this->connection, implode('', $this->commands));

		for($i = 0; $i < $commands; $i++)
		{
			$responses[] = $this->response();
		}

		// Reset pipelining

		$this->commands = array();

		$this->pipelined = false;

		// Return array of responses

		return $responses;
	}

	/**
	 * Sends command to Redis server and returns response.
	 *
	 * @access  public
	 * @param   string  $name       Command name
	 * @param   array   $arguments  Command arguments
	 * @return  mixed  
	 */

	public function __call($name, $arguments)
	{
		// Build command
		
		$arguments = array_merge(explode(' ', strtoupper(str_replace('_', ' ', String::camel2underscored($name)))), $arguments);

		$command = '*' . count($arguments) . static::CRLF;

		foreach($arguments as $argument)
		{
			$command .= '$' . strlen($argument) . static::CRLF . $argument . static::CRLF;
		}

		if($this->pipelined)
		{
			// Pipeline commands

			$this->commands[] = $command;

			return $this;
		}
		else
		{
			// Send command to server and return response

			fwrite($this->connection, $command);

			return $this->response();
		}
	}

	/**
	 * Magic shortcut to the default redis configuration.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::factory(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/