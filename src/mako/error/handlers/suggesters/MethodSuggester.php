<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\suggesters;

use mako\common\traits\SuggestionTrait;
use Override;
use ReflectionClass;
use Throwable;

use function array_map;
use function explode;
use function preg_match;
use function str_starts_with;

/**
 * Method suggester.
 */
class MethodSuggester implements SuggesterInterface
{
	use SuggestionTrait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canSuggest(Throwable $exception): bool
	{
		return str_starts_with($exception->getMessage(), 'Call to undefined method');
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getSuggestion(Throwable $exception): ?string
	{
		if (preg_match('/^Call to undefined method (.*)\(\)$/u', $exception->getMessage(), $matches) !== 1) {
			return null;
		}

		[$class, $method] = explode('::', $matches[1], 2);

		$methods = array_map(static fn ($method) => $method->getName(), (new ReflectionClass($class))->getMethods());

    	$suggestion = $this->suggest($method, $methods);

		if ($suggestion !== null) {
			return "Did you mean to call the {$class}::{$suggestion}() method?";
		}

		return null;
	}
}
