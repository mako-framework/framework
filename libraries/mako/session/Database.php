<?php

namespace mako\session;

use \PDOException;
use \mako\Database as DB;

/**
 * Database adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Database extends \mako\session\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection object.
	 *
	 * @var mako\database\Connection
	 */

	protected $connection;

	/**
	 * Session table.
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
	 * @param   array   $config  Configuration
	 */

	public function __construct(array $config)
	{
		parent::__construct();

		$this->connection = DB::connection($config['configuration']);

		$this->table = $config['table'];
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		session_write_close();

		// Fixes issue with Debian and Ubuntu session garbage collection

		if(mt_rand(1, 100) === 100)
		{
			$this->gc(0);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  mako\database\Query
	 */

	protected function table()
	{
		return $this->connection->table($this->table);
	}

	/**
	 * Returns session data.
	 *
	 * @access  public
	 * @param   string  $id  Session id
	 * @return  string
	 */

	public function read($id)
	{
		try
		{
			$data = $this->table()->where('id', '=', $id)->column('data');

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
	 * @param   string  $id    Session id
	 * @param   string  $data  Session data
	 */

	public function write($id, $data)
	{
		try
		{
			if($this->table()->where('id', '=', $id)->count() != 0)
			{
				return (bool) $this->table()->where('id', '=', $id)->update(array('data' => $data, 'expires' => (time() + $this->maxLifetime)));
			}
			else
			{
				return $this->table()->insert(array('id' => $id, 'data' => $data, 'expires' => (time() + $this->maxLifetime)));
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
	 * @param   string   $id  Session id
	 * @return  boolean
	 */

	public function destroy($id)
	{
		try
		{
			return (bool) $this->table()->where('id', '=', $id)->delete();
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
	 * @param   int      $maxLifetime  Lifetime in secods
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

/** -------------------- End of file --------------------**/