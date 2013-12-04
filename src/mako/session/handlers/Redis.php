<?php

namespace mako\session\handlers;

use \mako\database\Redis as Sider;

/**
 * Redis session handler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Redis implements \SessionHandlerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Redis object.
	 *
	 * @var \mako\Redis
	 */

	protected $redis;

	/**
	 * Max session lifetime.
	 *
	 * @var int
	 */

	protected $maxLifetime;

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
		$this->redis = new Sider($config['configuration']);

		$this->maxLifetime = ini_get('session.gc_maxlifetime');
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
		return (string) $this->redis->get('sess_' . $sessionId);
	}

	/**
	 * Writes data to the session.
	 *
	 * @access  public
	 * @param   string   $sessionId    Session id
	 * @param   string   $data         Session data
	 * @return  boolean
	 */

	public function write($sessionId, $data)
	{
		$this->redis->set('sess_' . $sessionId, $data);

		$this->redis->expire('sess_' . $sessionId, $this->maxLifetime);

		return true;
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
		return (bool) $this->redis->del('sess_' . $sessionId);
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
		return true;
	}
}

/** -------------------- End of file -------------------- **/