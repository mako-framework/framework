<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\syringe\Container;

/**
 * Abstract service.
 *
 * @author  Frederic G. Østby
 */

abstract class Service
{
	/**
	 * IoC container instance
	 *
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\syringe\Container  $container  IoC container instance
	 */

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Registers the service.
	 *
	 * @access  public
	 */

	abstract public function register();
}