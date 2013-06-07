<?php

namespace mako\session;

use \mako\Redis as Sider;

/**
 * Redis session adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Redis extends \mako\session\Adapter implements \mako\session\HandlerInterface
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

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		parent::__destruct();
		
		session_write_close();

		$this->redis = null;
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
		return (string) $this->redis->get('sess_' . $id);
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
		$this->redis->set('sess_' . $id, $data);

		$this->redis->expire('sess_' . $id, $this->maxLifetime);

		return true;
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
		return (bool) $this->redis->del('sess_' . $id);
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
		return true;
	}
}

/** -------------------- End of file -------------------- **/