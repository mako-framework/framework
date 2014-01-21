<?php

namespace mako\core\services;

use \mako\core\Application;

/**
 * Abstract service.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Service
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Application instance
	 * 
	 * @var \mako\core\Application
	 */

	protected $application;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\core\Application  $application  Application instance
	 */

	public function __construct(Application $application)
	{
		$this->application = $application;
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

/** -------------------- End of file -------------------- **/