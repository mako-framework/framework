<?php

namespace mako\cache;

use \RuntimeException;

/**
 * File based cache adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class File extends \mako\cache\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache path.
	 *
	 * @var string
	 */

	protected $path;

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
		
		$this->path = $config['path'];

		if(file_exists($this->path) === false || is_readable($this->path) === false || is_writable($this->path) === false)
		{
			throw new RuntimeException(vsprintf("%s(): Cache directory ('%s') is not writable.", array(__METHOD__, $this->path)));
		}
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
		return $this->path . '/mako_' . $this->identifier . $key . '.php';
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

		$data = "<?php defined('MAKO_APPLICATION_PATH') or die(); ?>\n{$ttl}\n" . serialize($value);

		return is_int(file_put_contents($this->cacheFile($key), $data, LOCK_EX));
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
		if(file_exists($this->cacheFile($key)))
		{
			// Cache exists
			
			$handle = fopen($this->cacheFile($key), 'r');

			fgets($handle); // skip first line

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

			fgets($handle); // skip first line

			$expired = (time() < (int) trim(fgets($handle)));

			fclose($handle);

			return $expired;
		}

		return false;
	}

	/**
	 * Updates the stored value while keeping the ttl.
	 * 
	 * @access  protected
	 * @param   string     $key    Cache key
	 * @param   mixed      $value  The variable to store
	 */

	protected function update($key, $value)
	{
		$handle = fopen($this->cacheFile($key), 'r+');

		fgets($handle); // skip first line

		// Fetch the ttl

		$ttl = trim(fgets($handle));

		// Truncate the file

		rewind($handle);

		ftruncate($handle, 0);

		// Write the data and close the handle

		$data = "<?php defined('MAKO_APPLICATION_PATH') or die(); ?>\n{$ttl}\n" . serialize($value);

		fwrite($handle, $data);

		fclose($handle);
	}

	/**
	 * Increases a stored number. Will return the incremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be increased by
	 * @return  mixed
	 */

	public function increment($key, $ammount = 1)
	{
		$value = $this->read($key);

		if($value === false || !is_numeric($value))
		{
			return false;
		}

		$value += $ammount;

		$this->update($key, $value);

		return (int) $value;
	}

	/**
	 * Decrements a stored number. Will return the decremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be decremented by
	 * @return  mixed
	 */

	public function decrement($key, $ammount = 1)
	{
		$value = $this->read($key);

		if($value === false || !is_numeric($value))
		{
			return false;
		}

		$value -= $ammount;

		$this->update($key, $value);

		return (int) $value;
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
		$files = scandir($this->path);

		if($files !== false)
		{
			foreach($files as $file)
			{
				if(mb_substr($file, 0, 5) === 'mako_')
				{
					if(unlink($this->path . '/' . $file) === false)
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}

/** -------------------- End of file -------------------- **/