<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\file\FileSystem;

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
	 * @var bool|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem     File system instance
	 * @param string                $sessionPath    Session path
	 * @param bool|array            $classWhitelist Class whitelist
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
		return $this->sessionPath . '/' . $sessionId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL)
	{
		if($this->fileSystem->isWritable($this->sessionPath))
		{
			$this->fileSystem->put($this->sessionFile($sessionId), serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function delete(string $sessionId)
	{
		if($this->fileSystem->has($this->sessionFile($sessionId)) && $this->fileSystem->isWritable($this->sessionFile($sessionId)))
		{
			$this->fileSystem->remove($this->sessionFile($sessionId));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc(int $dataTTL)
	{
		$files = $this->fileSystem->glob($this->sessionPath . '/*');

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
