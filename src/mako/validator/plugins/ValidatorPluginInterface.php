<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

/**
 * Validator plugin interface.
 *
 * @author  Frederic G. Østby
 */

interface ValidatorPluginInterface
{
	/**
	 * Returnst the rule name.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getRuleName();

	/**
	 * Returnst the package name.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getPackageName();
}