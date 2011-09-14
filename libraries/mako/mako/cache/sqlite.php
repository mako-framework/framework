<?php

namespace mako\cache
{
	use \PDO;
	use \PDOException;
	use \Exception;
	
	/**
	* SQLite based cache adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class SQLite extends \mako\cache\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Table structure.
		*/
		
		const TABLE = 'CREATE TABLE cache(key VARCHAR(128) PRIMARY KEY, data TEXT, lifetime INTEGER)';
		
		/**
		* PDO object.
		*/
		
		protected $sqlite;
		
		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Constructor.
		*
		* @access  public
		* @param   array   Configuration
		*/
		
		public function __construct(array $config)
		{
			parent::__construct($config['identifier']);
			
			if(extension_loaded('PDO') === false)
			{
				throw new Exception(__CLASS__ . ": PDO is not available.");
			}

			try
			{
				$options = array
				(
					PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION
				);
				
				$this->sqlite = new PDO('sqlite:' . MAKO_APPLICATION . '/storage/cache/mako_' . $this->identifier . '.sqlite', null, null, $options);
								
				$table = $this->sqlite->query("SELECT * FROM sqlite_master WHERE type = 'table' AND name = 'cache' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
								
				if($table === false)
				{
					$this->sqlite->exec(static::TABLE);
				}
			}
			catch(PDOException $e)
			{
				throw new Exception(__CLASS__ . ": Unable to create cache database.<br /><br />" . $e->getMessage());
			}
		}
		
		/**
		* Destructor.
		*
		* @access  public
		*/
		
		public function __destruct()
		{
			$this->sqlite = null;
		}
		
		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Store variable in the cache.
		*
		* @access  public
		* @param   string   Cache key
		* @param   mixed    The variable to store
		* @param   int      (optional) Time to live
		* @return  boolean
		*/
		
		public function write($key, $value, $ttl = 0)
		{
			$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();
			
			try
			{
				$this->delete($key);
				
				$stmt = $this->sqlite->prepare("INSERT INTO cache (key, data, lifetime) VALUES (:key, :data, :lifetime)");
				
				$stmt->bindParam(':key', $key, PDO::PARAM_STR);
				$stmt->bindValue(':data', serialize($value), PDO::PARAM_STR);
				$stmt->bindParam(':lifetime', $ttl, PDO::PARAM_INT);
				
				return $stmt->execute();
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
		* @param   string  Cache key
		* @return  mixed
		*/
		
		public function read($key)
		{
			try
			{
				$stmt = $this->sqlite->prepare("SELECT * FROM cache WHERE key = :key LIMIT 1");
				
				$stmt->bindParam(':key', $key, PDO::PARAM_STR);
				
				$stmt->execute();
				
				$cache = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if($cache !== false)
				{
					if(time() < $cache['lifetime'])
					{
						return unserialize($cache['data']);
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
		* @param   string   Cache key
		* @return  boolean
		*/
		
		public function delete($key)
		{
			try
			{
				$stmt = $this->sqlite->prepare("DELETE FROM cache WHERE key = :key");
				
				$stmt->bindParam(':key', $key, PDO::PARAM_STR);
				
				$stmt->execute();
				
				return (bool) $stmt->rowCount();
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
				$this->sqlite->exec("DELETE FROM cache");
				
				$this->sqlite->exec("VACUUM");
												
				return true;
			}
			catch(PDOException $e)
			{
				return false;
			}
		}
	}
}

/** -------------------- End of file --------------------**/