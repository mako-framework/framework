<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\file\FileSystem;

/**
 * File store.
 *
 * @author  Frederic G. Østby
 */
class File implements StoreInterface
{
	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Cache path.
	 *
	 * @var string
	 */
	protected $cachePath;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem  File system instance
	 * @param   string                 $cachePath   Cache path
	 */
	public function __construct(FileSystem $fileSystem, $cachePath)
	{
		$this->fileSystem = $fileSystem;

		$this->cachePath = $cachePath;
	}

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
	 * {@inheritdoc}
	 */
	public function put($key, $data, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$data = $ttl . "\n" . serialize($data);

		return is_int($this->fileSystem->putContents($this->cacheFile($key), $data, LOCK_EX));
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		if($this->fileSystem->exists($this->cacheFile($key)))
		{
			$file = $this->fileSystem->file($this->cacheFile($key), 'r');

			$expired = (time() < (int) trim($file->fgets()));

			unset($file);

			return $expired;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		if($this->fileSystem->exists($this->cacheFile($key)))
		{
			// Cache exists

			$file = $this->fileSystem->file($this->cacheFile($key), 'r');

			if(time() < (int) trim($file->fgets()))
			{
				// Cache has not expired ... fetch it

				$cache = '';

				while(!$file->eof())
				{
					$cache .= $file->fgets();
				}

				unset($file);

				return unserialize($cache);
			}
			else
			{
				// Cache has expired ... delete it

				unset($file);

				$this->fileSystem->delete($this->cacheFile($key));

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
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		if($this->fileSystem->exists($this->cacheFile($key)))
		{
			return $this->fileSystem->delete($this->cacheFile($key));
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		$files = $this->fileSystem->glob($this->cachePath . '/*');

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if($this->fileSystem->isFile($file) && $this->fileSystem->delete($file) === false)
				{
					return false;
				}
			}
		}

		return true;
	}
}