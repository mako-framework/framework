<?php

namespace mako\common;

use \RuntimeException;

use \mako\syringe\Syringe;

/**
 * Adapter manager.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class AdapterManager
{
	use \mako\common\ConfigurableManagerTrait;
	
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Reuse instances?
	 * 
	 * @var boolean
	 */

	const REUSE_INSTANCES = true;

	/**
	 * Syringe instance.
	 * 
	 * @var \mako\syringe\Syringe
	 */

	protected $syringe;

	/**
	 * Connections.
	 * 
	 * @var array
	 */

	protected $instances = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string                 $default         Default connection name
	 * @param   array                  $configurations  Configurations
	 * @param   \mako\syringe\Syringe  $syringe         Syringe instance
	 */

	public function __construct($default, array $configurations, Syringe $syringe)
	{
		$this->default = $default;

		$this->configurations = $configurations;

		$this->syringe = $syringe;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the factory method name.
	 * 
	 * @access  protected
	 * @param   string     $type  Cache type
	 * @return  string
	 */

	protected function getFactoryMethodName($type)
	{
		$method = $type . 'Adapter';

		if(!method_exists($this, $method))
		{
			throw new RuntimeException(vsprintf("%s(): A factory method for the [ %s ] adapter has not been defined.", [__METHOD__, $type]));
		}

		return $method;
	}

	/**
	 * Returns a new adapter instance.
	 * 
	 * @access  public
	 * @param   string  $configuration  Configuration name
	 * @return  mixed
	 */

	abstract protected function instantiate($configuration);

	/**
	 * Returns an instance of the chosen adapter configuration.
	 * 
	 * @access  public
	 * @param   string  $configuration  (optional) Configuration name
	 * @return  mixed
	 */

	public function instance($configuration = null)
	{
		$configuration = $configuration ?: $this->default;

		if(static::REUSE_INSTANCES)
		{
			if(!isset($this->instances[$configuration]))
			{
				$this->instances[$configuration] = $this->instantiate($configuration);
			}

			return $this->instances[$configuration];
		}
		else
		{
			return $this->instantiate($configuration);
		}
	}

	/**
	 * Magic shortcut to the default configuration.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->instance(), $name], $arguments);
	}
}

