<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

/**
 * Loader interface.
 */
interface LoaderInterface
{
	/**
	 * Loads the configuration file.
	 */
	public function load(string $file, ?string $environment = null): array;
}
