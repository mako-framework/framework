<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Closure;
use mako\redis\exceptions\RedisException;
use mako\utility\Str;

use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
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
 * @see http://redis.io/topics/protocol Redis protocol specification.
 * @see https://github.com/antirez/RESP3/blob/master/spec.md Redis protocol specification.
 *
 * @method mixed aclLoad(...$arguments)
 * @method mixed aclSave(...$arguments)
 * @method mixed aclList(...$arguments)
 * @method mixed aclUsers(...$arguments)
 * @method mixed aclGetuser(...$arguments)
 * @method mixed aclSetuser(...$arguments)
 * @method mixed aclDeluser(...$arguments)
 * @method mixed aclCat(...$arguments)
 * @method mixed aclGenpass(...$arguments)
 * @method mixed aclWhoami(...$arguments)
 * @method mixed aclLog(...$arguments)
 * @method mixed aclHelp(...$arguments)
 * @method mixed append(...$arguments)
 * @method mixed auth(...$arguments)
 * @method mixed bgrewriteaof(...$arguments)
 * @method mixed bgsave(...$arguments)
 * @method mixed bitcount(...$arguments)
 * @method mixed bitfield(...$arguments)
 * @method mixed bitop(...$arguments)
 * @method mixed bitpos(...$arguments)
 * @method mixed blpop(...$arguments)
 * @method mixed brpop(...$arguments)
 * @method mixed brpoplpush(...$arguments)
 * @method mixed bzpopmin(...$arguments)
 * @method mixed bzpopmax(...$arguments)
 * @method mixed clientCaching(...$arguments)
 * @method mixed clientId(...$arguments)
 * @method mixed clientKill(...$arguments)
 * @method mixed clientList(...$arguments)
 * @method mixed clientGetname(...$arguments)
 * @method mixed clientGetredir(...$arguments)
 * @method mixed clientPause(...$arguments)
 * @method mixed clientReply(...$arguments)
 * @method mixed clientSetname(...$arguments)
 * @method mixed clientTracking(...$arguments)
 * @method mixed clientUnblock(...$arguments)
 * @method mixed clusterAddslots(...$arguments)
 * @method mixed clusterBumpepoch(...$arguments)
 * @method mixed clusterCountFailureReports(...$arguments)
 * @method mixed clusterCountkeysinslot(...$arguments)
 * @method mixed clusterDelslots(...$arguments)
 * @method mixed clusterFailover(...$arguments)
 * @method mixed clusterFlushslots(...$arguments)
 * @method mixed clusterForget(...$arguments)
 * @method mixed clusterGetkeysinslot(...$arguments)
 * @method mixed clusterInfo(...$arguments)
 * @method mixed clusterKeyslot(...$arguments)
 * @method mixed clusterMeet(...$arguments)
 * @method mixed clusterMyid(...$arguments)
 * @method mixed clusterNodes(...$arguments)
 * @method mixed clusterReplicate(...$arguments)
 * @method mixed clusterReset(...$arguments)
 * @method mixed clusterSaveconfig(...$arguments)
 * @method mixed clusterSetConfigEpoch(...$arguments)
 * @method mixed clusterSetslot(...$arguments)
 * @method mixed clusterSlaves(...$arguments)
 * @method mixed clusterReplicas(...$arguments)
 * @method mixed clusterSlots(...$arguments)
 * @method mixed command(...$arguments)
 * @method mixed commandCount(...$arguments)
 * @method mixed commandGetkeys(...$arguments)
 * @method mixed commandInfo(...$arguments)
 * @method mixed configGet(...$arguments)
 * @method mixed configRewrite(...$arguments)
 * @method mixed configSet(...$arguments)
 * @method mixed configResetstat(...$arguments)
 * @method mixed dbsize(...$arguments)
 * @method mixed debugObject(...$arguments)
 * @method mixed debugSegfault(...$arguments)
 * @method mixed decr(...$arguments)
 * @method mixed decrby(...$arguments)
 * @method mixed del(...$arguments)
 * @method mixed discard(...$arguments)
 * @method mixed dump(...$arguments)
 * @method mixed echo(...$arguments)
 * @method mixed eval(...$arguments)
 * @method mixed evalsha(...$arguments)
 * @method mixed exec(...$arguments)
 * @method mixed exists(...$arguments)
 * @method mixed expire(...$arguments)
 * @method mixed expireat(...$arguments)
 * @method mixed flushall(...$arguments)
 * @method mixed flushdb(...$arguments)
 * @method mixed geoadd(...$arguments)
 * @method mixed geohash(...$arguments)
 * @method mixed geopos(...$arguments)
 * @method mixed geodist(...$arguments)
 * @method mixed georadius(...$arguments)
 * @method mixed georadiusbymember(...$arguments)
 * @method mixed get(...$arguments)
 * @method mixed getbit(...$arguments)
 * @method mixed getrange(...$arguments)
 * @method mixed getset(...$arguments)
 * @method mixed hdel(...$arguments)
 * @method mixed hello(...$arguments)
 * @method mixed hexists(...$arguments)
 * @method mixed hget(...$arguments)
 * @method mixed hgetall(...$arguments)
 * @method mixed hincrby(...$arguments)
 * @method mixed hincrbyfloat(...$arguments)
 * @method mixed hkeys(...$arguments)
 * @method mixed hlen(...$arguments)
 * @method mixed hmget(...$arguments)
 * @method mixed hmset(...$arguments)
 * @method mixed hset(...$arguments)
 * @method mixed hsetnx(...$arguments)
 * @method mixed hstrlen(...$arguments)
 * @method mixed hvals(...$arguments)
 * @method mixed incr(...$arguments)
 * @method mixed incrby(...$arguments)
 * @method mixed incrbyfloat(...$arguments)
 * @method mixed info(...$arguments)
 * @method mixed lolwut(...$arguments)
 * @method mixed keys(...$arguments)
 * @method mixed lastsave(...$arguments)
 * @method mixed lindex(...$arguments)
 * @method mixed linsert(...$arguments)
 * @method mixed llen(...$arguments)
 * @method mixed lpop(...$arguments)
 * @method mixed lpos(...$arguments)
 * @method mixed lpush(...$arguments)
 * @method mixed lpushx(...$arguments)
 * @method mixed lrange(...$arguments)
 * @method mixed lrem(...$arguments)
 * @method mixed lset(...$arguments)
 * @method mixed ltrim(...$arguments)
 * @method mixed memoryDoctor(...$arguments)
 * @method mixed memoryHelp(...$arguments)
 * @method mixed memoryMallocStats(...$arguments)
 * @method mixed memoryPurge(...$arguments)
 * @method mixed memoryStats(...$arguments)
 * @method mixed memoryUsage(...$arguments)
 * @method mixed mget(...$arguments)
 * @method mixed migrate(...$arguments)
 * @method mixed moduleList(...$arguments)
 * @method mixed moduleLoad(...$arguments)
 * @method mixed moduleUnload(...$arguments)
 * @method mixed move(...$arguments)
 * @method mixed mset(...$arguments)
 * @method mixed msetnx(...$arguments)
 * @method mixed multi(...$arguments)
 * @method mixed object(...$arguments)
 * @method mixed persist(...$arguments)
 * @method mixed pexpire(...$arguments)
 * @method mixed pexpireat(...$arguments)
 * @method mixed pfadd(...$arguments)
 * @method mixed pfcount(...$arguments)
 * @method mixed pfmerge(...$arguments)
 * @method mixed ping(...$arguments)
 * @method mixed psetex(...$arguments)
 * @method mixed pubsub(...$arguments)
 * @method mixed pttl(...$arguments)
 * @method mixed publish(...$arguments)
 * @method mixed quit(...$arguments)
 * @method mixed randomkey(...$arguments)
 * @method mixed readonly(...$arguments)
 * @method mixed readwrite(...$arguments)
 * @method mixed rename(...$arguments)
 * @method mixed renamenx(...$arguments)
 * @method mixed restore(...$arguments)
 * @method mixed role(...$arguments)
 * @method mixed rpop(...$arguments)
 * @method mixed rpoplpush(...$arguments)
 * @method mixed rpush(...$arguments)
 * @method mixed rpushx(...$arguments)
 * @method mixed sadd(...$arguments)
 * @method mixed save(...$arguments)
 * @method mixed scard(...$arguments)
 * @method mixed scriptDebug(...$arguments)
 * @method mixed scriptExists(...$arguments)
 * @method mixed scriptFlush(...$arguments)
 * @method mixed scriptKill(...$arguments)
 * @method mixed scriptLoad(...$arguments)
 * @method mixed sdiff(...$arguments)
 * @method mixed sdiffstore(...$arguments)
 * @method mixed select(...$arguments)
 * @method mixed set(...$arguments)
 * @method mixed setbit(...$arguments)
 * @method mixed setex(...$arguments)
 * @method mixed setnx(...$arguments)
 * @method mixed setrange(...$arguments)
 * @method mixed shutdown(...$arguments)
 * @method mixed sinter(...$arguments)
 * @method mixed sinterstore(...$arguments)
 * @method mixed sismember(...$arguments)
 * @method mixed slaveof(...$arguments)
 * @method mixed replicaof(...$arguments)
 * @method mixed slowlog(...$arguments)
 * @method mixed smembers(...$arguments)
 * @method mixed smove(...$arguments)
 * @method mixed sort(...$arguments)
 * @method mixed spop(...$arguments)
 * @method mixed srandmember(...$arguments)
 * @method mixed srem(...$arguments)
 * @method mixed stralgo(...$arguments)
 * @method mixed strlen(...$arguments)
 * @method mixed sunion(...$arguments)
 * @method mixed sunionstore(...$arguments)
 * @method mixed swapdb(...$arguments)
 * @method mixed sync(...$arguments)
 * @method mixed psync(...$arguments)
 * @method mixed time(...$arguments)
 * @method mixed touch(...$arguments)
 * @method mixed ttl(...$arguments)
 * @method mixed type(...$arguments)
 * @method mixed unlink(...$arguments)
 * @method mixed unwatch(...$arguments)
 * @method mixed wait(...$arguments)
 * @method mixed watch(...$arguments)
 * @method mixed zadd(...$arguments)
 * @method mixed zcard(...$arguments)
 * @method mixed zcount(...$arguments)
 * @method mixed zincrby(...$arguments)
 * @method mixed zinterstore(...$arguments)
 * @method mixed zlexcount(...$arguments)
 * @method mixed zpopmax(...$arguments)
 * @method mixed zpopmin(...$arguments)
 * @method mixed zrange(...$arguments)
 * @method mixed zrangebylex(...$arguments)
 * @method mixed zrevrangebylex(...$arguments)
 * @method mixed zrangebyscore(...$arguments)
 * @method mixed zrank(...$arguments)
 * @method mixed zrem(...$arguments)
 * @method mixed zremrangebylex(...$arguments)
 * @method mixed zremrangebyrank(...$arguments)
 * @method mixed zremrangebyscore(...$arguments)
 * @method mixed zrevrange(...$arguments)
 * @method mixed zrevrangebyscore(...$arguments)
 * @method mixed zrevrank(...$arguments)
 * @method mixed zscore(...$arguments)
 * @method mixed zunionstore(...$arguments)
 * @method mixed scan(...$arguments)
 * @method mixed sscan(...$arguments)
 * @method mixed hscan(...$arguments)
 * @method mixed zscan(...$arguments)
 * @method mixed xinfo(...$arguments)
 * @method mixed xadd(...$arguments)
 * @method mixed xtrim(...$arguments)
 * @method mixed xdel(...$arguments)
 * @method mixed xrange(...$arguments)
 * @method mixed xrevrange(...$arguments)
 * @method mixed xlen(...$arguments)
 * @method mixed xread(...$arguments)
 * @method mixed xgroup(...$arguments)
 * @method mixed xreadgroup(...$arguments)
 * @method mixed xack(...$arguments)
 * @method mixed xclaim(...$arguments)
 * @method mixed xpending(...$arguments)
 * @method mixed latencyDoctor(...$arguments)
 * @method mixed latencyGraph(...$arguments)
 * @method mixed latencyHistory(...$arguments)
 * @method mixed latencyLatest(...$arguments)
 * @method mixed latencyReset(...$arguments)
 * @method mixed latencyHelp(...$arguments)
 */
