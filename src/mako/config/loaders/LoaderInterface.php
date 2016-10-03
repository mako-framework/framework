<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\config\loaders;

/**
 * Loader interface.
 *
 * @author  Frederic G. Østby
 */
interface LoaderInterface
{
	public function load(string $file, string $environment = null);
}