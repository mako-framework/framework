<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\autoloading;

use function array_key_exists;
use function class_alias;

/**
 * Alias loader.
 */
class AliasLoader
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $aliases
	) {
	}

	/**
	 * Autoloads aliased classes.
	 */
	public function load(string $alias): bool
	{
		if (array_key_exists($alias, $this->aliases)) {
			return class_alias($this->aliases[$alias], $alias);
		}

		return false;
	}
}
