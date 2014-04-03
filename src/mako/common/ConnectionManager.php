<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\common;

/**
 * Connection manager.
 *
 * @author  Frederic G. Østby
 */

abstract class ConnectionManager
{
	use \mako\common\ConfigurableManagerTrait;
	
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Connections.
	 * 
	 * @var array
	 */

	protected $connections = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Connects to the chosen configuration and returns the connection.
	 * 
	 * @access  public
	 * @param   string  $connection  Connection name
	 * @return  mixed
	 */

	abstract protected function connect($connection);

	/**
	 * Returns the chosen connection.
	 * 
	 * @access  public
	 * @param   string  $connection  (optional) Connection name
	 * @return  mixed
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
	 * Magic shortcut to the default connection.
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

