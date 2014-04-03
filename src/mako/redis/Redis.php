<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\redis;

use \Closure;
use \RuntimeException;

use \mako\utility\Str;

/**
 * Redis client based on the protocol specification at http://redis.io/topics/protocol.
 *
 * @author  Frederic G. Østby
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

	protected $commands = [];

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
	 * @param   array   $configuration  Redis configuration
	 */

	public function __construct(array $configuration)
	{
		$this->connect($configuration);
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
	 * @param   array     $configuration  Redis configuration
	 * @return  resource
	 */

	protected function connect($configuration)
	{
		$this->connection = @fsockopen('tcp://' . $configuration['host'], $configuration['port'], $errNo, $errStr);

		if(!$this->connection)
		{
			throw new RuntimeException(vsprintf("%s(): %s", [__METHOD__, $errStr]));
		}

		if(!empty($configuration['password']))
		{
			$this->auth($configuration['password']);
		}

		if(!empty($configuration['database']) && $configuration['database'] !== 0)
		{
			$this->select($configuration['database']);
		}

		return $this->connection;
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
				throw new RuntimeException(vsprintf("%s(): %s.", [__METHOD__, substr($response, 5)]));
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

				$data = [];

				$count = substr($response, 1);

				for($i = 0; $i < $count; $i++)
				{
					$data[] = $this->response();
				}

				return $data;
				break;
			default:
				throw new RuntimeException(vsprintf("%s(): Unable to handle server response.", [__METHOD__]));
		}
	}

	/**
	 * Pipeline commands.
	 *
	 * @access  public
	 * @param   \Closure  $pipeline  Pipelined commands
	 * @return  array
	 */

	public function pipeline(Closure $pipeline)
	{
		// Enable pipelining

		$this->pipelined = true;

		// Build commands

		$pipeline($this);

		// Send all commands to server and fetch responses

		$responses = [];

		$commands = count($this->commands);

		fwrite($this->connection, implode('', $this->commands));

		for($i = 0; $i < $commands; $i++)
		{
			$responses[] = $this->response();
		}

		// Reset pipelining

		$this->commands = [];

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
		
		$arguments = array_merge(explode(' ', strtoupper(str_replace('_', ' ', Str::camel2underscored($name)))), $arguments);

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
}