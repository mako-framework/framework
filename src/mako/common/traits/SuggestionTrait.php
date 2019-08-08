<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

use function similar_text;

/**
 * Suggestion trait.
 *
 * @author Frederic G. Østby
 */
trait SuggestionTrait
{
	/**
	 * Returns the string that resembles the provided string the most.
	 * NULL is returned if no string with a similarity of 66% or more is found.
	 *
	 * @param  string      $string       String
	 * @param  array       $alternatives Alternatives
	 * @return string|null
	 */
	protected function suggest(string $string, array $alternatives): ?string
	{
		$suggestion = false;

		foreach($alternatives as $alternative)
		{
			similar_text($string, $alternative, $similarity);

			if($similarity > 66 && ($suggestion === false || $suggestion['similarity'] < $similarity))
			{
				$suggestion = ['string' => $alternative, 'similarity' => $similarity];
			}
		}

		return $suggestion === false ? null : $suggestion['string'];
	}
}
