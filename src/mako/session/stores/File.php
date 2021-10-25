<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\file\FileSystem;

use function is_array;
use function serialize;
use function time;
use function unserialize;

/**
 * File store.
 *
 * @author Frederic G. Østby
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
	 * Session path.
	 *
	 * @var string
	 */
	protected $sessionPath;

	/**
	 * Class whitelist.
	 *
	 * @var array|bool
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem     File system instance
	 * @param string                $sessionPath    Session path
	 * @param array|bool            $classWhitelist Class whitelist
	 */
	public function __construct(FileSystem $fileSystem, $sessionPath, $classWhitelist = false)
	{
		$this->fileSystem = $fileSystem;

		$this->sessionPath = $sessionPath;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * Returns the path to the session file.
	 *
	 * @param  string $sessionId Session id
	 * @return string
	 */
	protected function sessionFile(string $sessionId): string
	{
		return "{$this->sessionPath}/{$sessionId}";
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{
		if($this->fileSystem->isWritable($this->sessionPath))
		{
			$this->fileSystem->put($this->sessionFile($sessionId), serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(string $sessionId): array
	{
		$sessionData = [];

		if($this->fileSystem->has($this->sessionFile($sessionId)) && $this->fileSystem->isReadable($this->sessionFile($sessionId)))
		{
			$sessionData = unserialize($this->fileSystem->get($this->sessionFile($sessionId)), ['allowed_classes' => $this->classWhitelist]);
		}

		return $sessionData;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $sessionId): void
	{
		if($this->fileSystem->has($this->sessionFile($sessionId)) && $this->fileSystem->isWritable($this->sessionFile($sessionId)))
		{
			$this->fileSystem->remove($this->sessionFile($sessionId));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{
		$files = $this->fileSystem->glob("{$this->sessionPath}/*");

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if(($this->fileSystem->lastModified($file) + $dataTTL) < time() && $this->fileSystem->isWritable($file))
				{
					$this->fileSystem->remove($file);
				}
			}
		}
	}
}
