<?php

namespace mako\session\handlers;

/**
 * File session handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class File implements \SessionHandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Configuration.
	 * 
	 * @var array
	 */

	protected $config;

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
		$this->config = $config;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Open session.
	 *
	 * @access  public
	 * @param   string   $savePath   Save path
	 * @param   string   $sessionId  Session id
	 * @return  boolean
	 */

	public function open($savePath, $sessionId)
	{
		return true;
	}

	/**
	 * Close session.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function close()
	{
		return true;
	}

	/**
	 * Returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  string
	 */

	public function read($sessionId)
	{
		$data = '';

		if(file_exists($this->config['path'] . '/' . $sessionId) && is_readable($this->config['path'] . '/' . $sessionId))
		{
			$data = (string) file_get_contents($this->config['path'] . '/' . $sessionId);
		}

		return $data;
	}

	/**
	 * Writes data to the session.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 * @param   string   $data       Session data
	 * @return  boolean
	 */

	public function write($sessionId, $data)
	{
		if(is_writable($this->config['path']))
		{
			return file_put_contents($this->config['path'] . '/' . $sessionId, $data) === false ? false : true;
		}

		return false;
	}

	/**
	 * Destroys the session.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 * @return  boolean
	 */

	public function destroy($sessionId)
	{
		if(file_exists($this->config['path'] . '/' . $sessionId) && is_writable($this->config['path'] . '/' . $sessionId))
		{
			return unlink($this->config['path'] . '/' . $sessionId);
		}

		return false;
	}

	/**
	 * Garbage collector.
	 *
	 * @access  public
	 * @param   int      $maxLifetime  Max lifetime in secods
	 * @return  boolean
	 */

	public function gc($maxLifetime)
	{
		$files = glob($this->config['path'] . '/*');

		if(is_array($files))
		{
			foreach($files as $file)
			{
				if((filemtime($file) + $maxLifetime) < time() && is_writable($file))
				{
					unlink($file);
				}
			}
		}

		return true;
	}
}

/** -------------------- End of file -------------------- **/