<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

/**
 * Loader interface.
 *
 * @author Frederic G. Østby
 */
interface LoaderInterface
{
	/**
	 * Loads the configuration file.
	 *
	 * @param  string      $file        File name
	 * @param  string|null $environment Environment
	 * @return array
	 */
	public function load(string $file, ?string $environment = null): array;
}
