<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use mako\common\traits\SuggestionTrait;
use Override;
use ReflectionClass;
use Throwable;

use function array_map;
use function explode;
use function preg_match;
use function str_contains;
use function str_starts_with;

/**
 * Undefined method hint.
 */
class UndefinedMethod implements HintInterface
{
	use SuggestionTrait;

	/**
	 * Regex that matches the method name in the error message.
	 */
	protected const string REGEX = '/^Call to undefined method (.*)\(\)/u';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canProvideHint(Throwable $exception): bool
	{
		return str_starts_with($exception->getMessage(), 'Call to undefined method');
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHint(Throwable $exception): ?string
	{
		$message = $exception->getMessage();

		if (
			str_contains($message, 'class@anonymous')
			|| preg_match(static::REGEX, $message, $matches) !== 1
		) {
			return null;
		}

		[$class, $method] = explode('::', $matches[1], 2);

		$methods = array_map(static fn ($method) => $method->getName(), (new ReflectionClass($class))->getMethods());

    	$suggestion = $this->suggest($method, $methods);

		return $suggestion === null ? null : "Did you mean to call the {$class}::{$suggestion}() method?";
	}
}
