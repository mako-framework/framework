<?php

namespace mako\validator\plugins;

/**
 * Base plugin.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Rule name.
	 * 
	 * @var string
	 */

	protected $ruleName = '';

	/**
	 * Package name.
	 * 
	 * @var string
	 */

	protected $packageName = '';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returnst the rule name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getRuleName()
	{
		return $this->ruleName;
	}

	/**
	 * Returnst the package name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getPackageName()
	{
		return $this->packageName;
	}
}

