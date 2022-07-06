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
	 * Constructor.
	 *
	 * @param \mako\application\Application $app       Application
	 * @param \mako\syringe\Container       $container Container
	 * @param \mako\config\Config           $config    Config
	 */
	public function __construct(
		protected Application $app,
		protected Container $container,
		protected Config $config
	)
	{}

	/**
	 * Registers the service.
	 */
	abstract public function register(): void;
}
