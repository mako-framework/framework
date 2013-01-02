<?php

namespace mako\cache;

use \PDOException;
use \mako\Database as DB;

/**
 * Database based cache adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Database extends \mako\cache\Adapter
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
	 * Cache table.
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
		parent::__construct($config['identifier']);
		
		$this->connection = DB::connection($config['configuration']);

		$this->table = $config['table'];
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
	 * Store variable in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $value  The variable to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */
	
	public function write($key, $value, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();
		
		try
		{
			$this->delete($key);

			return $this->table()->insert(array('key' => $key, 'data' => serialize($value), 'lifetime' => $ttl));
		}
		catch(PDOException $e)
		{
			return false;
		}
	}
	
	/**
	 * Fetch variable from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */
	
	public function read($key)
	{
		try
		{
			$cache = $this->table()->where('key', '=', $key)->first();

			if($cache !== false)
			{
				if(time() < $cache->lifetime)
				{
					return unserialize($cache->data);
				}
				else
				{
					$this->delete($key);
					
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		catch(PDOException $e)
		{
			 return false;
		}
	}
	
	/**
	 * Delete a variable from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */
	
	public function delete($key)
	{
		try
		{
			return (bool) $this->table()->where('key', '=', $key)->delete();
		}
		catch(PDOException $e)
		{
			return false;
		}
	}
	
	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */
	
	public function clear()
	{
		try
		{
			$this->table()->delete();
											
			return true;
		}
		catch(PDOException $e)
		{
			return false;
		}
	}
}

/** -------------------- End of file --------------------**/