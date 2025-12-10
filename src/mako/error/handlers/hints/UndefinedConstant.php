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

use function array_keys;
use function array_last;
use function explode;
use function get_defined_constants;
use function preg_match;
use function str_contains;
use function str_starts_with;

/**
 * Undefined constant hint.
 */
class UndefinedConstant implements HintInterface
{
	use SuggestionTrait;

	/**
	 * Regex that matches the constant name in the error message.
	 */
	protected const string REGEX = '/^Undefined constant "?([^"]+)"?/u';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canProvideHint(Throwable $exception): bool
	{
		return str_starts_with($exception->getMessage(), 'Undefined constant');
	}

	/**
	 * Returns a class constant suggestion if possible and NULL if not.
	 */
	protected function getClassConstantSuggestion(string $classConstant): ?string
	{
		[$class, $constant] = explode('::', $classConstant, 2);

		$constants = array_keys((new ReflectionClass($class))->getConstants());

		$suggestion = $this->suggest($constant, $constants);

		return $suggestion === null ? null : "{$class}::{$suggestion}";
	}

	/**
	 * Returns a constant suggestion if possible and NULL if not.
	 */
	protected function getConstantSuggestion(string $constant): ?string
	{
		$constants = array_keys(get_defined_constants());

		$suggestion = $this->suggest($constant, $constants);

		if ($suggestion === null && str_contains($constant, '\\')) {
			$constant = explode('\\', $constant);
			$constant = array_last($constant);

			return $this->suggest($constant, $constants);
		}

		return $suggestion;
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

		$constant = $matches[1];

		if (str_contains($constant, '::')) {
			$suggestion = $this->getClassConstantSuggestion($constant);
		}
		else {
			$suggestion = $this->getConstantSuggestion($constant);
		}

		return $suggestion === null ? null : "Did you mean to use the {$suggestion} constant?";
	}
}
