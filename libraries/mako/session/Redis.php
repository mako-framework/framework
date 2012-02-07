<?php

namespace mako\session;

use \mako\Redis as MRedis;

/**
* Redis adapter.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Redis extends \mako\session\Adapter
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Redis object.
	*
	* @var mako\Redis
	*/

	protected $redis;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   array   Configuration
	*/

	public function __construct(array $config)
	{
		parent::__construct();

		$this->redis = new MRedis($config['configuration']);
	}

	/**
	* Destructor.
	*
	* @access  public
	*/

	public function __destruct()
	{
		session_write_close();

		$this->redis = null;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns session data.
	*
	* @access  public
	* @param   string  Session id
	* @return  string
	*/

	public function read($id)
	{
		return (string) $this->redis->get('sess_' . $id);
	}

	/**
	* Writes data to the session.
	*
	* @access  public
	* @param   string  Session id
	* @param   string  Session data
	*/

	public function write($id, $data)
	{
		$this->redis->set('sess_' . $id, $data);

		$this->redis->expire('sess_' . $id, $this->maxLifetime);

		return true;
	}

	/**
	* Destroys the session.
	*
	* @access  public
	* @param   string   Session id
	* @return  boolean
	*/

	public function destroy($id)
	{
		return (bool) $this->redis->del('sess_' . $id);
	}
}

/** -------------------- End of file --------------------**/