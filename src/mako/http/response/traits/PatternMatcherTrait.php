<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\traits;

use function implode;
use function preg_match;

/**
 * Pattern matcher trait.
 */
trait PatternMatcherTrait
{
	/**
	 * Returns true if the string matches one of the patterns and false if not.
	 */
	protected function matchesPatterns(string $string, array $patterns): bool
	{
		if (preg_match('/^(' . implode('|', $patterns) . ')$/iu', $string) === 1) {
			return true;
		}

		return false;
	}
}
