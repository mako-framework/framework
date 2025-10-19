<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use Throwable;

/**
 * Hint interface.
 */
interface HintInterface
{
	/**
	 * Returns TRUE if the hint can provide a solution for the exception and FALSE if not.
	 */
	public function canProvideHint(Throwable $exception): bool;

	/**
	 * Returns a hint if one is found and NULL if not.
	 */
	public function getHint(Throwable $exception): ?string;
}