class Redis
{
	/**
	 * Command terminator.
	 */
	protected const string CRLF = "\r\n";

	/**
	 * Command terminator length.
	 */
	protected const int CRLF_LENGTH = 2;

	/**
	 * Verbatim string prefix length.
	 */
	protected const int VERBATIM_PREFIX_LENGTH = 4;

	/**
	 * UUID representing a "end" response.
	 */
	protected const string END = 'dd0edad3-61d3-415b-aeab-61b14841cda3';

	/**
	 * RESP version 2.
	 */
	public const int RESP2 = 2;

	/**
	 * RESP version 3.
	 */
	public const int RESP3 = 3;

	/**
	 * RESP version the connection was created with.
	 */
	protected int $resp = Redis::RESP2;

	/**
	 * Redis username.
	 */
	protected null|string $username = null;

	/**
	 * Redis password.
	 */
	protected null|string $password = null;

	/**
	 * Redis database.
	 */
	protected null|int $database = null;

	/**
	 * Is pipelining enabled?
	 */
	protected bool $pipelined = false;

	/**
	 * Pipelined commands.
	 */
	protected array $commands = [];

	/**
	 * Cluster clients.
	 */
	protected array $clusterClients = [];

	/**
	 * Last command.
	 */
	protected string $lastCommand;

