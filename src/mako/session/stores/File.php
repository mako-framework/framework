<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\file\FileSystem;
use mako\session\stores\StoreInterface;

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
	 * Session path.
	 *
	 * @var string
	 */

	protected $sessionPath;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem   File system instance
	 * @param   string                 $sessionPath  Session path
	 */

	public function __construct(FileSystem $fileSystem, $sessionPath)
	{
		$this->fileSystem = $fileSystem;

		$this->sessionPath = $sessionPath;
	}

	/**
	 * {@inheritdoc}
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		if($this->fileSystem->isWritable($this->sessionPath))
		{
			$this->fileSystem->putContents($this->sessionPath . '/' . $sessionId, serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function read($sessionId)
	{
		$sessionData = [];

		if($this->fileSystem->exists($this->sessionPath . '/' . $sessionId) && $this->fileSystem->isReadable($this->sessionPath . '/' . $sessionId))
		{
			$sessionData = unserialize($this->fileSystem->getContents($this->sessionPath . '/' . $sessionId));
		}

		return $sessionData;
	}

	/**
	 * {@inheritdoc}
	 */

	public function delete($sessionId)
	{
		if($this->fileSystem->exists($this->sessionPath . '/' . $sessionId) && $this->fileSystem->isWritable($this->sessionPath . '/' . $sessionId))
		{
			$this->fileSystem->delete($this->sessionPath . '/' . $sessionId);
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function gc($dataTTL)
	{
		$files = $this->fileSystem->glob($this->sessionPath . '/*');

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if(($this->fileSystem->lastModified($file) + $dataTTL) < time() && $this->fileSystem->isWritable($file))
				{
					$this->fileSystem->delete($file);
				}
			}
		}
	}
}