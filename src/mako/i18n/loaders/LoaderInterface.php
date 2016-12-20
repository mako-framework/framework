<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\i18n\loaders;

/**
 * Loader interface.
 *
 * @author Frederic G. Østby
 */
interface LoaderInterface
{
	/**
	 * Returns inflection rules or NULL if they doesn't exist.
	 *
	 * @access public
	 * @param  string     $language Name of the language pack
	 * @return array|null
	 */
	public function loadInflection(string $language);

	/**
	 * Loads and returns language strings.
	 *
	 * @access public
	 * @param  string $language Name of the language pack
	 * @param  string $file     File we want to load
	 * @return array
	 */
	public function loadStrings(string $language, string $file): array;
}
