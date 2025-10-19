<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\suggesters\traits;

use mako\error\handlers\suggesters\ArgumentCountErrorSuggester;
use mako\error\handlers\suggesters\FunctionSuggester;
use mako\error\handlers\suggesters\MethodSuggester;
use Throwable;

/**
 * Suggester trait.
 */
trait SuggesterTrait
{
	/**
	 * Get suggestion.
	 */
	protected function getSuggestion(Throwable $exception): ?string
	{
		/** @var array<class-string<\mako\error\handlers\suggesters\SuggesterInterface>> $suggesters */
		$suggesters = [
			ArgumentCountErrorSuggester::class,
			FunctionSuggester::class,
			MethodSuggester::class,
		];

		try {
			foreach ($suggesters as $suggesterClass) {
				$suggester = new $suggesterClass;

				if ($suggester->canSuggest($exception)) {
					return $suggester->getSuggestion($exception);
				}
			}
		}
		catch (Throwable) {
			// Do nothing if a suggester fails
		}

		return null;
	}
}
