<?php

namespace mako\session\handlers;

use \PDOException;
use \mako\database\Database as DB;

/**
 * Database session handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Database implements \SessionHandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Configuration.
	 * 
	 * @var array
	 */

	protected $config;

	/**
	 * Database connection object.
	 *
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Max session lifetime.
	 *
	 * @var int
	 */

	protected $maxLifetime;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $config  Configuration
	 */

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->connection = DB::connection($config['configuration']);

		$this->maxLifetime = ini_get('session.gc_maxlifetime');
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
	 */

	protected function table()
	{
		return $this->connection->table($this->config['table']);
	}

	/**
	 * Open session.
	 *
	 * @access  public
	 * @param   string   $savePath   Save path
	 * @param   string   $sessionId  Session id
	 * @return  boolean
	 */

	public function open($savePath, $sessionId)
	{
		return true;
	}

	/**
	 * Close session.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function close()
	{
		return true;
	}

	/**
	 * Returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  string
	 */

	public function read($sessionId)
	{
		try
		{
			$data = $this->table()->where('id', '=', $sessionId)->column('data');

			return ($data !== false) ? $data : '';
		}
		catch(PDOException $e)
		{
			return '';
		}
	}

	/**
	 * Writes data to the session.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 * @param   string   $data       Session data
	 * @return  boolean
	 */

	public function write($sessionId, $data)
	{
		try
		{
			if($this->table()->where('id', '=', $sessionId)->count() != 0)
			{
				return (bool) $this->table()->where('id', '=', $sessionId)->update(array('data' => $data, 'expires' => (time() + $this->maxLifetime)));
			}
			else
			{
				return $this->table()->insert(array('id' => $sessionId, 'data' => $data, 'expires' => (time() + $this->maxLifetime)));
			}
		}
		catch(PDOException $e)
		{
			return false;
		}
	}

	/**
	 * Destroys the session.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 * @return  boolean
	 */

	public function destroy($sessionId)
	{
		try
		{
			return (bool) $this->table()->where('id', '=', $sessionId)->delete();
		}
		catch(PDOException $e)
		{
			return false;
		}
	}

	/**
	 * Garbage collector.
	 *
	 * @access  public
	 * @param   int      $maxLifetime  Max lifetime in secods
	 * @return  boolean
	 */

	public function gc($maxLifetime)
	{
		try
		{
			return (bool) $this->table()->where('expires', '<', time())->delete();
		}
		catch(PDOException $e)
		{
			return false;
		}
	}
}

/** -------------------- End of file -------------------- **/