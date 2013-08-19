<?php

namespace mako\session;

use \mako\Redis as Sider;

/**
 * File session adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class File extends \mako\session\Adapter implements \mako\session\HandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		parent::__destruct();
		
		session_write_close();

		// Fixes issue with Debian and Ubuntu session garbage collection

		if(mt_rand(1, 100) === 100)
		{
			$this->sessionGarbageCollector(ini_get('session.gc_maxlifetime'));
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Open session.
	 *
	 * @access  public
	 * @param   string   $savePath     Save path
	 * @param   string   $sessionName  Session name
	 * @return  boolean
	 */

	public function sessionOpen($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * Close session.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function sessionClose()
	{
		return true;
	}

	/**
	 * Returns session data.
	 *
	 * @access  public
	 * @param   string  $id  Session id
	 * @return  string
	 */

	public function sessionRead($id)
	{
		$data = '';

		if(file_exists($this->config['path'] . '/' . $id) && is_readable($this->config['path'] . '/' . $id))
		{
			$data = (string) file_get_contents($this->config['path'] . '/' . $id);
		}

		return $data;
	}

	/**
	 * Writes data to the session.
	 *
	 * @access  public
	 * @param   string  $id    Session id
	 * @param   string  $data  Session data
	 */

	public function sessionWrite($id, $data)
	{
		if(is_writable($this->config['path']))
		{
			return file_put_contents($this->config['path'] . '/' . $id, $data) === false ? false : true;
		}

		return false;
	}

	/**
	 * Destroys the session.
	 *
	 * @access  public
	 * @param   string   $id  Session id
	 * @return  boolean
	 */

	public function sessionDestroy($id)
	{
		if(file_exists($this->config['path'] . '/' . $id) && is_writable($this->config['path'] . '/' . $id))
		{
			return unlink($this->config['path'] . '/' . $id);
		}

		return false;
	}

	/**
	 * Garbage collector.
	 *
	 * @access  public
	 * @param   int      $maxLifetime  Lifetime in secods
	 * @return  boolean
	 */

	public function sessionGarbageCollector($maxLifetime)
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