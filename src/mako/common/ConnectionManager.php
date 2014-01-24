<?php

namespace mako\common;

abstract class ConnectionManager
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
	 * Configurations.
	 * 
	 * @var array
	 */

	protected $configurations;

	/**
	 * Connections.
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
	 * @param   array   $configurations  Configurations
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
	 * Adds a configuration.
	 * 
	 * @access  public
	 * @param   string  $name           Configuration name
	 * @param   array   $configuration  Configuration
	 */

	public function addConfiguration($name, array $configuration)
	{
		$this->configurations[$name] = $configuration;
	}

	/**
	 * Removes a configuration.
	 * 
	 * @access  public
	 * @param   string  $name  Configuration name
	 */

	public function removeConfiguration($name)
	{
		unset($this->configurations[$name]);
	}

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

/** -------------------- End of file -------------------- **/