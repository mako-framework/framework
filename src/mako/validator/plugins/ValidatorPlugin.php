<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

/**
 * Base plugin.
 *
 * @author  Frederic G. Østby
 */

abstract class ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
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