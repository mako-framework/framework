<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\file\FileSystem;

use function is_array;
use function is_int;
use function serialize;
use function str_replace;
use function time;
use function trim;
use function unserialize;

/**
 * File store.
 */
class File extends Store
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
	 * Class whitelist.
	 *
	 * @var bool|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem     File system instance
	 * @param string                $cachePath      Cache path
	 * @param bool|array            $classWhitelist Class whitelist
	 */
	public function __construct(FileSystem $fileSystem, string $cachePath, $classWhitelist = false)
	{
		$this->fileSystem = $fileSystem;

		$this->cachePath = $cachePath;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * Returns the path to the cache file.
	 *
	 * @param  string $key Cache key
	 * @return string
	 */
	protected function cacheFile(string $key): string
	{
		return "{$this->cachePath}/" . str_replace(['/', ':'], '_', $this->getPrefixedKey($key)) . '.php';
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$data = $ttl . "\n" . serialize($data);

		return is_int($this->fileSystem->put($this->cacheFile($key), $data, true));
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		if($this->fileSystem->has($this->cacheFile($key)))
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
	public function get(string $key)
	{
		if($this->fileSystem->has($this->cacheFile($key)))
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

				return unserialize($cache, ['allowed_classes' => $this->classWhitelist]);
			}
			else
			{
				// Cache has expired ... delete it

				unset($file);

				$this->fileSystem->remove($this->cacheFile($key));
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		if($this->fileSystem->has($this->cacheFile($key)))
		{
			return $this->fileSystem->remove($this->cacheFile($key));
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		$files = $this->fileSystem->glob("{$this->cachePath}/*");

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if($this->fileSystem->isFile($file) && $this->fileSystem->remove($file) === false)
				{
					return false;
				}
			}
		}

		return true;
	}
}
