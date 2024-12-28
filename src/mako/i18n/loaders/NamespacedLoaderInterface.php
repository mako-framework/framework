<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\i18n\loaders;

/**
 * Namespaced loader interface.
 */
interface NamespacedLoaderInterface extends LoaderInterface
{
	/**
	 * Registers a namespace.
	 */
	public function registerNamespace(string $namespace, string $path): void;
}
