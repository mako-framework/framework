<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\redis;

use mako\redis\RedisException;

/**
 * Redis connection.
 *
 * @author  Frederic G. Østby
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
	 * Last command.
	 *
	 * @var string
	 */
	protected $lastCommand;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $host  Redis host
	 * @param   int     $port  Redis port
	 */
	public function __construct(string $host, int $port = 6379)
	{
		$this->connection = @fsockopen('tcp://' . $host, $port, $errNo, $errStr);

		if(!$this->connection)
		{
			throw new RedisException(vsprintf("%s(): %s", [__METHOD__, $errStr]), (int) $errNo);
		}
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */
	public function __desctruct()
	{
		if(is_resource($this->connection))
		{
			fclose($this->connection);
		}
	}

	/**
	 * Creates a new connection.
	 *
	 * @access  public
	 * @param   string                  $host  Redis host
	 * @param   int                     $port  Redis port
	 * @return  \mako\redis\Connection
	 */
	public static function createConnection(string $host, int $port): Connection
	{
		return new static($host, $port);
	}

	/**
	 * Gets line from the resource.
	 *
	 * @access  public
	 * @return  string|false
	 */
	public function gets()
	{
		return fgets($this->connection);
	}

	/**
	 * Reads n bytes from the resource.
	 *
	 * @access  public
	 * @param   int           $bytes  Number of bytes to read
	 * @return  string|false
	 */
	public function read(int $bytes)
	{
		return fread($this->connection, $bytes);
	}

	/**
	 * Writes data to the resource.
	 *
	 * @access  public
	 * @param   string     $data  Data to write
	 * @return  int|false
	 */
	public function write(string $data)
	{
		return fwrite($this->connection, $this->lastCommand = $data);
	}

	/**
	 * Returns the last command.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getLastCommand(): string
	{
		return $this->lastCommand;
	}
}