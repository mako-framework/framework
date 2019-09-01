<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Closure;
use mako\utility\Str;

use function array_merge;
use function count;
use function explode;
use function implode;
use function str_replace;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;
use function trim;
use function vsprintf;

/**
 * Redis client.
 *
 * @author Frederic G. Østby
 *
 * @see http://redis.io/topics/protocol Redis protocol specification.
 *
 * @method mixed append()
 * @method mixed auth()
 * @method mixed bgrewriteaof()
 * @method mixed bgsave()
 * @method mixed bitcount()
 * @method mixed bitfield()
 * @method mixed bitop()
 * @method mixed bitpos()
 * @method mixed blpop()
 * @method mixed brpop()
 * @method mixed brpoplpush()
 * @method mixed bzpopmin()
 * @method mixed bzpopmax()
 * @method mixed clientId()
 * @method mixed clientKill()
 * @method mixed clientList()
 * @method mixed clientGetname()
 * @method mixed clientPause()
 * @method mixed clientReply()
 * @method mixed clientSetname()
 * @method mixed clientUnblock()
 * @method mixed clusterAddslots()
 * @method mixed clusterCountFailureReports()
 * @method mixed clusterCountkeysinslot()
 * @method mixed clusterDelslots()
 * @method mixed clusterFailover()
 * @method mixed clusterForget()
 * @method mixed clusterGetkeysinslot()
 * @method mixed clusterInfo()
 * @method mixed clusterKeyslot()
 * @method mixed clusterMeet()
 * @method mixed clusterNodes()
 * @method mixed clusterReplicate()
 * @method mixed clusterReset()
 * @method mixed clusterSaveconfig()
 * @method mixed clusterSetConfigEpoch()
 * @method mixed clusterSetslot()
 * @method mixed clusterSlaves()
 * @method mixed clusterReplicas()
 * @method mixed clusterSlots()
 * @method mixed command()
 * @method mixed commandCount()
 * @method mixed commandGetkeys()
 * @method mixed commandInfo()
 * @method mixed configGet()
 * @method mixed configRewrite()
 * @method mixed configSet()
 * @method mixed configResetstat()
 * @method mixed dbsize()
 * @method mixed debugObject()
 * @method mixed debugSegfault()
 * @method mixed decr()
 * @method mixed decrby()
 * @method mixed del()
 * @method mixed discard()
 * @method mixed dump()
 * @method mixed echo()
 * @method mixed eval()
 * @method mixed evalsha()
 * @method mixed exec()
 * @method mixed exists()
 * @method mixed expire()
 * @method mixed expireat()
 * @method mixed flushall()
 * @method mixed flushdb()
 * @method mixed geoadd()
 * @method mixed geohash()
 * @method mixed geopos()
 * @method mixed geodist()
 * @method mixed georadius()
 * @method mixed georadiusbymember()
 * @method mixed get()
 * @method mixed getbit()
 * @method mixed getrange()
 * @method mixed getset()
 * @method mixed hdel()
 * @method mixed hexists()
 * @method mixed hget()
 * @method mixed hgetall()
 * @method mixed hincrby()
 * @method mixed hincrbyfloat()
 * @method mixed hkeys()
 * @method mixed hlen()
 * @method mixed hmget()
 * @method mixed hmset()
 * @method mixed hset()
 * @method mixed hsetnx()
 * @method mixed hstrlen()
 * @method mixed hvals()
 * @method mixed incr()
 * @method mixed incrby()
 * @method mixed incrbyfloat()
 * @method mixed info()
 * @method mixed keys()
 * @method mixed lastsave()
 * @method mixed lindex()
 * @method mixed linsert()
 * @method mixed llen()
 * @method mixed lpop()
 * @method mixed lpush()
 * @method mixed lpushx()
 * @method mixed lrange()
 * @method mixed lrem()
 * @method mixed lset()
 * @method mixed ltrim()
 * @method mixed memoryDoctor()
 * @method mixed memoryHelp()
 * @method mixed memoryMallocStats()
 * @method mixed memoryPurge()
 * @method mixed memoryStats()
 * @method mixed memoryUsage()
 * @method mixed mget()
 * @method mixed migrate()
 * @method mixed move()
 * @method mixed mset()
 * @method mixed msetnx()
 * @method mixed multi()
 * @method mixed object()
 * @method mixed persist()
 * @method mixed pexpire()
 * @method mixed pexpireat()
 * @method mixed pfadd()
 * @method mixed pfcount()
 * @method mixed pfmerge()
 * @method mixed ping()
 * @method mixed psetex()
 * @method mixed pubsub()
 * @method mixed pttl()
 * @method mixed publish()
 * @method mixed quit()
 * @method mixed randomkey()
 * @method mixed readonly()
 * @method mixed readwrite()
 * @method mixed rename()
 * @method mixed renamenx()
 * @method mixed restore()
 * @method mixed role()
 * @method mixed rpop()
 * @method mixed rpoplpush()
 * @method mixed rpush()
 * @method mixed rpushx()
 * @method mixed sadd()
 * @method mixed save()
 * @method mixed scard()
 * @method mixed scriptDebug()
 * @method mixed scriptExists()
 * @method mixed scriptFlush()
 * @method mixed scriptKill()
 * @method mixed scriptLoad()
 * @method mixed sdiff()
 * @method mixed sdiffstore()
 * @method mixed select()
 * @method mixed set()
 * @method mixed setbit()
 * @method mixed setex()
 * @method mixed setnx()
 * @method mixed setrange()
 * @method mixed shutdown()
 * @method mixed sinter()
 * @method mixed sinterstore()
 * @method mixed sismember()
 * @method mixed slaveof()
 * @method mixed replicaof()
 * @method mixed slowlog()
 * @method mixed smembers()
 * @method mixed smove()
 * @method mixed sort()
 * @method mixed spop()
 * @method mixed srandmember()
 * @method mixed srem()
 * @method mixed strlen()
 * @method mixed sunion()
 * @method mixed sunionstore()
 * @method mixed swapdb()
 * @method mixed sync()
 * @method mixed time()
 * @method mixed touch()
 * @method mixed ttl()
 * @method mixed type()
 * @method mixed unlink()
 * @method mixed unwatch()
 * @method mixed wait()
 * @method mixed watch()
 * @method mixed zadd()
 * @method mixed zcard()
 * @method mixed zcount()
 * @method mixed zincrby()
 * @method mixed zinterstore()
 * @method mixed zlexcount()
 * @method mixed zpopmax()
 * @method mixed zpopmin()
 * @method mixed zrange()
 * @method mixed zrangebylex()
 * @method mixed zrevrangebylex()
 * @method mixed zrangebyscore()
 * @method mixed zrank()
 * @method mixed zrem()
 * @method mixed zremrangebylex()
 * @method mixed zremrangebyrank()
 * @method mixed zremrangebyscore()
 * @method mixed zrevrange()
 * @method mixed zrevrangebyscore()
 * @method mixed zrevrank()
 * @method mixed zscore()
 * @method mixed zunionstore()
 * @method mixed scan()
 * @method mixed sscan()
 * @method mixed hscan()
 * @method mixed zscan()
 * @method mixed xinfo()
 * @method mixed xadd()
 * @method mixed xtrim()
 * @method mixed xdel()
 * @method mixed xrange()
 * @method mixed xrevrange()
 * @method mixed xlen()
 * @method mixed xread()
 * @method mixed xgroup()
 * @method mixed xreadgroup()
 * @method mixed xack()
 * @method mixed xclaim()
 * @method mixed xpending()
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
	 * Last command.
	 *
	 * @var string
	 */
	protected $lastCommand;

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
	 * Returns the connection.
	 *
	 * @return \mako\redis\Connection
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * Returns the cluster clients.
	 *
	 * @return array
	 */
	public function getClusterClients(): array
	{
		return $this->clusterClients;
	}

	/**
	 * Creates a cluster client.
	 *
	 * @param  string            $server Server string
	 * @return \mako\redis\Redis
	 */
	protected function createClusterClient(string $server): Redis
	{
		[$server, $port] = explode(':', $server, 2);

		$isPersistent = $this->connection->isPersistent();

		$timeout = $this->connection->getTimeout();

		$name = $this->connection->getName();

		$connection = new Connection($server, $port, $isPersistent, $timeout, $name);

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
		[, $server] = explode(' ', $serverInfo, 2);

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

		[$type, $error] = explode(' ', $response, 2);

		switch($type)
		{
			case 'MOVED':
			case 'ASK':
				return $this->getClusterClient($error)->sendCommandAndGetResponse($this->lastCommand);
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
	protected function handleBulkResponse(string $response): ?string
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
	protected function handleMultiBulkResponse(string $response): ?array
	{
		if($response === '*-1')
		{
			return null;
		}

		$data = [];

		$count = (int) substr($response, 1);

		for($i = 0; $i < $count; $i++)
		{
			$data[] = $this->getResponse();
		}

		return $data;
	}

	/**
	 * Returns response from redis server.
	 *
	 * @return mixed
	 */
	protected function getResponse()
	{
		$response = trim($this->connection->readLine());

		switch(substr($response, 0, 1))
		{
			case '-': // error reply
				return $this->handleErrorResponse($response);
			case '+': // status reply
				return $this->handleStatusResponse($response);
			case ':': // integer reply
				return $this->handleIntegerResponse($response);
			case '$': // bulk reply
				return $this->handleBulkResponse($response);
			case '*': // multi-bulk reply
				return $this->handleMultiBulkResponse($response);
			default:
				throw new RedisException(vsprintf('Unable to handle server response [ %s ].', [$response]));
		}
	}

	/**
	 * Builds command.
	 *
	 * @param  string $name      Camel cased or snake cased command name
	 * @param  array  $arguments Command arguments
	 * @return array
	 */
	protected function buildCommand(string $name, array $arguments = []): string
	{
		$command = strtoupper(str_replace('_', ' ', Str::camel2underscored($name)));

		if(strpos($command, ' ') === false)
		{
			$command = [$command];
		}
		else
		{
			$command = explode(' ', $command, 2);

			if(strpos($command[1], ' ') !== false)
			{
				$command[1] = str_replace(' ', '-', $command[1]);
			}
		}

		$pieces = array_merge($command, $arguments);

		$command = '*' . count($pieces) . static::CRLF;

		foreach($pieces as $piece)
		{
			$command .= '$' . strlen($piece) . static::CRLF . $piece . static::CRLF;
		}

		return $command;
	}

	/**
	 * Sends command to server.
	 *
	 * @param string $command Command
	 */
	protected function sendCommand(string $command): void
	{
		$this->lastCommand = $command;

		$this->connection->write($command);
	}

	/**
	 * Executes raw Redis commands and returns the response.
	 *
	 * @param  string $command Command
	 * @return mixed
	 */
	protected function sendCommandAndGetResponse(string $command)
	{
		$this->sendCommand($command);

		return $this->getResponse();
	}

	/**
	 * Subscribes to the chosen channels.
	 *
	 * @param array    $channels    Channels
	 * @param \Closure $subscriber  Subscriber
	 * @param array    $accept      Message types to accept
	 * @param string   $subscribe   Subscribe command
	 * @param string   $unsubscribe Unsubscribe command
	 */
	protected function subscribe(array $channels, Closure $subscriber, array $accept, string $subscribe, string $unsubscribe): void
	{
		$this->sendCommand($this->buildCommand($subscribe, $channels));

		while(true)
		{
			$message = new Message($this->getResponse());

			if(in_array($message->getType(), $accept) && $subscriber($message) === false)
			{
				break;
			}

			unset($message);
		}

		foreach($channels as $channel)
		{
			$this->sendCommandAndGetResponse($this->buildCommand($unsubscribe, [$channel]));
		}
	}

	/**
	 * Subscribes to the chosen channels.
	 *
	 * @param array    $channels   Channels
	 * @param \Closure $subscriber Subscriber
	 * @param array    $accept     Message types to accept
	 */
	public function subscribeTo(array $channels, Closure $subscriber, array $accept = ['message']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'subscribe', 'unsubscribe');
	}

	/**
	 * Subscribes to the chosen channels.
	 *
	 * @param array    $channels   Channels
	 * @param \Closure $subscriber Subscriber
	 * @param array    $accept     Message types to accept
	 */
	public function subscribeToPattern(array $channels, Closure $subscriber, array $accept = ['pmessage']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'psubscribe', 'punsubscribe');
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

		$this->sendCommand(implode('', $this->commands));

		for($i = 0; $i < $commands; $i++)
		{
			$responses[] = $this->getResponse();
		}

		// Reset pipelining

		$this->commands = [];

		$this->pipelined = false;

		// Return array of responses

		return $responses;
	}

	/**
	 * Sends command to Redis server and returns response
	 * or appends command to the pipeline and returns the client.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		$command = $this->buildCommand($name, $arguments);

		if($this->pipelined)
		{
			// Pipeline commands

			$this->commands[] = $command;

			return $this;
		}

		// Send command to server and return response

		return $this->sendCommandAndGetResponse($command);
	}
}
