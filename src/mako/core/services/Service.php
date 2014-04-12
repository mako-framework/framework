<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\syringe\Syringe;

/**
 * Abstract service.
 *
 * @author  Frederic G. Østby
 */

abstract class Service
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * IoC container instance
	 * 
	 * @var \mako\syringe\Syringe
	 */

	protected $container;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\syringe\Syringe  $container  IoC container instance
	 */

	public function __construct(Syringe $container)
	{
		$this->container = $container;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	abstract public function register();
}