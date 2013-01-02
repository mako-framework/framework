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

		return is_int(file_put_contents("{$this->path}/mako_{$this->identifier}_{$key}.php", $data, LOCK_EX));
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
		if(file_exists("{$this->path}/mako_{$this->identifier}_{$key}.php"))
		{
			// Cache exists
			
			$handle = fopen("{$this->path}/mako_{$this->identifier}_{$key}.php", 'r');

			fgets($handle); // skip first line

			if(time() < (int) fgets($handle))
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

				unlink("{$this->path}/mako_{$this->identifier}_{$key}.php");

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
	 * Delete a variable from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function delete($key)
	{
		if(file_exists("{$this->path}/mako_{$this->identifier}_{$key}.php"))
		{
			return unlink("{$this->path}/mako_{$this->identifier}_{$key}.php");
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
					if(unlink("{$this->path}/{$file}") === false)
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}

/** -------------------- End of file --------------------**/