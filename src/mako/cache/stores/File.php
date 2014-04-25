<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * File store.
 *
 * @author  Frederic G. Østby
 */

class File implements \mako\cache\stores\StoreInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache path.
	 * 
	 * @var string
	 */

	protected $cachePath;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $cachePath  Cache path
	 */

	public function __construct($cachePath)
	{
		$this->cachePath = $cachePath;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the path to the cache file.
	 * 
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  string
	 */

	protected function cacheFile($key)
	{
		return $this->cachePath . '/' . str_replace(['/', ':'], '_', $key) . '.php';
	}

	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function put($key, $data, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$data = $ttl . "\n" . serialize($data);

		return is_int(file_put_contents($this->cacheFile($key), $data, LOCK_EX));
	}

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function has($key)
	{
		if(file_exists($this->cacheFile($key)))
		{
			$handle = fopen($this->cacheFile($key), 'r');

			$expired = (time() < (int) trim(fgets($handle)));

			fclose($handle);

			return $expired;
		}

		return false;
	}

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	public function get($key)
	{
		if(file_exists($this->cacheFile($key)))
		{
			// Cache exists
			
			$handle = fopen($this->cacheFile($key), 'r');

			if(time() < (int) trim(fgets($handle)))
			{
				// Cache has not expired ... fetch it

				$cache = '';

				while(!feof($handle))
				{
					$cache .= fgets($handle);
				}

				fclose($handle);

				return unserialize($cache);
			}
			else
			{
				// Cache has expired ... delete it

				fclose($handle);

				unlink($this->cacheFile($key));

				return false;
			}
		}
		else
		{
			// Cache doesn't exist

			return false;
		}
	}

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function remove($key)
	{
		if(file_exists($this->cacheFile($key)))
		{
			return unlink($this->cacheFile($key));
		}

		return false;
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		$files = scandir($this->cachePath);

		if($files !== false)
		{
			foreach($files as $file)
			{
				$file = $this->cachePath . '/' . $file;

				if(is_file($file) && unlink($file) === false)
				{
					return false;
				}
			}
		}

		return true;
	}
}