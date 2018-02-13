<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
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
 * @author Frederic G. Østby
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
	 * Command terminator length.
	 *
	 * @var string
	 */
	const CRLF_LENGTH = 2;

	/**
	 * Redis password.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Redis database.
	 *
	 * @var int
	 */
	protected $database;

	/**
	 * Is pipelining enabled?
	 *
	 * @var bool
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
	 * Cluster clients.
	 *
	 * @var array
	 */
	protected $clusterClients = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\redis\Connection $connection Redis connection
	 * @param array                  $options    Options
	 */
	public function __construct(Connection $connection, array $options = [])
	{
		$this->connection = $connection;

		if(!empty($options['password']))
		{
			$this->auth($this->password = $options['password']);
		}

		if(!empty($options['database']))
		{
			$this->select($this->database = $options['database']);
		}
	}

	/**
	 * Creates a cluster client.
	 *
	 * @param  string            $server Server string
	 * @return \mako\redis\Redis
	 */
	protected function createClusterClient(string $server): Redis
	{
		list($server, $port) = explode(':', $server, 2);

		$connection = $this->connection->create($server, $port, $this->connection->isPersistent());

		return new static($connection, ['password' => $this->password, 'database' => $this->database]);
	}

	/**
	 * Gets a cluster client.
	 *
	 * @param  string            $serverInfo Cluster slot and server string
	 * @return \mako\redis\Redis
	 */
	protected function getClusterClient(string $serverInfo): Redis
	{
		list($slot, $server) = explode(' ', $serverInfo, 2);

		if(!isset($this->clusterClients[$server]))
		{
			$this->clusterClients[$server] = $this->createClusterClient($server);
		}

		return $this->clusterClients[$server];
	}

	/**
	 * Handles redis error responses.
	 *
	 * @param  string $response Error response
	 * @return mixed
	 */
	protected function handleErrorResponse(string $response)
	{
		$response = substr($response, 1);

		list($type, $error) = explode(' ', $response, 2);

		switch($type)
		{
			case 'MOVED':
			case 'ASK':
				return $this->getClusterClient($error)->rawCommand($this->connection->getLastCommand());
				break;
			default:
				throw new RedisException(vsprintf('%s.', [$response]));
		}
	}

	/**
	 * Handles a status response.
	 *
	 * @param  string $response Redis response
	 * @return string
	 */
	protected function handleStatusResponse(string $response): string
	{
		return substr($response, 1);
	}

	/**
	 * Handles a integer response.
	 *
	 * @param  string $response Redis response
	 * @return int
	 */
	protected function handleIntegerResponse(string $response): int
	{
		return (int) substr($response, 1);
	}

	/**
	 * Handles a bulk response.
	 *
	 * @param  string      $response Redis response
	 * @return string|null
	 */
	protected function handleBulkResponse(string $response)
	{
		if($response === '$-1')
		{
			return null;
		}

		$length = (int) substr($response, 1);

		return substr($this->connection->read($length + static::CRLF_LENGTH), 0, - static::CRLF_LENGTH);
	}

	/**
	 * Handles a multi-bulk response.
	 *
	 * @param  string     $response Redis response
	 * @return array|null
	 */
	protected function handleMultiBulkResponse(string $response)
	{
		if($response === '*-1')
		{
			return null;
		}

		$data = [];

		$count = (int) substr($response, 1);

		for($i = 0; $i < $count; $i++)
		{
			$data[] = $this->response();
		}

		return $data;
	}

	/**
	 * Returns response from redis server.
	 *
	 * @return mixed
	 */
	protected function response()
	{
		$response = trim($this->connection->readLine());

		switch(substr($response, 0, 1))
		{
			case '-': // error reply
				return $this->handleErrorResponse($response);
				break;
			case '+': // status reply
				return $this->handleStatusResponse($response);
				break;
			case ':': // integer reply
				return $this->handleIntegerResponse($response);
				break;
			case '$': // bulk reply
				return $this->handleBulkResponse($response);
				break;
			case '*': // multi-bulk reply
				return $this->handleMultiBulkResponse($response);
				break;
			default:
				throw new RedisException('Unable to handle server response.');
		}
	}

	/**
	 * Pipeline commands.
	 *
	 * @param  \Closure $pipeline Pipelined commands
	 * @return array
	 */
	public function pipeline(Closure $pipeline): array
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
	 * Executes raw Redis commands and returns the response.
	 *
	 * @param  string $command Command
	 * @return mixed
	 */
	public function rawCommand(string $command)
	{
		$this->connection->write($command);

		return $this->response();
	}

	/**
	 * Builds command from method name.
	 *
	 * @param  string $name Method name
	 * @return array
	 */
	protected function buildCommand(string $name): array
	{
		$command = strtoupper(str_replace('_', ' ', Str::camel2underscored($name)));

		if(strpos($command, ' ') === false)
		{
			return [$command];
		}

		$command = explode(' ', $command, 2);

		if(strpos($command[1], ' ') !== false)
		{
			$command[1] = str_replace(' ', '-', $command[1]);
		}

		return $command;
	}

	/**
	 * Sends command to Redis server and returns response.
	 *
	 * @param  string $name      Command name
	 * @param  array  $arguments Command arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		// Build command

		$arguments = array_merge($this->buildCommand($name), $arguments);

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

			return $this->rawCommand($command);
		}
	}
}
