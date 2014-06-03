<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

/**
 * Reactor helpers trait.
 *
 * @author  Frederic G. Østby
 */

trait HelpersTrait
{
	/**
	 * Returns the application namespace.
	 * 
	 * @access  protected
	 * @param   string     $applicationPath  Application path
	 * @param   boolean    $prefix           (optional) Prefix the namespace with a slash?
	 */

	protected function getApplicationNamespace($applicationPath, $prefix = false)
	{
		$namespace = basename(rtrim($applicationPath, '\\'));

		if($prefix)
		{
			$namespace = '\\' . $namespace;
		}

		return $namespace;
	}
}