	/**
	 * Response attributes.
	 */
	protected array $attributes = [];

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Connection $connection,
		array $options = []
	) {
		// Switch protocol to RESP3

		if (!empty($options['resp']) && $options['resp'] === static::RESP3) {
			$this->hello($this->resp = static::RESP3);
		}

		// Authenticate

		if (!empty($options['password'])) {
			if (empty($options['username'])) {
				$this->auth($this->password = $options['password']);
			}
			else {
				$this->auth($this->username = $options['username'], $this->password = $options['password']);
			}
		}

		// Select database

		if (!empty($options['database'])) {
			$this->select($this->database = $options['database']);
		}
	}

	/**
	 * Returns the RESP version the connection was created with.
	 */
	public function getRespVersion(): int
	{
		return $this->resp;
	}

	/**
	 * Returns the connection.
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * Returns the cluster clients.
	 */
	public function getClusterClients(): array
	{
		return $this->clusterClients;
	}

	/**
	 * Returns the response attributes from the last call.
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Creates a cluster client.
	 */
	protected function createClusterClient(string $server): Redis
	{
		[$server, $port] = explode(':', $server, 2);

		return new static(new Connection($server, $port, $this->connection->getOptions()), [
			'resp'     => $this->resp,
			'username' => $this->username,
			'password' => $this->password,
			'database' => $this->database,
		]);
	}

	/**
	 * Gets a cluster client.
	 */
	protected function getClusterClient(string $serverInfo): Redis
	{
		[, $server] = explode(' ', $serverInfo, 2);

		if (!isset($this->clusterClients[$server])) {
			$this->clusterClients[$server] = $this->createClusterClient($server);
		}

		return $this->clusterClients[$server];
	}

	/**
	 * Handles a simple string response.
	 */
	protected function handleSimpleStringResponse(string $response): string
	{
		return substr($response, 1);
	}

	/**
	 * Handles a blob string response.
	 */
	protected function handleBlobStringResponse(string $response): ?string
	{
		if ($response === '$-1') {
			return null;
		}

		$length = substr($response, 1);

		// Do we have a streamed blob string response?

		if ($length === '?') {
			$string = '';

			$length = (int) substr(trim($this->connection->readLine()), 1);

			while ($length !== 0) {
				$string .= substr($this->connection->read($length + static::CRLF_LENGTH), 0, -static::CRLF_LENGTH);

				$length = (int) substr(trim($this->connection->readLine()), 1);
			}

			return $string;
		}

		// It was just a normal blob string response

		return substr($this->connection->read((int) $length + static::CRLF_LENGTH), 0, -static::CRLF_LENGTH);
	}

	/**
	 * Handles a verbatim string response.
	 */
	protected function handleVerbatimStringResponse(string $response): string
	{
		$length = (int) substr($response, 1);

		return substr($this->connection->read($length + static::CRLF_LENGTH), static::VERBATIM_PREFIX_LENGTH, -static::CRLF_LENGTH);
	}

	/**
	 * Handles a number response.
	 */
	protected function handleNumberResponse(string $response): int
	{
		return (int) substr($response, 1);
	}

	/**
	 * Handles a double response.
	 */
	protected function handleDoubleResponse(string $response): float
	{
		$value = substr($response, 1);

		if ($value === 'inf') {
			return INF;
		}

		if ($value === '-inf') {
			return -INF;
		}

		return (float) $value;
	}

	/**
	 * Handles a big number response.
	 */
	protected function handleBigNumberResponse(string $response): string
	{
		return substr($response, 1);
	}

	/**
	 * Handles a boolean response.
	 */
	protected function handleBooleanResponse(string $response): bool
	{
		if ($response === '#t') {
			return true;
		}

		return false;
	}

	/**
	 * Handles a array response.
	 */
	protected function handleArrayResponse(string $response): ?array
	{
		if ($response === '*-1') {
			return null;
		}

		$data = [];

		$count = substr($response, 1);

		// Do we have a streamed array response?

		if ($count === '?') {
			while (true) {
				$value = $this->getResponse();

				if ($value === static::END) {
					break;
				}

				$data[] = $value;
			}

			return $data;
		}

		// It was just a normal array response

		$count = (int) $count;

		for ($i = 0; $i < $count; $i++) {
			$data[] = $this->getResponse();
		}

		return $data;
	}

	/**
	 * Handles a map response.
	 */
	protected function handleMapResponse(string $response): array
	{
		$data = [];

		$count = substr($response, 1);

		// Do we have a streamed map response?

		if ($count === '?') {
			while (true) {
				$key = $this->getResponse();

				if ($key === static::END) {
					break;
				}

				$data[$key] = $this->getResponse();
			}

			return $data;
		}

		// It was just a normal map response

		$count = (int) $count;

		for ($i = 0; $i < $count; $i++) {
			$data[$this->getResponse()] = $this->getResponse();
		}

		return $data;
	}

	/**
	 * Handles a set response.
	 */
	protected function handleSetResponse(string $response): array
	{
		return array_unique($this->handleArrayResponse($response), SORT_REGULAR);
	}

	/**
	 * Handles an attribute response.
	 */
	protected function handleAttributeResponse(string $response): mixed
	{
		// Fetch and store the response attributes

		$attributes = [];

		$count = (int) substr($response, 1);

		for ($i = 0; $i < $count; $i++) {
			$attributes[$this->getResponse()] = $this->getResponse();
		}

		$this->attributes[] = $attributes;

		// Return the actual response data

		return $this->getResponse();
	}

	/**
	 * Handles a push response.
	 */
	protected function handlePushResponse(string $response): array
	{
		return $this->handleArrayResponse($response);
	}

	/**
	 * Handles simple error responses.
	 */
	protected function handleSimpleErrorResponse(string $response): mixed
	{
		$response = substr($response, 1);

		[$type, $error] = explode(' ', $response, 2);

		return match ($type) {
			'MOVED', 'ASK' => $this->getClusterClient($error)->sendCommandAndGetResponse($this->lastCommand),
			default        => throw new RedisException(vsprintf('%s.', [$response])),
		};
	}

	/**
	 * Handles blob error responses.
	 */
	protected function handleBlobErrorResponse(string $response): void
	{
		$length = (int) substr($response, 1);

		$response = substr($this->connection->read($length + static::CRLF_LENGTH), 0, -static::CRLF_LENGTH);

		throw new RedisException(vsprintf('%s.', [$response]));
	}

	/**
	 * Returns response from redis server.
	 */
	protected function getResponse(): mixed
	{
		$response = trim($this->connection->readLine());

		return match (substr($response, 0, 1)) {
			'+'     => $this->handleSimpleStringResponse($response),
			'$'     => $this->handleBlobStringResponse($response),
			'='     => $this->handleVerbatimStringResponse($response),
			':'     => $this->handleNumberResponse($response),
			','     => $this->handleDoubleResponse($response),
			'('     => $this->handleBigNumberResponse($response),
			'#'     => $this->handleBooleanResponse($response),
			'*'     => $this->handleArrayResponse($response),
			'%'     => $this->handleMapResponse($response),
			'~'     => $this->handleSetResponse($response),
			'|'     => $this->handleAttributeResponse($response),
			'>'     => $this->handlePushResponse($response),
			'-'     => $this->handleSimpleErrorResponse($response),
			'!'     => $this->handleBlobErrorResponse($response),
			'_'     => null,
			'.'     => static::END,
			default => throw new RedisException(vsprintf('Unable to handle server response [ %s ].', [$response])),
		};
	}

	/**
	 * Builds command.
	 */
	protected function buildCommand(string $name, array $arguments = []): string
	{
		$command = strtoupper(str_replace('_', ' ', Str::camelToSnake($name)));

		if (strpos($command, ' ') === false) {
			$command = [$command];
		}
		else {
			$command = explode(' ', $command, 2);

			if (strpos($command[1], ' ') !== false) {
				$command[1] = str_replace(' ', '-', $command[1]);
			}
		}

		$pieces = [...$command, ...$arguments];

		$command = '*' . count($pieces) . static::CRLF;

		foreach ($pieces as $piece) {
			$command .= '$' . strlen($piece) . static::CRLF . $piece . static::CRLF;
		}

		return $command;
	}

	/**
	 * Sends command to server.
	 */
	protected function sendCommand(string $command): void
	{
		$this->lastCommand = $command;

		$this->connection->write($command);
	}

	/**
	 * Executes raw Redis commands and returns the response.
	 */
	protected function sendCommandAndGetResponse(string $command): mixed
	{
		$this->sendCommand($command);

		return $this->getResponse();
	}

	/**
	 * Subscribes to the chosen channels.
	 */
	protected function subscribe(array $channels, Closure $subscriber, array $accept, string $subscribe, string $unsubscribe): void
	{
		$this->sendCommand($this->buildCommand($subscribe, $channels));

		while (true) {
			$message = new Message($this->getResponse());

			if (in_array($message->getType(), $accept) && $subscriber($message) === false) {
				break;
			}

			unset($message);
		}

		foreach ($channels as $channel) {
			$this->sendCommandAndGetResponse($this->buildCommand($unsubscribe, [$channel]));
		}
	}

	/**
	 * Subscribes to the chosen channels.
	 */
	public function subscribeTo(array $channels, Closure $subscriber, array $accept = ['message']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'subscribe', 'unsubscribe');
	}

	/**
	 * Subscribes to the chosen channels.
	 */
	public function subscribeToPattern(array $channels, Closure $subscriber, array $accept = ['pmessage']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'psubscribe', 'punsubscribe');
	}

	/**
	 * Monitors the redis server.
	 */
	public function monitor(Closure $monitor): void
	{
		$this->sendCommandAndGetResponse($this->buildCommand('monitor'));

		while (true) {
			if ($monitor($this->getResponse()) === false) {
				break;
			}
		}

		$this->sendCommandAndGetResponse($this->buildCommand('quit'));
	}

	/**
	 * Pipeline commands.
	 */
	public function pipeline(Closure $pipeline): array
	{
		// Reset attributes

		$this->attributes = [];

		// Enable pipelining

		$this->pipelined = true;

		// Build commands

		$pipeline($this);

		// Send all commands to server and fetch responses

		$responses = [];

		$commands = count($this->commands);

		$this->sendCommand(implode('', $this->commands));

		for ($i = 0; $i < $commands; $i++) {
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
	 */
	public function __call(string $name, array $arguments): mixed
	{
		$command = $this->buildCommand($name, $arguments);

		if ($this->pipelined) {
			// Pipeline commands

			$this->commands[] = $command;

			return $this;
		}

		// Reset attributes

		$this->attributes = [];

		// Send command to server and return response

		return $this->sendCommandAndGetResponse($command);
	}
}
