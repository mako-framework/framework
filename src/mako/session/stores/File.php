<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use \mako\file\FileSystem;

/**
 * File store.
 *
 * @author  Frederic G. Østby
 */

class File implements \mako\session\stores\StoreInterface
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
	 * Writes session data.
	 *
	 * @access  public
	 * @param   string  $sessionId    Session id
	 * @param   array   $sessionData  Session data
	 * @param   int     $dataTTL      TTL in seconds
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		if($this->fileSystem->isWritable($this->sessionPath))
		{
			$this->fileSystem->putContents($this->sessionPath . '/' . $sessionId, serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * Reads and returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  array
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
	 * Destroys the session data assiciated with the provided id.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 */

	public function delete($sessionId)
	{
		if($this->fileSystem->exists($this->sessionPath . '/' . $sessionId) && $this->fileSystem->isWritable($this->sessionPath . '/' . $sessionId))
		{
			$this->fileSystem->delete($this->sessionPath . '/' . $sessionId);
		}
	}

	/**
	 * Garbage collector that deletes expired session data.
	 *
	 * @access  public
	 * @param   int      $dataTTL  Data TTL in seconds
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