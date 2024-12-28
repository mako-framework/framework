<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

/**
 * Namespaced Loader interface.
 */
interface NamespacedLoaderInterface extends LoaderInterface
{
	/**
	 * Registers a namespace.
	 */
	public function registerNamespace(string $namespace, string $path): void;
}
