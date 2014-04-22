<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use \mako\database\Connection;

/**
 * Database store.
 *
 * @author  Frederic G. Østby
 */

class Database implements \mako\session\stores\StoreInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection
	 * 
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Database table.
	 * 
	 * @var string
	 */

	protected $table;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\Connection  $connection  Database connection
	 * @param   string                     $table       Database table
	 */

	public function __construct(Connection $connection, $table)
	{
		$this->connection = $connection;

		$this->table = $table;
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
		return $this->connection->table($this->table);
	}

	/**
	 * Writes session data.
	 *
	 * @access  public
	 * @param   string   $sessionId    Session id
	 * @param   string   $sessionData  Session data
	 * @param   int      $dataTTL      TTL in seconds
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		$sessionData = serialize($sessionData);

		if($this->table()->where('id', '=', $sessionId)->count() != 0)
		{
			// Update existing session data

			$this->table()->where('id', '=', $sessionId)->update(['data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
		else
		{
			// Insert new session data

			$this->table()->insert(['id' => $sessionId, 'data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
	}

	/**
	 * Reads and returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  string
	 */

	public function read($sessionId)
	{
		$sessionData = $this->table()->where('id', '=', $sessionId)->column('data');

		return ($sessionData !== false) ? unserialize($sessionData) : [];
	}

	/**
	 * Destroys the session data assiciated with the provided id.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 */

	public function delete($sessionId)
	{
		$this->table()->where('id', '=', $sessionId)->delete();
	}

	/**
	 * Garbage collector that deletes expired session data.
	 *
	 * @access  public
	 * @param   int      $dataTTL  Data TTL in seconds
	 */

	public function gc($dataTTL)
	{
		$this->table()->where('expires', '<', time())->delete();
	}
}