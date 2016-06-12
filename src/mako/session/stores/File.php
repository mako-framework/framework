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
	 * Class whitelist.
	 *
	 * @var boolean|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem      File system instance
	 * @param   string                 $sessionPath     Session path
	 * @param   boolean|array          $classWhitelist  Class whitelist
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
	 * @access  protected
	 * @param   string     $sessionId  Session id
	 * @return  string
	 */
	protected function sessionFile($sessionId)
	{
		return $this->sessionPath . '/' . $sessionId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($sessionId, $sessionData, $dataTTL)
	{
		if($this->fileSystem->isWritable($this->sessionPath))
		{
			$this->fileSystem->putContents($this->sessionFile($sessionId), serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($sessionId)
	{
		$sessionData = [];

		if($this->fileSystem->exists($this->sessionFile($sessionId)) && $this->fileSystem->isReadable($this->sessionFile($sessionId)))
		{
			$sessionData = unserialize($this->fileSystem->getContents($this->sessionFile($sessionId)), ['allowed_classes' => $this->classWhitelist]);
		}

		return $sessionData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($sessionId)
	{
		if($this->fileSystem->exists($this->sessionFile($sessionId)) && $this->fileSystem->isWritable($this->sessionFile($sessionId)))
		{
			$this->fileSystem->delete($this->sessionFile($sessionId));
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