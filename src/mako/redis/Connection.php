<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Throwable;

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
	protected $isPersistent = false;

	/**
	 * Last command.
	 *
	 * @var string
	 */
	protected $lastCommand;

	/**
	 * Constructor.
	 *
	 * @param string $host       Redis host
	 * @param int    $port       Redis port
	 * @param bool   $persistent Should the connection be persistent?
	 */
	public function __construct(string $host, int $port = 6379, bool $persistent = false)
	{
		if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)
		{
			$host = '[' . $host . ']';
		}

		try
		{
			if($persistent)
			{
				$this->isPersistent = true;

				$this->connection = pfsockopen('tcp://' . $host, $port, $errNo);
			}
			else
			{
				$this->connection = fsockopen('tcp://' . $host, $port, $errNo);
			}
		}
		catch(Throwable $e)
		{
			throw new RedisException(vsprintf('%s', [$e->getMessage()]), (int) $errNo);
		}
	}

	/**
	 * Destructor.
	 */
	public function __desctruct()
	{
		if(!$this->isPersistent && is_resource($this->connection))
		{
			fclose($this->connection);
		}
	}

	/**
	 * Creates a new connection.
	 *
	 * @param  string                 $host       Redis host
	 * @param  int                    $port       Redis port
	 * @param  bool                   $persistent Should the connection be persistent?
	 * @return \mako\redis\Connection
	 */
	public static function create(string $host, int $port, bool $persistent = false): Connection
	{
		return new static($host, $port, $persistent);
	}

	/**
	 * Gets line from the resource.
	 *
	 * @return string|false
	 */
	public function readLine()
	{
		return fgets($this->connection);
	}

	/**
	 * Reads n bytes from the resource.
	 *
	 * @param  int          $bytes Number of bytes to read
	 * @return string|false
	 */
	public function read(int $bytes)
	{
		$bytesLeft = $bytes;

		$data = '';

		do
		{
			$data .= fread($this->connection, min($bytesLeft, 4096));

			$bytesLeft = $bytes - strlen($data);
		}
		while($bytesLeft > 0);

		return $data;
	}

	/**
	 * Writes data to the resource.
	 *
	 * @param  string    $data Data to write
	 * @return int|false
	 */
	public function write(string $data)
	{
		return fwrite($this->connection, $this->lastCommand = $data);
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
	 * Returns the last command.
	 *
	 * @return string
	 */
	public function getLastCommand(): string
	{
		return $this->lastCommand;
	}
}
