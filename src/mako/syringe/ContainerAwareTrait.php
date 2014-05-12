<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\syringe;

use \RuntimeException;

use \mako\syringe\Container;

/**
 * Container aware trait.
 * 
 * @author  Frederic G. Østby
 */

trait ContainerAwareTrait
{
	/**
	 * IoC container instance.
	 * 
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Sets the container instance.
	 * 
	 * @access  public
	 * @param   \mako\syringe\Container  $container  IoC container instance
	 */

	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves item from the container using overloading.
	 * 
	 * @access  public
	 * @param   string  $key  Key
	 * @return  mixed
	 */

	public function __get($key)
	{
		if(!$this->container->has($key))
		{
			throw new RuntimeException(vsprintf("%s: Unable to resolve [ %s ].", [__TRAIT__, $key]));
		}

		return $this->container->get($key);
	}
}