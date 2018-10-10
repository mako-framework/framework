<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\config\Config;
use mako\syringe\Container;

/**
 * Abstract service.
 *
 * @author Frederic G. Østby
 */
abstract class Service
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Config.
	 *
	 * @var \mako\config\Config
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container $container Container
	 * @param \mako\config\Config     $config    Config
	 */
	public function __construct(Container $container, Config $config)
	{
		$this->container = $container;

		$this->config = $config;
	}

	/**
	 * Registers the service.
	 */
	abstract public function register();
}
