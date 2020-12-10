<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\services;

use mako\application\Application;
use mako\config\Config;
use mako\syringe\Container;

/**
 * Abstract service.
 */
abstract class Service
{
	/**
	 * Application.
	 *
	 * @var \mako\application\Application
	 */
	protected $app;

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
	 * @param \mako\application\Application $app       Application
	 * @param \mako\syringe\Container       $container Container
	 * @param \mako\config\Config           $config    Config
	 */
	public function __construct(Application $app, Container $container, Config $config)
	{
		$this->app = $app;

		$this->container = $container;

		$this->config = $config;
	}

	/**
	 * Registers the service.
	 */
	abstract public function register(): void;
}
