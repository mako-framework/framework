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
	 * Returns true if the hint can provide a solution for the exception and false if not.
	 */
	public function canProvideHint(Throwable $exception): bool;

	/**
	 * Returns a hint if possible and NULL if not.
	 */
	public function getHint(Throwable $exception): ?string;
}
