<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use InvalidArgumentException;
use mako\redis\exceptions\RedisException;
use SensitiveParameter;
use Throwable;

use function fclose;
use function fgets;
use function filter_var;
use function fread;
use function fwrite;
use function is_resource;
use function min;
use function sprintf;
use function stream_context_create;
use function stream_get_meta_data;
use function stream_set_timeout;
use function stream_socket_client;
use function strlen;
use function substr;
use function trim;

/**
 * Redis connection.
 *
 * @phpstan-type ConnectionOptions array{
 *	name?: string|null,
 *	persistent?: bool|null,
 *	connection_timeout?: int|null,
 *	read_write_timeout?: int|null,
 *	tcp_nodelay?: bool|null,
 *	tls?: bool|null,
 *	tls_options?: array<string, mixed>|null,
 * }
 */
class Connection
{
	/**
	 * Connection.
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * Connection name.
	 */
	protected ?string $name;

	/**
	 * Is the connection persistent?
	 */
	protected bool $isPersistent;

	/**
	 * Connection timeout.
	 */
	protected int $connectionTimeout;

	/**
	 * Read/write timeout.
	 */
	protected int $readWriteTimeout;

	/**
	 * TCP nodelay.
	 */
	protected bool $tcpNodelay;

	/**
	 * Use TLS?
	 */
	protected bool $tls;

	/**
	 * TLS stream context options.
	 */
	protected array $tlsOptions;

	/**
	 * Constructor.
	 *
	 * @param ConnectionOptions $options
	 */
	public function __construct(string $host, int $port = 6379, #[SensitiveParameter] array $options = [])
	{
		// Configure

		$this->name = $options['name'] ?? null;

		$this->isPersistent = $options['persistent'] ?? false;

		$this->connectionTimeout = $options['connection_timeout'] ?? 5;

		$this->readWriteTimeout = $options['read_write_timeout'] ?? 60;

		$this->tcpNodelay = $options['tcp_nodelay'] ?? true;

		$this->tls = $options['tls'] ?? false;

		$this->tlsOptions = $options['tls_options'] ?? [];

		// Create connection

		$this->connection = $this->createConnection($host, $port);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if (!$this->isPersistent && is_resource($this->connection)) {
			fclose($this->connection);
		}
	}

	/**
	 * Returns the connection options.
	 *
	 * @return ConnectionOptions
	 */
	public function getOptions(): array
	{
		return [
			'name'               => $this->name,
			'persistent'         => $this->isPersistent,
			'connection_timeout' => $this->connectionTimeout,
			'read_write_timeout' => $this->readWriteTimeout,
			'tcp_nodelay'        => $this->tcpNodelay,
			'tls'                => $this->tls,
			'tls_options'        => $this->tlsOptions,
		];
	}

	/**
	 * Creates a connection to the server.
	 *
	 * @return resource
	 */
	protected function createConnection(string $host, int $port)
	{
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			$host = "[{$host}]";
		}

		try {
			$scheme = $this->tls ? 'tls' : 'tcp';

			$flags = $this->isPersistent ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT;

			$contextOptions = ['socket' => ['tcp_nodelay' => $this->tcpNodelay]];

			if ($this->tls && !empty($this->tlsOptions)) {
				$contextOptions['ssl'] = $this->tlsOptions;
			}

			$context = stream_context_create($contextOptions);

			$connection = stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, $this->connectionTimeout, $flags, $context);

			stream_set_timeout($connection, $this->readWriteTimeout);

			return $connection;
		}
		catch (Throwable $e) {
			throw new RedisException(
				trim(sprintf('Failed to connect to [ %s ]. %s', $this->name ?? "{$host}:{$port}", $errstr ?? '')),
				(int) ($errno ?? 0),
				$e
			);
		}
	}

	/**
	 * Returns the message with the read error reason to the
	 * error message if possible along with the stream metadata.
	 */
	protected function getMessageAndStreamMetadata(string $message): array
	{
		$metadata = stream_get_meta_data($this->connection);

		if ($metadata['timed_out']) {
			$message = "{$message} The stream timed out while waiting for data.";
		}
		elseif ($metadata['eof']) {
			$message = "{$message} The server closed the connection.";

			if (!$this->tls) {
				$message .= ' This can happen if the server requires TLS and the [ tls ] option is not enabled.';
			}
		}

		return ['message' => $message, 'streamMetadata' => $metadata];
	}

	/**
	 * Gets line from the server.
	 */
	public function readLine(): string
	{
		$line = fgets($this->connection);

		if ($line === false || $line === '') {
			throw new RedisException(...$this->getMessageAndStreamMetadata('Failed to read line from the server.'));
		}

		return $line;
	}

	/**
	 * Reads n bytes from the server.
	 */
	public function read(int $bytes): string
	{
		if ($bytes <= 0) {
			throw new InvalidArgumentException('Read length must be greater than zero.');
		}

		$bytesLeft = $bytes;

		$data = '';

		do {
			$chunk = fread($this->connection, min($bytesLeft, 8192));

			if ($chunk === false || $chunk === '') {
				throw new RedisException(...$this->getMessageAndStreamMetadata('Failed to read data from the server.'));
			}

			$data .= $chunk;

			$bytesLeft = $bytes - strlen($data);
		}
		while ($bytesLeft > 0);

		return $data;
	}

	/**
	 * Writes data to the server.
	 */
	public function write(string $data): int
	{
		$totalBytesWritten = 0;

		$bytesLeft = strlen($data);

		do {
			$totalBytesWritten += $bytesWritten = fwrite($this->connection, $data);

			if ($bytesWritten === false) {
				throw new RedisException('Failed to write data to the server.');
			}

			$bytesLeft -= $bytesWritten;

			$data = substr($data, $bytesWritten);
		}
		while ($bytesLeft > 0);

		return $totalBytesWritten;
	}
}
