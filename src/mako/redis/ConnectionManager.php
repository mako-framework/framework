<?php

namespace mako\redis;

use \RuntimeException;

use \mako\redis\Redis;

/**
 * Class that handles redis connections.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ConnectionManager
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Name of the default connection.
	 * 
	 * @var string
	 */

	protected $default;

	/**
	 * Redis configurations.
	 * 
	 * @var array
	 */

	protected $configurations;

	/**
	 * Redis connections.
	 * 
	 * @var array
	 */

	protected $connections = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $default         Default connection name
	 * @param   array   $configurations  Redis configurations
	 */

	public function __construct($default, array $configurations)
	{
		$this->default = $default;

		$this->configurations = $configurations;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Connects to the chosen redis configuration and returns the connection.
	 * 
	 * @access  public
	 * @param   string             $connection  Connection name
	 * @return  \mako\redis\Redis
	 */

	protected function connect($connection)
	{
		if(!isset($this->configurations[$connection]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the redis configuration.", [__METHOD__, $connection]));
		}

		return new Redis($this->configurations[$connection]);
	}

	/**
	 * Returns the chosen connection.
	 * 
	 * @access  public
	 * @param   string             $connection  (optional) Connection name
	 * @return  \mako\redis\Redis
	 */

	public function connection($connection = null)
	{
		$connection = $connection ?: $this->default;

		if(!isset($this->connections[$connection]))
		{
			$this->connections[$connection] = $this->connect($connection);
		}

		return $this->connections[$connection];
	}

	/**
	 * Magic shortcut to the default redis connection.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->connection(), $name], $arguments);
	}
}

/** -------------------- End of file -------------------- **/