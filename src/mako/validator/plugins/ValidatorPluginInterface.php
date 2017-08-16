<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

/**
 * Validator plugin interface.
 *
 * @author Frederic G. Østby
 */
interface ValidatorPluginInterface
{
	/**
	 * Returnst the rule name.
	 *
	 * @return string
	 */
	public function getRuleName(): string;

	/**
	 * Returnst the package name.
	 *
	 * @return string
	 */
	public function getPackageName(): string;
}
