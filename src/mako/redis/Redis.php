<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\redis;

use Closure;

use mako\redis\RedisException;
use mako\utility\Str;

/**
 * Redis client.
 *
 * Based on the protocol specification at http://redis.io/topics/protocol.
 *
 * @author  Frederic G. Østby
 */

class Redis
{
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
	 * Redis connection.
	 *
	 * @var \mako\redis\Connection
	 */

	protected $connection;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\redis\Connection  $connection  Redis connection
	 * @param   array                   $options     Options
	 */

	public function __construct(Connection $connection, array $options = [])
	{
		$this->connection = $connection;

		if(!empty($options['password']))
		{
			$this->auth($options['password']);
		}

		if(!empty($options['database']) && $options['database'] !== 0)
		{
			$this->select($options['database']);
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
		$response = trim($this->connection->gets());

		switch(substr($response, 0, 1))
		{
			case '-': // error reply
				throw new RedisException(vsprintf("%s(): %s.", [__METHOD__, substr($response, 1)]));
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

				return substr($this->connection->read($length + strlen(static::CRLF)), 0, - strlen(static::CRLF));
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
				throw new RedisException(vsprintf("%s(): Unable to handle server response.", [__METHOD__]));
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

		$this->connection->write(implode('', $this->commands));

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

			$this->connection->write($command);

			return $this->response();
		}
	}
}