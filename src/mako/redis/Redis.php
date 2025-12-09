<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Closure;
use mako\redis\exceptions\RedisException;
use mako\redis\traits\AutoSuggestTrait;
use mako\redis\traits\BloomFilterTrait;
use mako\redis\traits\CoreTrait;
use mako\redis\traits\CountMinSketchTrait;
use mako\redis\traits\CuckooFilterTrait;
use mako\redis\traits\JsonTrait;
use mako\redis\traits\RedisQueryEngineTrait;
use mako\redis\traits\TDigestTrait;
use mako\redis\traits\TimeSeriesTrait;
use mako\redis\traits\TopKTrait;
use SensitiveParameter;

use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
use function sprintf;
use function strlen;
use function substr;
use function trim;

/**
 * Redis client.
 *
 * @see https://redis.io/docs/latest/develop/reference/protocol-spec/ Redis protocol specification.
 */
class Redis
{
	use AutoSuggestTrait;
	use BloomFilterTrait;
	use CoreTrait;
	use CountMinSketchTrait;
	use CuckooFilterTrait;
	use JsonTrait;
	use RedisQueryEngineTrait;
	use TDigestTrait;
	use TimeSeriesTrait;
	use TopKTrait;

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
		#[SensitiveParameter] protected array $options = []
	) {
		// Switch protocol to RESP3

		if (!empty($options['resp']) && $options['resp'] === static::RESP3) {
			$this->hello(static::RESP3);
		}

		// Authenticate

		if (!empty($options['password'])) {
			if (empty($options['username'])) {
				$this->auth($options['password']);
			}
			else {
				$this->auth($options['username'], $options['password']);
			}
		}

		// Select database

		if (!empty($options['database'])) {
			$this->select($options['database']);
		}
	}

	/**
	 * Returns the RESP version the connection was created with.
	 */
	public function getRespVersion(): int
	{
		return $this->options['resp'] ?? static::RESP2;
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
		[$host, $port] = explode(':', $server, 2);

		return new static(new Connection($host, (int) $port, $this->connection->getOptions()), $this->options);
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
	 * Handles a bulk string response.
	 */
	protected function handleBulkStringResponse(string $response): ?string
	{
		if ($response === '$-1') {
			return null;
		}

		$length = substr($response, 1);

		// Do we have a streamed bulk string response?

		if ($length === '?') {
			$string = '';

			$length = (int) substr(trim($this->connection->readLine()), 1);

			while ($length !== 0) {
				$string .= substr($this->connection->read($length + static::CRLF_LENGTH), 0, -static::CRLF_LENGTH);

				$length = (int) substr(trim($this->connection->readLine()), 1);
			}

			return $string;
		}

		// It was just a normal bulk string response

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
	 * Handles an integer response.
	 */
	protected function handleIntegerResponse(string $response): int
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
			default        => throw new RedisException(sprintf('%s.', $response)),
		};
	}

	/**
	 * Handles bulk error responses.
	 */
	protected function handleBulkErrorResponse(string $response): void
	{
		$length = (int) substr($response, 1);

		$response = substr($this->connection->read($length + static::CRLF_LENGTH), 0, -static::CRLF_LENGTH);

		throw new RedisException(sprintf('%s.', $response));
	}

	/**
	 * Returns response from redis server.
	 */
	protected function getResponse(): mixed
	{
		$response = trim($this->connection->readLine());

		return match (substr($response, 0, 1)) {
			'+'     => $this->handleSimpleStringResponse($response),
			'$'     => $this->handleBulkStringResponse($response),
			'='     => $this->handleVerbatimStringResponse($response),
			':'     => $this->handleIntegerResponse($response),
			','     => $this->handleDoubleResponse($response),
			'('     => $this->handleBigNumberResponse($response),
			'#'     => $this->handleBooleanResponse($response),
			'*'     => $this->handleArrayResponse($response),
			'%'     => $this->handleMapResponse($response),
			'~'     => $this->handleSetResponse($response),
			'|'     => $this->handleAttributeResponse($response),
			'>'     => $this->handlePushResponse($response),
			'-'     => $this->handleSimpleErrorResponse($response),
			'!'     => $this->handleBulkErrorResponse($response),
			'_'     => null,
			'.'     => static::END,
			default => throw new RedisException(sprintf('Unable to handle server response [ %s ].', $response)),
		};
	}

	/**
	 * Builds command.
	 */
	protected function buildCommand(array $command, array $arguments = []): string
	{
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
		$this->sendCommand($this->buildCommand([$subscribe], $channels));

		while (true) {
			$message = new Message($this->getResponse());

			if (in_array($message->getType(), $accept) && $subscriber($message) === false) {
				break;
			}

			unset($message);
		}

		foreach ($channels as $channel) {
			$this->sendCommandAndGetResponse($this->buildCommand([$unsubscribe], [$channel]));
		}
	}

	/**
	 * Subscribes to the chosen channels.
	 */
	public function subscribeTo(array $channels, Closure $subscriber, array $accept = ['message']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'SUBSCRIBE', 'UNSUBSCRIBE');
	}

	/**
	 * Subscribes to the chosen channels.
	 */
	public function subscribeToPattern(array $channels, Closure $subscriber, array $accept = ['pmessage']): void
	{
		$this->subscribe($channels, $subscriber, $accept, 'PSUBSCRIBE', 'PUNSUBSCRIBE');
	}

	/**
	 * Monitors the redis server.
	 */
	public function monitor(Closure $monitor): void
	{
		$this->sendCommandAndGetResponse($this->buildCommand(['MONITOR']));

		while (true) {
			if ($monitor($this->getResponse()) === false) {
				break;
			}
		}

		$this->sendCommandAndGetResponse($this->buildCommand(['RESET']));
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
	 * Builds and sends a command to the Redis server and returns response
	 * or appends command to the pipeline and returns the client.
	 */
	protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed
	{
		$command = $this->buildCommand($command, $arguments);

		// Should we pipeline the command?

		if ($this->pipelined) {
			$this->commands[] = $command;

			return $this;
		}

		// Reset attributes

		$this->attributes = [];

		// Send command to server and return response

		return $this->sendCommandAndGetResponse($command);
	}

	/**
	 * Sends a command to the Redis server and returns the response.
	 */
	public function executeCommand(string $command, mixed ...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(explode(' ', $command), $arguments);
	}
}
