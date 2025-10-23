<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use ErrorException;
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
 * Undefined property hint.
 */
class UndefinedProperty implements HintInterface
{
	use SuggestionTrait;

	/**
	 * Regex that matches the class property name in the error message.
	 */
	protected const string REGEX = '/^Undefined property: (.*)/u';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canProvideHint(Throwable $exception): bool
	{
		return $exception instanceof ErrorException && str_starts_with($exception->getMessage(), 'Undefined property');
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

		[$class, $property] = explode('::$', $matches[1], 2);

		if ($class === 'stdClass') {
			return null;
		}

		$properties = array_map(static fn ($property) => $property->getName(), (new ReflectionClass($class))->getProperties());

    	$suggestion = $this->suggest($property, $properties);

		return $suggestion === null ? null : "Did you mean to access the {$class}::\${$suggestion} property?";
	}
}
