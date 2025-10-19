<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\suggesters;

use Throwable;

/**
 * Suggester interface.
 */
interface SuggesterInterface
{
	/**
	 * Returns TRUE if the suggester can suggest a solution for the exception and FALSE if not.
	 */
	public function canSuggest(Throwable $exception): bool;

	/**
	 * Returns a suggestion if one is found and NULL if not.
	 */
	public function getSuggestion(Throwable $exception): ?string;
}
