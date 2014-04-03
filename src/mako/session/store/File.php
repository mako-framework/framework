<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\store;

/**
 * File store.
 *
 * @author  Frederic G. Østby
 */

class File implements \mako\session\store\StoreInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Session path.
	 * 
	 * @var string
	 */

	protected $sessionPath;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $sessionPath  Session path
	 */

	public function __construct($sessionPath)
	{
		$this->sessionPath = $sessionPath;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Writes session data.
	 *
	 * @access  public
	 * @param   string   $sessionId    Session id
	 * @param   string   $sessionData  Session data
	 * @param   int      $dataTTL      TTL in seconds
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		if(is_writable($this->sessionPath))
		{
			file_put_contents($this->sessionPath . '/' . $sessionId, serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * Reads and returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  string
	 */

	public function read($sessionId)
	{
		$sessionData = [];

		if(file_exists($this->sessionPath . '/' . $sessionId) && is_readable($this->sessionPath . '/' . $sessionId))
		{
			$sessionData = unserialize(file_get_contents($this->sessionPath . '/' . $sessionId));
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
		if(file_exists($this->sessionPath . '/' . $sessionId) && is_writable($this->sessionPath . '/' . $sessionId))
		{
			unlink($this->sessionPath . '/' . $sessionId);
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
		$files = glob($this->sessionPath . '/*');

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if((filemtime($file) + $dataTTL) < time() && is_writable($file))
				{
					unlink($file);
				}
			}
		}
	}
}

