<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\i18n\loaders;

/**
 * Loader interface.
 */
interface LoaderInterface
{
	/**
	 * Returns the inflection rules or NULL if they don't exist.
	 */
	public function loadInflection(string $language): ?array;

	/**
	 * Loads and returns language strings.
	 */
	public function loadStrings(string $language, string $file): array;
}
