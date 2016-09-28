<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\common;

use Closure;
use RuntimeException;

use mako\common\ConfigurableTrait;
use mako\syringe\Container;

/**
 * Adapter manager.
 *
 * @author  Frederic G. Østby
 */
abstract class AdapterManager
{
	use ConfigurableTrait;

	/**
	 * IoC container instance.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * Connections.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string                   $default         Default connection name
	 * @param   array                    $configurations  Configurations
	 * @param   \mako\syringe\Container  $container       IoC container instance
	 */
	public function __construct(string $default, array $configurations, Container $container)
	{
		$this->default = $default;

		$this->configurations = $configurations;

		$this->container = $container;
	}

	/**
	 * Adds extension.
	 *
	 * @access  public
	 * @param   string           $name        Adapter name
	 * @param   string|\Closure  $adapter     Adapter
	 * @param   array            $parameters  Parameters
	 */
	public function extend(string $name, $adapter, array $parameters = [])
	{
		$this->extensions[$name] = ['adapter' => $adapter, 'parameters' => $parameters];
	}

	/**
	 * Factory.
	 *
	 * @access  protected
	 * @param   string     $adapterName    Adapter name
	 * @param   array      $configuration  Adapter configuration
	 * @return  mixed
	 */
	protected function factory(string $adapterName, array $configuration = [])
	{
		if(method_exists($this, ($method = $adapterName . 'Factory')))
		{
			return $this->$method($configuration);
		}
		elseif(isset($this->extensions[$adapterName]))
		{
			$adapter = $this->extensions[$adapterName]['adapter'];

			$parameters = $this->extensions[$adapterName]['parameters'] + $configuration;

			if($adapter instanceof Closure)
			{
				return $this->container->call($adapter, $parameters);
			}

			return $this->container->get($adapter, $parameters);
		}

		throw new RuntimeException(vsprintf("%s(): A factory method for the [ %s ] adapter has not been defined.", [__METHOD__, $adapterName]));
	}

	/**
	 * Returns a new adapter instance.
	 *
	 * @access  public
	 * @param   string  $configuration  Configuration name
	 * @return  mixed
	 */
	abstract protected function instantiate(string $configuration);

	/**
	 * Returns an instance of the chosen adapter configuration.
	 *
	 * @access  public
	 * @param   string  $configuration  Configuration name
	 * @return  mixed
	 */
	public function instance(string $configuration = null)
	{
		$configuration = $configuration ?? $this->default;

		if(!isset($this->instances[$configuration]))
		{
			$this->instances[$configuration] = $this->instantiate($configuration);
		}

		return $this->instances[$configuration];
	}

	/**
	 * Magic shortcut to the default configuration.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->instance()->{$name}(...$arguments);
	}
}