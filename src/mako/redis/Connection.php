<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Throwable;

use function fclose;
use function fgets;
use function filter_var;
use function fread;
use function fsockopen;
use function fwrite;
use function is_resource;
use function min;
use function pfsockopen;
use function stream_get_meta_data;
use function stream_set_timeout;
use function strlen;
use function substr;
use function vsprintf;

/**
 * Redis connection.
 *
 * @author Frederic G. Østby
 */
class Connection
{
	/**
	 * Socket connection.
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * Is the socket persistent?
	 *
	 * @var bool
	 */
	protected $isPersistent;

	/**
	 * Read/write timeout.
	 *
	 * @var int
	 */
	protected $readWriteTimeout;

	/**
	 * Connection timeout.
	 *
	 * @var int
	 */
	protected $connectionTimeout;

	/**
	 * Connection name.
	 *
	 * @var string|null
	 */
	protected $name;

	/**
	 * Constructor.
	 *
	 * @param string      $host              Redis host
	 * @param int         $port              Redis port
	 * @param bool        $persistent        Should the connection be persistent?
	 * @param int         $readWriteTimeout  Read/write timeout in seconds
	 * @param int         $connectionTimeout Connection timeout in seconds
	 * @param string|null $name              Connection name
	 */
	public function __construct(string $host, int $port = 6379, bool $persistent = false, int $readWriteTimeout = 60, int $connectionTimeout = 5, ?string $name = null)
	{
		$this->name = $name;

		if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)
		{
			$host = "[{$host}]";
		}

		// Connect to the server

		$this->connection = $this->createConnection($host, $port, $this->isPersistent = $persistent, $this->connectionTimeout = $connectionTimeout);

		// Set timeout for read/write operations

		stream_set_timeout($this->connection, $this->readWriteTimeout = $readWriteTimeout);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if(!$this->isPersistent && is_resource($this->connection))
		{
			fclose($this->connection);
		}
	}

	/**
	 * Creates a socket connection to the server.
	 *
	 * @param  string   $host              Redis host
	 * @param  int      $port              Redis port
	 * @param  bool     $persistent        Should the connection be persistent?
	 * @param  int      $connectionTimeout Connection timeout
	 * @return resource
	 */
	protected function createConnection(string $host, int $port, bool $persistent, int $connectionTimeout)
	{
		try
		{
			if($persistent)
			{
				return pfsockopen("tcp://{$host}", $port, $errNo, $errStr, $connectionTimeout);
			}

			return fsockopen("tcp://{$host}", $port, $errNo, $errStr, $connectionTimeout);
		}
		catch(Throwable $e)
		{
			$message = $this->name === null ? 'Failed to connect' : vsprintf('Failed to connect to [ %s ]', [$this->name]);

			throw new RedisException(vsprintf('%s. %s', [$message, $e->getMessage()]), (int) $errNo);
		}
	}

	/**
	 * Is the connection persistent?
	 *
	 * @return bool
	 */
	public function isPersistent(): bool
	{
		return $this->isPersistent;
	}

	/**
	 * Returns the read/write timeout value of the connection.
	 *
	 * @return int
	 */
	public function getReadWriteTimeout(): int
	{
		return $this->readWriteTimeout;
	}

	/**
	 * Returns the connection timeout value of the connection.
	 *
	 * @return int
	 */
	public function getConnectionTimeout(): int
	{
		return $this->connectionTimeout;
	}

	/**
	 * Returns the connection name.
	 *
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Appends the read error reason to the error message if possible.
	 *
	 * @param  string $message Error message
	 * @return string
	 */
	protected function appendReadErrorReason($message): string
	{
		if(stream_get_meta_data($this->connection)['timed_out'])
		{
			return "{$message} The stream timed out while waiting for data.";
		}

		return $message;
	}

	/**
	 * Gets line from the server.
	 *
	 * @return string
	 */
	public function readLine(): string
	{
		$line = fgets($this->connection);

		if($line === false || $line === '')
		{
			throw new RedisException($this->appendReadErrorReason('Failed to read line from the server.'));
		}

		return $line;
	}

	/**
	 * Reads n bytes from the server.
	 *
	 * @param  int    $bytes Number of bytes to read
	 * @return string
	 */
	public function read(int $bytes): string
	{
		$bytesLeft = $bytes;

		$data = '';

		do
		{
			$chunk = fread($this->connection, min($bytesLeft, 4096));

			if($chunk === false || $chunk === '')
			{
				throw new RedisException($this->appendReadErrorReason('Failed to read data from the server.'));
			}

			$data .= $chunk;

			$bytesLeft = $bytes - strlen($data);
		}
		while($bytesLeft > 0);

		return $data;
	}

	/**
	 * Writes data to the server.
	 *
	 * @param  string $data Data to write
	 * @return int
	 */
	public function write(string $data): int
	{
		$totalBytesWritten = 0;

		$bytesLeft = strlen($data);

		do
		{
			$totalBytesWritten += $bytesWritten = fwrite($this->connection, $data);

			if($bytesWritten === false || $bytesWritten === 0)
			{
				throw new RedisException('Failed to write data to the server.');
			}

			$bytesLeft -= $bytesWritten;

			$data = substr($data, $bytesWritten);
		}
		while($bytesLeft > 0);

		return $totalBytesWritten;
	}
}
